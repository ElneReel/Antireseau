<?php

namespace Database\Seeders;

use App\Models\Bookmark;
use App\Models\Comment;
use App\Models\Media;
use App\Models\Post;
use App\Models\Profile;
use App\Models\Report;
use App\Models\ReportEvidence;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            return;
        }

        $this->command->info("Réinitialisation du jeu de données immersif...");

        Bookmark::query()->delete();
        Comment::query()->delete();
        ReportEvidence::query()->delete();
        Report::query()->delete();
        Media::query()->delete();
        Post::query()->delete();
        Profile::query()->delete();
        User::query()->delete();

        $passwordHash = Hash::make('MotDePasseMembre123!');
        $adminPasswordHash = Hash::make('MotDePasseAdmin123!');

        // 1. Administrateur
        $admin = User::create([
            'email' => 'admin@insociable.local',
            'password' => $adminPasswordHash,
            'role' => 'admin',
            'status' => 'active',
            'terms_accepted_at' => Carbon::now('UTC')->subDays(10),
            'is_adult_confirmed' => true,
        ]);
        Profile::create([
            'user_id' => $admin->id,
            'handle' => 'admin',
            'handle_lower' => 'admin',
            'display_name' => 'Modération Insociable',
            'bio' => 'Compte officiel de modération et sécurité de la communauté.',
        ]);

        // 2. Membres
        $members = [
            'alice' => ['Alice Martin', 'Photographe & Designeuse. Amoureuse des grands espaces.'],
            'bob' => ['Bob Durand', 'Beatmaker & sound designer. Des ondes, du grain, pas d’algorithme.'],
            'camille' => ['Camille V.', 'Artisanat & céramique. Vivre à son propre rythme.'],
            'david' => ['David R.', 'Architecte urbain. Les lignes simples qui durent.'],
            'emma' => ['Emma S.', 'Cinéaste nomade. Capturer la lumière de l’aube.'],
        ];

        $users = ['admin' => $admin];
        foreach ($members as $handle => [$name, $bio]) {
            $u = User::create([
                'email' => "{$handle}@insociable.local",
                'password' => $passwordHash,
                'role' => 'member',
                'status' => 'active',
                'terms_accepted_at' => Carbon::now('UTC')->subDays(5),
                'is_adult_confirmed' => true,
            ]);
            Profile::create([
                'user_id' => $u->id,
                'handle' => $handle,
                'handle_lower' => strtolower($handle),
                'display_name' => $name,
                'bio' => $bio,
            ]);
            $users[$handle] = $u;
        }

        // 3. Génération des fichiers médias de démonstration (Photos verticales 9:16 + Audio interactif)
        $mediaDir = 'private/media';
        Storage::disk('local')->makeDirectory($mediaDir);

        // A. Générer une piste audio WAV relaxante (Fréquence 432 Hz, onde sonore synthétique pure de 6 secondes)
        $audioPath = $this->generateAmbientWaveAudio($mediaDir);

        // B. Générer 3 superbes visuels verticaux 9:16 (format Instagram Reels / Stories 1080x1920)
        $photoPaths = $this->generateVerticalPhotos($mediaDir);

        $now = Carbon::now('UTC');

        // Post 1 : Photo verticale artistique
        $post1 = Post::create([
            'user_id' => $users['alice']->id,
            'body' => "Lueur d'or sur les crêtes des Vosges ce matin. Aucun filtre, juste la lumière brute.",
            'comments_allowed' => true,
            'status' => 'published',
            'content_hash' => hash('sha256', 'post_1'),
            'created_at' => $now->copy()->subHours(1),
        ]);
        Media::create([
            'user_id' => $users['alice']->id,
            'post_id' => $post1->id,
            'disk' => 'local',
            'file_path' => $photoPaths[0],
            'original_name' => 'lumiere_vosges_matin',
            'mime_type' => 'image/webp',
            'media_type' => 'image',
            'file_size' => 45000,
            'width' => 1080,
            'height' => 1920,
            'alt_text' => 'Aurore dorée sur les montagnes',
            'file_hash' => hash('sha256', 'vosges_img'),
        ]);

        // Post 2 : Audio immersif
        $post2 = Post::create([
            'user_id' => $users['bob']->id,
            'body' => "Extrait d'une boucle ambient 432 Hz composée hier soir sur synthé modulaire. Mettez un casque 🎧",
            'comments_allowed' => true,
            'status' => 'published',
            'content_hash' => hash('sha256', 'post_2'),
            'created_at' => $now->copy()->subHours(3),
        ]);
        Media::create([
            'user_id' => $users['bob']->id,
            'post_id' => $post2->id,
            'disk' => 'local',
            'file_path' => $audioPath,
            'original_name' => 'ambient_loop_432hz',
            'mime_type' => 'audio/wav',
            'media_type' => 'audio',
            'file_size' => 180000,
            'alt_text' => 'Piste audio ambient modulaire',
            'file_hash' => hash('sha256', 'audio_loop_bob'),
        ]);

        // Post 3 : Pensée typographique puissante
        Post::create([
            'user_id' => $users['camille']->id,
            'body' => "La créativité ne se mesure pas au nombre de vues. Elle s'épanouit dans l'espace que l'on s'accorde pour contempler sans être interrompu.",
            'comments_allowed' => true,
            'status' => 'published',
            'content_hash' => hash('sha256', 'post_3'),
            'created_at' => $now->copy()->subHours(6),
        ]);

        // Post 4 : Seconde photo verticale
        $post4 = Post::create([
            'user_id' => $users['david']->id,
            'body' => "L'escalier hélicoïdal en béton brut de la bibliothèque municipale. Géométrie pure.",
            'comments_allowed' => true,
            'status' => 'published',
            'content_hash' => hash('sha256', 'post_4'),
            'created_at' => $now->copy()->subHours(9),
        ]);
        Media::create([
            'user_id' => $users['david']->id,
            'post_id' => $post4->id,
            'disk' => 'local',
            'file_path' => $photoPaths[1],
            'original_name' => 'architecture_brut',
            'mime_type' => 'image/webp',
            'media_type' => 'image',
            'file_size' => 52000,
            'width' => 1080,
            'height' => 1920,
            'alt_text' => 'Escalier géométrique moderne',
            'file_hash' => hash('sha256', 'archi_img'),
        ]);

        // Post 5 : Troisième photo
        $post5 = Post::create([
            'user_id' => $users['emma']->id,
            'body' => "Tournage terminé. Dans la boîte.",
            'comments_allowed' => true,
            'status' => 'published',
            'content_hash' => hash('sha256', 'post_5'),
            'created_at' => $now->copy()->subHours(14),
        ]);
        Media::create([
            'user_id' => $users['emma']->id,
            'post_id' => $post5->id,
            'disk' => 'local',
            'file_path' => $photoPaths[2],
            'original_name' => 'fin_de_tournage',
            'mime_type' => 'image/webp',
            'media_type' => 'image',
            'file_size' => 48000,
            'width' => 1080,
            'height' => 1920,
            'alt_text' => 'Clap de fin de tournage cinématographique',
            'file_hash' => hash('sha256', 'tournage_img'),
        ]);

        // Commentaires de test
        Comment::create([
            'post_id' => $post1->id,
            'user_id' => $users['bob']->id,
            'body' => 'Les teintes sont somptueuses ! C’était vers quelle heure ?',
            'created_at' => $now->copy()->subMinutes(45),
        ]);
        Comment::create([
            'post_id' => $post1->id,
            'user_id' => $users['alice']->id,
            'body' => 'Vers 6h45, juste avant que la brume ne se dissipe 🌄',
            'created_at' => $now->copy()->subMinutes(30),
        ]);

        Comment::create([
            'post_id' => $post2->id,
            'user_id' => $users['camille']->id,
            'body' => 'Incroyable atmosphère, ça détend immédiatement.',
            'created_at' => $now->copy()->subHours(2),
        ]);

        // Sauvegarde de test
        Bookmark::create([
            'user_id' => $users['alice']->id,
            'post_id' => $post2->id,
            'created_at' => $now->copy()->subHour(),
        ]);

        $this->command->info("Nouveau jeu de données moderne déployé avec succès !");
    }

    protected function generateAmbientWaveAudio(string $dir): string
    {
        $sampleRate = 22050;
        $seconds = 5;
        $numSamples = $sampleRate * $seconds;
        $frequency = 432.0;

        $header = pack('a4Va4a4VvvVVvva4V',
            'RIFF',
            36 + $numSamples * 2,
            'WAVE',
            'fmt ',
            16,
            1, // PCM
            1, // Mono
            $sampleRate,
            $sampleRate * 2,
            2,
            16,
            'data',
            $numSamples * 2
        );

        $body = '';
        for ($i = 0; $i < $numSamples; $i++) {
            $t = $i / $sampleRate;
            $envelope = sin(M_PI * ($i / $numSamples)); // fade-in fade-out doux
            $val = (int) (sin(2 * M_PI * $frequency * $t) * 16000 * $envelope);
            $body .= pack('v', $val);
        }

        $filename = 'ambient_sound_' . Str::random(12) . '.wav';
        $fullPath = $dir . '/' . $filename;
        Storage::disk('local')->put($fullPath, $header . $body);

        return $fullPath;
    }

    protected function generateVerticalPhotos(string $dir): array
    {
        $palettes = [
            [[244, 63, 94], [168, 85, 247], [99, 102, 241]], // Sunset Neon
            [[59, 130, 246], [16, 185, 129], [6, 78, 59]],   // Northern Lights
            [[245, 158, 11], [239, 68, 68], [136, 19, 55]],  // Obsidian Flame
        ];

        $paths = [];
        foreach ($palettes as $idx => $colors) {
            $w = 720;
            $h = 1280;
            $img = imagecreatetruecolor($w, $h);

            for ($y = 0; $y < $h; $y++) {
                $ratio = $y / $h;
                if ($ratio < 0.5) {
                    $rSub = $ratio * 2;
                    $r = (int) ($colors[0][0] * (1 - $rSub) + $colors[1][0] * $rSub);
                    $g = (int) ($colors[0][1] * (1 - $rSub) + $colors[1][1] * $rSub);
                    $b = (int) ($colors[0][2] * (1 - $rSub) + $colors[1][2] * $rSub);
                } else {
                    $rSub = ($ratio - 0.5) * 2;
                    $r = (int) ($colors[1][0] * (1 - $rSub) + $colors[2][0] * $rSub);
                    $g = (int) ($colors[1][1] * (1 - $rSub) + $colors[2][1] * $rSub);
                    $b = (int) ($colors[1][2] * (1 - $rSub) + $colors[2][2] * $rSub);
                }
                $c = imagecolorallocate($img, $r, $g, $b);
                imageline($img, 0, $y, $w, $y, $c);
            }

            ob_start();
            imagewebp($img, null, 85);
            $data = ob_get_clean();
            imagedestroy($img);

            $filename = "reel_photo_{$idx}_" . Str::random(10) . '.webp';
            $fullPath = $dir . '/' . $filename;
            Storage::disk('local')->put($fullPath, $data);
            $paths[] = $fullPath;
        }

        return $paths;
    }
}
