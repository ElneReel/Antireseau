# Insociable

> **Personne à suivre. Tout le monde ici.**
> Des publications dans l'ordre. Pas dans l'ordre qui arrange un algorithme.

Application web locale complète réalisée selon le cahier des charges d'**Insociable** : un réseau social sans amis, sans abonnements, sans compteurs de popularité et sans recommandation personnalisée. Les membres publient dans un fil commun, strictement chronologique (`created_at DESC, id DESC`), où la visibilité n'est influencée ni par les réactions, ni par les commentaires, ni par l'auteur.

---

## 1. Stack technique locale

- **Backend** : PHP 8.3 / Laravel 11.
- **Frontend** : Blade, Alpine.js (interactions légères uniquement), CSS personnalisé fondé sur les design tokens éditoriaux.
- **Compilation** : Vite (assets pré-compilés pour exécution autonome).
- **Base de données** : SQLite local (`database/database.sqlite`), compatible MySQL/MariaDB pour hébergement futur.
- **Stockage média** : Disque local privé avec service contrôlé par route sécurisée (`/media/{id}`) et réencodage GD (suppression des métadonnées EXIF/GPS).
- **Sessions & Cache** : Persistance en base SQLite.
- **Emails** : Transport local `log` (aucun service externe requis).
- **Tests** : Suite automatisée PHPUnit couvrant l'ensemble des règles critiques et d'acceptation.

---

## 2. Installation et exécution locale

### Prérequis
- **PHP** 8.2 ou supérieur (avec extensions `pdo_sqlite`, `sqlite3`, `curl`, `fileinfo`, `gd`, `intl`, `mbstring`, `openssl`).
- **Composer** (v2).
- **Node.js** (v18+) et **npm**.

*(Note : sur cette machine Windows, un PHP 8.3 autonome et Composer ont été installés dans `C:\Users\AllZ\.tools\php` et `C:\Users\AllZ\.tools`).*

---

### Commandes Windows PowerShell

Ouvrez un terminal PowerShell dans le dossier du projet (`c:\Users\AllZ\Documents\Antiso`) :

```powershell
# 1. Si PHP et Composer sont dans le dossier utilisateur .tools :
$env:Path = "C:\Users\AllZ\.tools\php;C:\Users\AllZ\.tools;" + $env:Path

# 2. Installation des dépendances PHP et JavaScript
composer install
npm install

# 3. Configuration de l'environnement (si .env n'existe pas)
Copy-Item .env.example .env
php artisan key:generate

# 4. Création de la base SQLite et exécution des migrations
php artisan migrate --force

# 5. Injection du jeu de données de démonstration locale
php artisan db:seed

# 6. Compilation finale des assets (mode autonome)
npm run build

# 7. Lancement du serveur applicatif local
php artisan serve --host=127.0.0.1 --port=8000
```

L'application est immédiatement accessible sur votre navigateur à l'adresse : **`http://127.0.0.1:8000`**

---

### Commandes macOS / Linux (Équivalent)

```bash
# 1. Installation des dépendances
composer install
npm install

# 2. Configuration d'environnement
cp .env.example .env
php artisan key:generate

# 3. Migrations et données de démonstration
touch database/database.sqlite
php artisan migrate --force
php artisan db:seed

# 4. Compilation des assets
npm run build

# 5. Démarrage du serveur local
php artisan serve --host=127.0.0.1 --port=8000
```

---

## 3. Comptes de démonstration locaux

Le seeder local (`php artisan db:seed`) initialise un jeu complet de 8 membres fictifs et 40 publications échelonnées :

| Compte | Email | Mot de passe | Rôle / Statut |
|---|---|---|---|
| **Administrateur** | `admin@insociable.local` | `MotDePasseAdmin123!` | Administrateur (accès à `/admin`) |
| **Membre témoin 1** | `alice@insociable.local` | `MotDePasseMembre123!` | Membre actif (@alice) |
| **Membre témoin 2** | `bob@insociable.local` | `MotDePasseMembre123!` | Membre actif (@bob) |
| **Membre suspendu** | `gregoire@insociable.local` | `MotDePasseMembre123!` | Membre avec suspension d'écriture 24h active (@gregoire) |

*Autres membres disponibles : `@camille`, `@david`, `@emma`, `@francois`, `@helene`.*

---

## 4. Règles métier et Antispam

Le service dédié `App\Services\AntispamService` applique des contrôles atomiques stricts configurés dans `config/insociable.php` et `.env` :

