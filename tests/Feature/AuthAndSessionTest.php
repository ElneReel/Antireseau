<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthAndSessionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1 : Deux personnes s'inscrivent et se connectent dans deux sessions séparées.
     */
    public function test_two_users_can_register_and_authenticate_separately(): void
    {
        // Inscription Utilisateur 1
        $response1 = $this->post('/inscription', [
            'email' => 'user1@example.com',
            'handle' => 'user_un',
            'display_name' => 'Utilisateur Un',
            'password' => 'MotDePasseFort123!',
            'password_confirmation' => 'MotDePasseFort123!',
            'terms_accepted' => '1',
            'is_adult' => '1',
        ]);
        $response1->assertRedirect('/fil');
        $this->assertAuthenticated();

        $user1 = User::where('email', 'user1@example.com')->first();
        $this->assertNotNull($user1);
        $this->assertEquals('user_un', $user1->profile->handle);

        // Déconnexion
        $this->post('/deconnexion');
        $this->assertGuest();

        // Inscription Utilisateur 2
        $response2 = $this->post('/inscription', [
            'email' => 'user2@example.com',
            'handle' => 'user_deux',
            'display_name' => 'Utilisateur Deux',
            'password' => 'MotDePasseFort123!',
            'password_confirmation' => 'MotDePasseFort123!',
            'terms_accepted' => '1',
            'is_adult' => '1',
        ]);
        $response2->assertRedirect('/fil');
        $this->assertAuthenticated();

        $user2 = User::where('email', 'user2@example.com')->first();
        $this->assertNotNull($user2);
        $this->assertNotEquals($user1->id, $user2->id);
    }

    /**
     * Test 17 : La suppression du compte révoque les sessions et retire ses contenus et fichiers physiques.
     */
    public function test_account_deletion_purges_contents_and_media_files(): void
    {
        Storage::fake('local');

        $user = User::factory()->create([
            'email' => 'delete_me@example.com',
            'password' => 'MonMotDePasseSecret123!',
        ]);
        $user->profile()->create([
            'handle' => 'deleteme',
            'handle_lower' => 'deleteme',
        ]);

        $filePath = 'private/media/dummy_file_to_delete.webp';
        Storage::disk('local')->put($filePath, 'binarycontent');

        $post = Post::create([
            'user_id' => $user->id,
            'body' => 'Ce post doit être effacé',
            'status' => 'published',
        ]);

        $media = Media::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'disk' => 'local',
            'file_path' => $filePath,
            'original_name' => 'dummy',
            'mime_type' => 'image/webp',
            'file_size' => 1234,
            'file_hash' => 'dummyhash',
        ]);

        $this->actingAs($user);
        $this->assertTrue(Storage::disk('local')->exists($filePath));

        // Suppression avec le mot de passe
        $response = $this->delete('/reglages/compte', [
            'password' => 'MonMotDePasseSecret123!',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/');
        $this->assertGuest();

        // Vérifier que le compte, le profil et le post n'existent plus
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
        $this->assertDatabaseMissing('media', ['id' => $media->id]);

        // Vérifier la purge physique du fichier sur disque
        $this->assertFalse(Storage::disk('local')->exists($filePath));
    }
}
