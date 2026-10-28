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
    /**
     * Exécute le seed local de démonstration.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command->error("Le seed de démonstration est strictement interdit en environnement de production.");
            return;
        }

        $this->command->info("Nettoyage et réinitialisation des données de test...");

        // Nettoyage préalable pour garantir l'idempotence
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

        // 1. Administrateur local
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
            'bio' => 'Compte officiel de modération et d’administration locale du lieu.',
        ]);

        // 2. Membres réguliers (8 membres)
        $membersData = [
            [
                'email' => 'alice@insociable.local',
                'handle' => 'alice',
                'display_name' => 'Alice Martin',
                'bio' => 'Typographie, papier vergé et longues marches sans téléphone.',
            ],
            [
                'email' => 'bob@insociable.local',
                'handle' => 'bob',
                'display_name' => 'Bob Durand',
                'bio' => 'Luthier amateur et amateur de silence numérique.',
            ],
            [
                'email' => 'camille@insociable.local',
                'handle' => 'camille',
                'display_name' => 'Camille',
                'bio' => 'Rien à vendre. Curieuse de littérature et d’artisanat.',
            ],
            [
                'email' => 'david@insociable.local',
                'handle' => 'david',
                'display_name' => 'David R.',
                'bio' => 'Architecture frugale et café noir.',
            ],
            [
                'email' => 'emma@insociable.local',
                'handle' => 'emma',
                'display_name' => 'Emma',
                'bio' => 'Botanique sauvage et carnet de croquis.',
            ],
            [
                'email' => 'francois@insociable.local',
                'handle' => 'francois',
                'display_name' => 'François B.',
                'bio' => 'Traducteur indépendant. Passionné d’essais et de poésie.',
            ],
            [
                'email' => 'gregoire@insociable.local',
                'handle' => 'gregoire',
                'display_name' => 'Grégoire',
                'bio' => 'Écrivain nocturne.',
                'suspended' => true, // Membre de test avec suspension d'écriture active
            ],
            [
                'email' => 'helene@insociable.local',
                'handle' => 'helene',
                'display_name' => 'Hélène',
                'bio' => 'Observatrice discrète des changements de saisons.',
            ],
        ];

        $users = [];
        foreach ($membersData as $data) {
            $suspendedUntil = !empty($data['suspended']) ? Carbon::now('UTC')->addHours(20) : null;
            $suspendedReason = !empty($data['suspended']) ? "Suspension de test : même contenu soumis 3 fois en 10 minutes." : null;

            $u = User::create([
                'email' => $data['email'],
                'password' => $passwordHash,
                'role' => 'member',
                'status' => 'active',
                'write_suspended_until' => $suspendedUntil,
                'write_suspended_reason' => $suspendedReason,
                'terms_accepted_at' => Carbon::now('UTC')->subDays(7),
                'is_adult_confirmed' => true,
            ]);

            Profile::create([
                'user_id' => $u->id,
                'handle' => $data['handle'],
                'handle_lower' => strtolower($data['handle']),
                'display_name' => $data['display_name'],
                'bio' => $data['bio'],
            ]);

            $users[$data['handle']] = $u;
        }

        // Créer 2 images de test géométriques élégantes avec GD pour les tests
        $testImagePaths = $this->generateTestImages();

        // 3. Quarante publications réparties sur 5 jours
        $postsText = [
            "Premier jour ici. L'absence d'algorithme donne une étrange sensation de calme.",
            "J'ai passé deux heures à relire un livre sans vérifier mon écran une seule fois.",
            "Pourquoi cherchions-nous toujours à plaire à une machine qui récompense l'agressivité ?",
            "Une tasse d'infusion verveine, une fenêtre ouverte sur la brume d'automne.",
            "Voici une petite illustration géométrique préparée ce matin.", // avec image
            "Le format court oblige à une sobriété salutaire.",
            "Est-ce que quelqu'un a déjà exploré la menuiserie traditionnelle japonaise ?",
            "Il pleut sur les toits en zinc. Le bruit est régulier et rassurant.",
            "Pas de likes, pas de retweet. Juste un texte qui existe là où on l'a posé.",
            "Rendez-vous à la bibliothèque municipale pour dénicher de vieux guides géographiques.",
            "La lumière rasante de 17h éclaire la reliure de mes dictionnaires.",
            "Une publication avec les commentaires fermés, juste pour poser une pensée sans attendre d'écho.",
            "Je préfère cent fois lire vingt personnes au hasard qu'un flux optimisé pour mon anxiété.",
            "Retour de marché : courges, poireaux et châtaignes fraîches.",
            "Quelques lignes sur la sobriété des interfaces web.",
            "Deuxième essai graphique partagé dans le fil.", // avec image
            "Le temps semble plus long quand on ne le découpe pas en notifications.",
            "Un extrait de Philippe Jaccottet pour égayer l'après-midi : « L'effacement soit ma façon de resplendir ».",
            "Qui d'autre travaille encore au stylo plume sur du papier épais ?",
            "Parfois, le silence complet est la meilleure des réponses.",
            "Le tri chronologique strict remet tout le monde à égalité, quel soulagement.",
            "Préparation d'un thé sombre pu-erh pour affronter la soirée fraîche.",
            "Les oiseaux migrateurs passent très haut dans le ciel ce matin.",
            "Une réflexion sur la vitesse : ralentir n'est pas reculer, c'est mieux voir.",
            "On peut enfin lire ce que les gens écrivent sans filtre de recommandation.",
            "Petite promenade en forêt : les feuilles commencent à tomber par milliers.",
            "La typographie avec empattements repose le regard après des années de sans-serif clinique.",
            "Ce soir, soupe de potimarron maison avec un filet d'huile de noisette.",
            "Un post sans prétention pour tester le fil d'actualité.",
            "Comment s'organise votre espace de travail quand vous devez vraiment vous concentrer ?",
            "Une pensée pour ceux qui préfèrent observer avant de prendre la parole.",
            "La simplicité est une conquête exigeante.",
            "L'air du matin sent la terre humide et la fumée de bois.",
            "Aucune recommandation personnalisée : on redécouvre la surprise du hasard bienveillant.",
            "Finir un carnet de notes est un moment toujours un peu solennel.",
            "Le bruit des touches mécaniques sous mes doigts.",
            "Une question : quel est le dernier texte qui vous a vraiment marqué cette semaine ?",
            "Le soleil se lève enfin derrière les collines.",
            "Prendre le temps de formuler une phrase complète plutôt qu'un slogan.",
            "Bienvenue à toutes celles et ceux qui rejoignent le lieu aujourd'hui."
        ];

        $now = Carbon::now('UTC');
        $createdPosts = [];

        foreach ($postsText as $index => $text) {
            // Échelonnement chronologique : du plus ancien (il y a 5 jours) au plus récent (il y a quelques minutes)
            $minutesAgo = (40 - $index) * 180; // espacés de 3 heures
            $createdAt = $now->copy()->subMinutes($minutesAgo);

            $authorHandles = ['alice', 'bob', 'camille', 'david', 'emma', 'francois', 'helene'];
            $authorHandle = $authorHandles[$index % count($authorHandles)];
            $author = $users[$authorHandle];

            $commentsAllowed = ($index !== 11); // Post 11 a les commentaires fermés

            $post = Post::create([
                'user_id' => $author->id,
                'body' => $text,
                'comments_allowed' => $commentsAllowed,
                'status' => 'published',
                'content_hash' => hash('sha256', 'text:' . trim($text)),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // Associer une image de test pour certains posts
            if ($index === 4 && isset($testImagePaths[0])) {
                Media::create([
                    'user_id' => $author->id,
                    'post_id' => $post->id,
                    'disk' => 'local',
                    'file_path' => $testImagePaths[0],
                    'original_name' => 'composition_artisanale',
                    'mime_type' => 'image/webp',
                    'file_size' => 12400,
                    'width' => 800,
                    'height' => 600,
                    'alt_text' => 'Dessin d’étude géométrique tons pastel',
                    'file_hash' => hash('sha256', 'dummy_seed_image_1'),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            } elseif ($index === 15 && isset($testImagePaths[1])) {
                Media::create([
                    'user_id' => $author->id,
                    'post_id' => $post->id,
                    'disk' => 'local',
                    'file_path' => $testImagePaths[1],
                    'original_name' => 'etude_forme',
                    'mime_type' => 'image/webp',
                    'file_size' => 14200,
                    'width' => 800,
                    'height' => 600,
                    'alt_text' => 'Composition minimaliste encre et lavis',
                    'file_hash' => hash('sha256', 'dummy_seed_image_2'),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            $createdPosts[] = $post;
        }

        // 4. Commentaires sur quelques publications
        Comment::create([
            'post_id' => $createdPosts[0]->id,
            'user_id' => $users['bob']->id,
            'body' => 'Tout à fait d’accord. On retrouve le plaisir de lire sans précipitation.',
            'created_at' => $createdPosts[0]->created_at->copy()->addMinutes(15),
            'updated_at' => $createdPosts[0]->created_at->copy()->addMinutes(15),
        ]);

        Comment::create([
            'post_id' => $createdPosts[0]->id,
            'user_id' => $users['camille']->id,
            'body' => 'La sensation de ne pas être manipulé par un algorithme est immédiate.',
            'created_at' => $createdPosts[0]->created_at->copy()->addMinutes(25),
            'updated_at' => $createdPosts[0]->created_at->copy()->addMinutes(25),
        ]);

        Comment::create([
            'post_id' => $createdPosts[6]->id,
            'user_id' => $users['francois']->id,
            'body' => 'Oui, l’art du joint à mi-bois sans colle ni clou. C’est fascinant.',
            'created_at' => $createdPosts[6]->created_at->copy()->addMinutes(30),
            'updated_at' => $createdPosts[6]->created_at->copy()->addMinutes(30),
        ]);

        // 5. Sauvegardes de test pour Alice
        Bookmark::create([
            'user_id' => $users['alice']->id,
            'post_id' => $createdPosts[0]->id,
            'created_at' => $now->copy()->subHours(10),
        ]);
        Bookmark::create([
            'user_id' => $users['alice']->id,
            'post_id' => $createdPosts[17]->id,
            'created_at' => $now->copy()->subHours(5),
        ]);

        // 6. Signalement de test avec preuve (snapshot) pour l'espace admin
        $reportedPost = $createdPosts[10];
        $report = Report::create([
            'reporter_id' => $users['bob']->id,
            'target_type' => 'post',
            'target_id' => $reportedPost->id,
            'reason' => 'spam',
            'details' => 'Signalement de démonstration pour tester l’interface d’administration locale.',
            'status' => 'open',
            'created_at' => $now->copy()->subHours(2),
        ]);

        ReportEvidence::create([
            'report_id' => $report->id,
            'snapshot' => [
                'type' => 'post',
                'id' => $reportedPost->id,
                'author_id' => $reportedPost->user_id,
                'author_handle' => $reportedPost->user->profile->handle,
                'body' => $reportedPost->body,
                'created_at' => $reportedPost->created_at->toISOString(),
            ],
            'created_at' => $now->copy()->subHours(2),
        ]);

        $this->command->info("Seed local terminé avec succès !");
        $this->command->line("--------------------------------------------------");
        $this->command->line("Administrateur : admin@insociable.local / MotDePasseAdmin123!");
        $this->command->line("Compte témoin 1 : alice@insociable.local / MotDePasseMembre123!");
        $this->command->line("Compte témoin 2 : bob@insociable.local   / MotDePasseMembre123!");
        $this->command->line("--------------------------------------------------");
    }

    /**
     * Génère localement des images de test légères en WebP via GD.
     */
    protected function generateTestImages(): array
    {
        $dir = 'private/media';
        Storage::disk('local')->makeDirectory($dir);

        $paths = [];

        for ($i = 1; $i <= 2; $i++) {
            $img = imagecreatetruecolor(800, 600);
            $bg = $i === 1 ? imagecolorallocate($img, 230, 223, 238) : imagecolorallocate($img, 242, 239, 232);
            imagefilledrectangle($img, 0, 0, 800, 600, $bg);

            $accent = imagecolorallocate($img, 101, 81, 138);
            imagefilledrectangle($img, 100, 100, 700, 500, $accent);

            $inner = imagecolorallocate($img, 250, 248, 243);
            imagefilledrectangle($img, 120, 120, 680, 480, $inner);

            ob_start();
            imagewebp($img, null, 80);
            $data = ob_get_clean();
            imagedestroy($img);

            $filename = "seed_test_image_{$i}_" . Str::random(10) . '.webp';
            $fullPath = $dir . '/' . $filename;
            Storage::disk('local')->put($fullPath, $data);

            $paths[] = $fullPath;
        }

        return $paths;
    }
}
