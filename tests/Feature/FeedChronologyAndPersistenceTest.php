<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedChronologyAndPersistenceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user1;
    protected User $user2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user1 = User::create([
            'email' => 'user1@example.com',
            'password' => 'secret123',
            'role' => 'member',
            'status' => 'active',
            'is_adult_confirmed' => true,
        ]);
        $this->user1->profile()->create([
            'handle' => 'user1',
            'handle_lower' => 'user1',
        ]);

        $this->user2 = User::create([
            'email' => 'user2@example.com',
            'password' => 'secret123',
            'role' => 'member',
            'status' => 'active',
            'is_adult_confirmed' => true,
        ]);
        $this->user2->profile()->create([
            'handle' => 'user2',
            'handle_lower' => 'user2',
        ]);
    }

    /**
     * Test 2 : Une publication persiste en base.
     */
    public function test_post_persists_in_database(): void
    {
        $this->actingAs($this->user1);

        $response = $this->post('/publications', [
            'body' => 'Une pensée persistante dans le fil.',
            'comments_allowed' => 1,
        ]);

        $response->assertRedirect('/fil');

        $this->assertDatabaseHas('posts', [
            'user_id' => $this->user1->id,
            'body' => 'Une pensée persistante dans le fil.',
            'status' => 'published',
        ]);
    }

    /**
     * Test 3 : Les deux comptes voient le même ordre chronologique strict (created_at DESC, id DESC).
     */
    public function test_all_users_see_identical_chronological_order(): void
    {
        $baseTime = Carbon::now('UTC')->subHours(5);

        $postOld = Post::create([
            'user_id' => $this->user1->id,
            'body' => 'Publication ancienne',
            'status' => 'published',
            'created_at' => $baseTime,
            'updated_at' => $baseTime,
        ]);

        $postMid = Post::create([
            'user_id' => $this->user2->id,
            'body' => 'Publication intermédiaire',
            'status' => 'published',
            'created_at' => $baseTime->copy()->addHour(),
            'updated_at' => $baseTime->copy()->addHour(),
        ]);

        $postNew = Post::create([
            'user_id' => $this->user1->id,
            'body' => 'Publication récente',
            'status' => 'published',
            'created_at' => $baseTime->copy()->addHours(2),
            'updated_at' => $baseTime->copy()->addHours(2),
        ]);

        // Pour user1
        $this->actingAs($this->user1);
        $feed1 = Post::feedFor($this->user1)->pluck('id')->all();

        // Pour user2
        $this->actingAs($this->user2);
        $feed2 = Post::feedFor($this->user2)->pluck('id')->all();

        $this->assertEquals([$postNew->id, $postMid->id, $postOld->id], $feed1);
        $this->assertEquals([$postNew->id, $postMid->id, $postOld->id], $feed2);
    }

    /**
     * Test 4 : Modifier ou commenter une ancienne publication ne la fait JAMAIS remonter.
     */
    public function test_editing_or_commenting_old_post_does_not_bump_it_in_feed(): void
    {
        $timeOld = Carbon::now('UTC')->subDays(2);
        $timeNew = Carbon::now('UTC')->subHours(1);

        $oldPost = Post::create([
            'user_id' => $this->user1->id,
            'body' => 'Texte original ancien',
            'status' => 'published',
            'created_at' => $timeOld,
            'updated_at' => $timeOld,
        ]);

        $newPost = Post::create([
            'user_id' => $this->user2->id,
            'body' => 'Publication récente',
            'status' => 'published',
            'created_at' => $timeNew,
            'updated_at' => $timeNew,
        ]);

        // Vérification de l'ordre initial : newPost en premier
        $initialOrder = Post::feedFor($this->user1)->pluck('id')->all();
        $this->assertEquals([$newPost->id, $oldPost->id], $initialOrder);

        // 1. Modifier l'ancien post
        $this->actingAs($this->user1);
        $this->put("/publications/{$oldPost->id}", [
            'body' => 'Texte modifié hier soir',
            'comments_allowed' => 1,
        ]);

        // Vérifier que created_at est intact
        $oldPost->refresh();
        $this->assertEquals($timeOld->timestamp, $oldPost->created_at->timestamp);
        $this->assertNotNull($oldPost->edited_at);

        // L'ordre du fil doit rester inchangé : newPost toujours en premier !
        $orderAfterEdit = Post::feedFor($this->user1)->pluck('id')->all();
        $this->assertEquals([$newPost->id, $oldPost->id], $orderAfterEdit);

        // 2. Commenter l'ancien post
        $this->actingAs($this->user2);
        $this->post("/publications/{$oldPost->id}/commentaires", [
            'body' => 'Un commentaire tout récent sur ce vieux post',
        ]);

        // L'ordre du fil doit ENCORE être strictement inchangé !
        $orderAfterComment = Post::feedFor($this->user1)->pluck('id')->all();
        $this->assertEquals([$newPost->id, $oldPost->id], $orderAfterComment);
    }

    /**
     * Test 5 : La pagination par curseur stable ne produit aucun doublon si une nouvelle publication arrive entre deux pages.
     */
    public function test_cursor_pagination_prevents_duplicates_when_new_post_arrives(): void
    {
        // Créer 25 publications pour avoir 2 pages (20 par page)
        $base = Carbon::now('UTC')->subHours(30);
        $posts = [];
        for ($i = 1; $i <= 25; $i++) {
            $t = $base->copy()->addMinutes($i * 10);
            $posts[] = Post::create([
                'user_id' => $this->user1->id,
                'body' => "Post numéro {$i}",
                'status' => 'published',
                'created_at' => $t,
                'updated_at' => $t,
            ]);
        }

        $this->actingAs($this->user1);

        // Page 1 (les 20 plus récents)
        $responsePage1 = $this->get('/fil');
        $responsePage1->assertStatus(200);
        $page1Posts = $responsePage1->viewData('posts');
        $this->assertCount(20, $page1Posts);
        $nextCursor = $responsePage1->viewData('nextCursor');
        $this->assertNotNull($nextCursor);

        // Un autre utilisateur publie soudainement une NOUVELLE publication en haut du fil
        $brandNewPost = Post::create([
            'user_id' => $this->user2->id,
            'body' => 'Publication surgie entre deux pages',
            'status' => 'published',
            'created_at' => Carbon::now('UTC'),
            'updated_at' => Carbon::now('UTC'),
        ]);

        // Page 2 demandée avec le curseur stable
        $responsePage2 = $this->get('/fil?' . http_build_query($nextCursor));
        $responsePage2->assertStatus(200);
        $page2Posts = $responsePage2->viewData('posts');

        // Vérifier qu'aucun post de la page 1 ne se retrouve dans la page 2 (ZÉRO doublon)
        $page1Ids = $page1Posts->pluck('id')->all();
        $page2Ids = $page2Posts->pluck('id')->all();
        $intersect = array_intersect($page1Ids, $page2Ids);

        $this->assertEmpty($intersect, "La pagination par curseur ne doit produire aucun doublon.");
        $this->assertFalse(in_array($brandNewPost->id, $page2Ids));
    }
}
