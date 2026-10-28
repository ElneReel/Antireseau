<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PermissionsAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $user1;
    protected User $user2;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user1 = User::create([
            'email' => 'alice@test.local',
            'password' => 'secret',
            'role' => 'member',
            'status' => 'active',
            'is_adult_confirmed' => true,
        ]);
        $this->user1->profile()->create(['handle' => 'alice', 'handle_lower' => 'alice']);

        $this->user2 = User::create([
            'email' => 'bob@test.local',
            'password' => 'secret',
            'role' => 'member',
            'status' => 'active',
            'is_adult_confirmed' => true,
        ]);
        $this->user2->profile()->create(['handle' => 'bob', 'handle_lower' => 'bob']);

        $this->admin = User::create([
            'email' => 'admin@test.local',
            'password' => 'secret',
            'role' => 'admin',
            'status' => 'active',
            'is_adult_confirmed' => true,
        ]);
        $this->admin->profile()->create(['handle' => 'admin', 'handle_lower' => 'admin']);
    }

    /**
     * Test 6 : Une personne ne peut modifier ou supprimer le contenu d'une autre en forgeant une requête.
     */
    public function test_user_cannot_update_or_delete_another_users_post(): void
    {
        $post = Post::create([
            'user_id' => $this->user1->id,
            'body' => 'Post appartenant à Alice',
            'status' => 'published',
        ]);

        // Bob tente de modifier le post d'Alice
        $this->actingAs($this->user2);
        $responseUpdate = $this->put("/publications/{$post->id}", [
            'body' => 'Tentative d’usurpation de modification',
        ]);
        $responseUpdate->assertStatus(403);
        $this->assertEquals('Post appartenant à Alice', $post->fresh()->body);

        // Bob tente de supprimer le post d'Alice
        $responseDelete = $this->delete("/publications/{$post->id}");
        $responseDelete->assertStatus(403);
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    /**
     * Test 7 : Les commentaires fermés sont refusés côté serveur (403).
     */
    public function test_commenting_on_closed_post_is_forbidden_server_side(): void
    {
        $post = Post::create([
            'user_id' => $this->user1->id,
            'body' => 'Post avec commentaires fermés',
            'comments_allowed' => false,
            'status' => 'published',
        ]);

        $this->actingAs($this->user2);
        $response = $this->post("/publications/{$post->id}/commentaires", [
            'body' => 'Commentaire forcé',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('comments', ['post_id' => $post->id]);
    }

    /**
     * Test 13 : Le blocage agit sur le fil, les liens directs et les commentaires.
     */
    public function test_blocking_hides_content_and_prevents_interactions_bidirectionally(): void
    {
        $postAlice = Post::create([
            'user_id' => $this->user1->id,
            'body' => 'Secret d’Alice',
            'status' => 'published',
        ]);

        // Bob bloque Alice
        Block::create([
            'blocker_id' => $this->user2->id,
            'blocked_id' => $this->user1->id,
        ]);

        // 1. Le fil de Bob ne doit plus contenir le post d'Alice
        $this->actingAs($this->user2);
        $feedBob = Post::feedFor($this->user2)->pluck('id')->all();
        $this->assertNotContains($postAlice->id, $feedBob);

        // 2. Accès direct au post d'Alice par Bob -> 403
        $responseDirect = $this->get("/publications/{$postAlice->id}");
        $responseDirect->assertStatus(403);

        // 3. Bob ne peut pas commenter le post d'Alice -> 403
        $responseComment = $this->post("/publications/{$postAlice->id}/commentaires", [
            'body' => 'Tentative de commentaire sous blocage',
        ]);
        $responseComment->assertStatus(403);

        // 4. De son côté, Alice ne peut pas voir le profil de Bob -> 404
        $this->actingAs($this->user1);
        $responseProfile = $this->get("/membres/bob");
        $responseProfile->assertStatus(404);
    }

    /**
     * Test 14 : Un non-membre ne récupère pas une image privée par son URL (403 ou redirection login).
     */
    public function test_guest_cannot_access_private_media(): void
    {
        Storage::fake('local');
        $filePath = 'private/media/test_secure.webp';
        Storage::disk('local')->put($filePath, 'fake_bytes');

        $media = Media::create([
            'user_id' => $this->user1->id,
            'disk' => 'local',
            'file_path' => $filePath,
            'original_name' => 'secret_doc',
            'mime_type' => 'image/webp',
            'file_size' => 10,
            'file_hash' => 'fake_hash',
        ]);

        // Visiteur non connecté
        $response = $this->get("/media/{$media->id}");
        // Redirection vers login ou 403
        $this->assertTrue(in_array($response->status(), [302, 403]));
    }

    /**
     * Test 15 : Un membre ordinaire ne peut accéder aux actions admin (403).
     */
    public function test_regular_member_cannot_access_admin_dashboard_or_actions(): void
    {
        $this->actingAs($this->user1);

        $responseDashboard = $this->get('/admin');
        $responseDashboard->assertStatus(403);

        $responseBan = $this->post("/admin/membres/{$this->user2->id}/moderer", [
            'action' => 'ban',
            'reason' => 'Tentative non autorisée',
        ]);
        $responseBan->assertStatus(403);
    }

    /**
     * Test 16 : Les textes XSS sont échappés sans exécution et les SVG/fichiers interdits sont refusés.
     */
    public function test_xss_payload_is_escaped_and_svg_uploads_are_rejected(): void
    {
        $this->actingAs($this->user1);

        // 1. Textes XSS
        $xssText = '<script>alert("XSS")</script><img src="x" onerror="alert(1)">Texte normal';
        $response = $this->post('/publications', [
            'body' => $xssText,
        ]);
        $response->assertRedirect('/fil');

        $createdPost = Post::where('user_id', $this->user1->id)->latest('id')->first();
        $showResponse = $this->get("/publications/{$createdPost->id}");
        $showResponse->assertStatus(200);

        // Vérifier que le contenu brut <script> n'est pas injecté tel quel
        $showResponse->assertDontSee('<script>alert("XSS")</script>', false);
        $showResponse->assertSee('&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;', false);

        // 2. Tentative d'envoi d'un fichier SVG ou PHP
        $svgFile = UploadedFile::fake()->create('malicious.svg', 100, 'image/svg+xml');
        $responseSvg = $this->post('/publications', [
            'body' => 'Post avec svg',
            'image' => $svgFile,
        ]);
        $responseSvg->assertSessionHasErrors('image');
    }
}
