<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Services\AntispamService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AntispamAndSuspensionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected AntispamService $antispam;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'email' => 'spammer_test@example.com',
            'password' => 'secret',
            'role' => 'member',
            'status' => 'active',
            'is_adult_confirmed' => true,
        ]);
        $this->user->profile()->create(['handle' => 'spammer', 'handle_lower' => 'spammer']);
        $this->antispam = app(AntispamService::class);
    }

    /**
     * Test 8 : Un retry idempotent avec la même clé ne crée ni doublon ni infraction.
     */
    public function test_idempotent_retry_does_not_duplicate_or_penalize(): void
    {
        $this->actingAs($this->user);
        $idempotencyKey = 'unique_network_retry_key_12345';

        // 1ère requête
        $res1 = $this->post('/publications', [
            'body' => 'Contenu sous clé idempotente',
            'idempotency_key' => $idempotencyKey,
        ]);
        $res1->assertRedirect('/fil');
        $this->assertEquals(1, Post::where('user_id', $this->user->id)->count());

        // 2ème requête réseau avec EXACTEMENT la même clé d'idempotence (retry)
        $res2 = $this->post('/publications', [
            'body' => 'Contenu sous clé idempotente',
            'idempotency_key' => $idempotencyKey,
        ]);

        // Ne doit pas créer de doublon
        $this->assertEquals(1, Post::where('user_id', $this->user->id)->count());
        // L'utilisateur ne doit pas être suspendu
        $this->assertFalse($this->user->fresh()->isWritingSuspended());
    }

    /**
     * Test 9 & 10 : Le spam déclenche exactement 24h de suspension ; une tentative pendant la sanction ne la prolonge pas.
     * À expiration simulée avec une horloge de test, le compte peut écrire à nouveau.
     */
    public function test_spam_triggers_24h_suspension_attempts_during_do_not_extend_and_expires_cleanly(): void
    {
        $this->actingAs($this->user);
        $duplicateBody = 'Texte répété de façon identique';

        // 1ère soumission -> acceptée
        $res1 = $this->antispam->validatePostSubmission($this->user, $duplicateBody);
        $this->assertTrue($res1['allowed']);
        $this->antispam->recordRateLimitEvent($this->user, 'post', 'accepted', $res1['payload_hash']);

        // 2ème soumission (dans les 10 min) -> refusée pour délai <60s
        $res2 = $this->antispam->validatePostSubmission($this->user, $duplicateBody);
        $this->assertFalse($res2['allowed']);
        $this->assertFalse($this->user->fresh()->isWritingSuspended(), "La 2e tentative ne doit pas suspendre directement.");

        // 3ème soumission (même texte 3 fois en 10 minutes) -> déclenche suspension 24h !
        $res3 = $this->antispam->validatePostSubmission($this->user, $duplicateBody);
        $this->assertFalse($res3['allowed']);
        $this->assertTrue($res3['suspended']);

        $userFresh = $this->user->fresh();
        $this->assertTrue($userFresh->isWritingSuspended());
        $initialSuspensionEnd = $userFresh->write_suspended_until;

        // Vérifier que la durée est bien de 24h
        $this->assertEquals(24, (int) round(Carbon::now('UTC')->diffInSeconds($initialSuspensionEnd) / 3600));

        // Avancer l'horloge de test de 2 heures (pendant la suspension)
        Carbon::setTestNow(Carbon::now('UTC')->addHours(2));

        // Une tentative pendant la sanction ne doit PAS la prolonger
        $attemptDuring = $this->antispam->validatePostSubmission($userFresh, 'Nouvel essai pendant la suspension');
        $this->assertFalse($attemptDuring['allowed']);
        $this->assertTrue($attemptDuring['suspended']);

        // Vérifier que la date de fin n'a PAS été repoussée
        $this->assertEquals($initialSuspensionEnd->timestamp, $this->user->fresh()->write_suspended_until->timestamp);

        // Avancer l'horloge de test au-delà de 24 heures (25h après la sanction initiale)
        Carbon::setTestNow(Carbon::now('UTC')->addHours(23)); // 2 + 23 = 25 heures

        $this->assertFalse($this->user->fresh()->isWritingSuspended(), "La suspension doit expirer automatiquement sans action manuelle.");

        // Le compte doit pouvoir écrire à nouveau
        $resAfterExpiry = $this->antispam->validatePostSubmission($this->user->fresh(), 'Message après libération');
        $this->assertTrue($resAfterExpiry['allowed']);

        Carbon::setTestNow(); // Reset horloge
    }

    /**
     * Test 11 : Un quota atteint sans répétition abusive ne déclenche pas la sanction de spam.
     */
    public function test_reaching_daily_quota_refuses_without_qualifying_as_spam_suspension(): void
    {
        $now = Carbon::now('UTC');

        // Simuler 5 publications acceptées réparties sur les dernières 24h (distinctes)
        for ($i = 1; $i <= 5; $i++) {
            $t = $now->copy()->subHours(6 - $i);
            $this->antispam->recordRateLimitEvent(
                $this->user,
                'post',
                'accepted',
                hash('sha256', "unique_post_{$i}"),
                null,
                $t
            );
        }

        // 6ème tentative distincte
        $result = $this->antispam->validatePostSubmission($this->user, 'Sixième publication distincte');

        // Doit refuser la publication
        $this->assertFalse($result['allowed']);
        $this->assertStringContainsString('limite de 5 publications', $result['reason']);

        // Mais ne doit PAS suspendre le compte pour 24h
        $this->assertFalse($result['suspended']);
        $this->assertFalse($this->user->fresh()->isWritingSuspended());
    }

    /**
     * Test 12 : Espacement strict de moins de 60 secondes entre deux publications.
     */
    public function test_two_posts_less_than_60_seconds_apart_are_rejected(): void
    {
        $this->actingAs($this->user);

        // 1er post accepté
        $this->post('/publications', ['body' => 'Premier message']);
        $this->assertEquals(1, Post::where('user_id', $this->user->id)->count());

        // 2e post 5 secondes après avec un texte différent
        $response = $this->post('/publications', ['body' => 'Deuxième message trop rapide']);

        // Doit être rejeté avec message d'attente
        $this->assertEquals(1, Post::where('user_id', $this->user->id)->count());
        $response->assertSessionHas('error');
    }
}