1. **Espacement** : Deux publications d'un même membre espacées de moins de 60 secondes sont refusées avec calcul dynamique du délai restant (sans suspension immédiate).
2. **Quota journalier** : Maximum 5 publications acceptées sur 24 heures glissantes. Au-delà, refus poli sans qualification automatique de spam.
3. **Commentaires** : Maximum 5 commentaires acceptés par minute et 30 par heure.
4. **Détection de doublons** : Même contenu soumis 3 fois en 10 minutes (texte normalisé en Unicode forme C sans espaces superflus, ou hash sha256 du fichier pour image seule) -> refus du 3e envoi et **suspension automatique de l'écriture pour 24 heures**.
5. **Protection contre le pilonnage (Burst)** : 10 tentatives distinctes rejetées au-delà des limites en 10 minutes -> **suspension d'écriture de 24 heures**.
6. **Portée de la sanction** : La suspension prive le compte de publication et de commentaire, mais lui laisse un accès complet à la lecture, à la suppression de ses contenus, à ses réglages et au formulaire de contestation (`/contestations/nouvelle`).
7. **Idempotence** : Envoi d'une clé d'idempotence (`X-Idempotency-Key` ou champ `idempotency_key`). Un retry réseau avec la même clé renvoie le résultat mémorisé sans créer de doublon ni compter d'infraction.

---

## 5. Tests d'acceptation validés

La suite automatisée PHPUnit (`php artisan test`) vérifie l'intégralité des 18 critères d'acceptation du cahier des charges :

- [x] **Test 1** : Inscription et authentification séparée de deux utilisateurs.
- [x] **Test 2** : Persistance des publications en base.
- [x] **Test 3** : Ordre chronologique strict identique (`created_at DESC, id DESC`) pour tous les membres hors blocages.
- [x] **Test 4** : Modifier ou commenter une ancienne publication ne la fait jamais remonter dans le fil.
- [x] **Test 5** : Pagination par curseur stable sans doublon même lors de l'arrivée d'une nouvelle publication.
- [x] **Test 6** : Impossibilité pour un utilisateur de modifier ou supprimer le contenu d'un tiers.
- [x] **Test 7** : Refus côté serveur (403) de tout commentaire sur une publication dont les commentaires sont fermés.
- [x] **Test 8** : Retry idempotent sans création de doublon ni pénalité.
- [x] **Test 9** : Déclenchement exact de 24h de suspension en cas de spam, sans prolongation par des essais pendant la sanction.
- [x] **Test 10** : Reprise automatique de l'écriture à expiration de la suspension sans intervention requise.
- [x] **Test 11** : Atteinte du quota journalier sans qualification automatique de spam.
- [x] **Test 12** : Refus des publications espacées de moins de 60 secondes.
- [x] **Test 13** : Blocage bilatéral agissant sur le fil, les liens directs et les commentaires.
- [x] **Test 14** : Impossibilité pour un non-membre d'accéder aux images privées par leur URL.
- [x] **Test 15** : Blocage côté serveur (403) des actions d'administration pour les membres ordinaires.
- [x] **Test 16** : Échappement systématique des injections XSS et rejet des formats exécutables ou SVG.
- [x] **Test 17** : Suppression de compte avec confirmation de mot de passe, révocation de session et purge physique des médias.
- [x] **Test 18** : Interface responsive mobile dès 360 px avec contrôles tactiles >= 44 px et conformité des contrastes.

Pour relancer les tests à tout moment :
```powershell
php artisan test
```

---

## 6. État des fonctionnalités

### Fonctionnalités terminées et vérifiées (V1)
- Accueil éditorial public avec signature « Personne à suivre. Tout le monde ici. »
- Inscription avec handle normalisé (a-z0-9_), vérification 18+ et acceptation des règles.
- Authentification avec protection de session et bannissement géré.
- Fil commun chronologique strict (`created_at DESC, id DESC`).
- Formulaire compact de publication (texte 2 000 car., image facultative 3 Mo, texte alt, fermeture des réponses).
- Commentaires un niveau (1 000 car., chronologique ascendant).
- Traitement d'images sécurisé (réencodage GD WebP, suppression EXIF/GPS, stockage privé, route auth avec en-têtes no-cache).
- Profils membres (`/membres/{handle}`) sans compteurs de followers ni statut de présence.
- Sauvegardes privées discrètes (`/sauvegardes`).
- Blocage mutuel bilatéral (fil, profils, commentaires).
- Service antispam atomique avec suspension 24h et clés d'idempotence.
- Espace de contestation (`/contestations/nouvelle`) pour membres suspendus.
- Espace d'administration (`/admin`) avec gestion des signalements, archivage immuable de preuves (`report_evidence`), modération avec motifs consignés.
- Réglages du compte et suppression définitive avec mot de passe et purge des fichiers.

### Fonctions exclues conformément au cahier des charges
- Messages privés, stories, audio hébergé, flux algorithmique, badges payants, IA intégrée, notifications push, géolocalisation.
