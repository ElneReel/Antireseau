<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Identité de la plateforme
    |--------------------------------------------------------------------------
    */
    'name' => env('APP_NAME', 'Insociable'),
    'tagline' => 'Personne à suivre. Tout le monde ici.',
    'sub_tagline' => 'Des publications dans l’ordre. Pas dans l’ordre qui arrange un algorithme.',

    /*
    |--------------------------------------------------------------------------
    | Limites éditoriales
    |--------------------------------------------------------------------------
    */
    'post_max_length' => 2000,
    'comment_max_length' => 1000,
    'bio_max_length' => 240,
    'posts_per_page' => 20,

    /*
    |--------------------------------------------------------------------------
    | Médias
    |--------------------------------------------------------------------------
    */
    'media' => [
        'max_file_size_kb' => 3072, // 3 Mo
        'allowed_mimes' => ['image/jpeg', 'image/png', 'image/webp'],
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp'],
        'disk' => 'local',
        'storage_path' => 'private/media',
        'avatar_path' => 'private/avatars',
        'max_dimension' => 2560, // Limite max largeur/hauteur en pixels
    ],

    /*
    |--------------------------------------------------------------------------
    | Règles antispam et sanctions
    |--------------------------------------------------------------------------
    */
    'antispam' => [
        // Deux publications espacées de moins de 60 secondes : refus sans suspension
        'min_seconds_between_posts' => (int) env('ANTISPAM_MIN_SECONDS_BETWEEN_POSTS', 60),

        // Plus de 5 publications acceptées sur 24h glissantes : refus du quota sans suspension automatique
        'max_posts_per_day' => (int) env('ANTISPAM_MAX_POSTS_PER_DAY', 5),

        // Commentaires : maximum 5 acceptés par minute et 30 par heure
        'max_comments_per_minute' => (int) env('ANTISPAM_MAX_COMMENTS_PER_MINUTE', 5),
        'max_comments_per_hour' => (int) env('ANTISPAM_MAX_COMMENTS_PER_HOUR', 30),

        // Durée de suspension d'écriture (24 heures)
        'suspension_hours' => (int) env('ANTISPAM_SUSPENSION_HOURS', 24),

        // Même texte soumis 3 fois en 10 minutes (première fois comprise) -> refus du 3e et suspension 24h
        'duplicate_threshold' => (int) env('ANTISPAM_DUPLICATE_THRESHOLD', 3),
        'duplicate_window_minutes' => (int) env('ANTISPAM_DUPLICATE_WINDOW_MINUTES', 10),

        // 10 tentatives réellement distinctes au-delà des limites en 10 minutes -> suspension 24h
        'burst_attempts_limit' => (int) env('ANTISPAM_BURST_ATTEMPTS_LIMIT', 10),
        'burst_window_minutes' => (int) env('ANTISPAM_BURST_WINDOW_MINUTES', 10),

        // Durée de validité des clés d'idempotence (en heures)
        'idempotency_ttl_hours' => 24,

        // Durée de rétention des logs d'événements de rate limit (en jours)
        'rate_limit_retention_days' => 14,
    ],
];
