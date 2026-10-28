<?php

namespace App\Services;

use App\Models\IdempotencyKey;
use App\Models\RateLimitEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AntispamService
{
    /**
     * Normalise le texte selon les spécifications :
     * Unicode normalisé (forme C ou compatible), espaces répétés réduits, trim début/fin.
     */
    public function normalizeContent(?string $text): string
    {
        if ($text === null) {
            return '';
        }

        // Normalisation Unicode si disponible
        if (class_exists(\Normalizer::class)) {
            $normalized = \Normalizer::normalize($text, \Normalizer::FORM_C);
            if ($normalized !== false) {
                $text = $normalized;
            }
        }

        // Remplacement des espaces multiples/retours par un espace unique pour le hash de duplication
        $text = preg_replace('/\s+/u', ' ', $text);

        return trim((string) $text);
    }

    /**
     * Calcule le condensat de contenu pour détection de doublons.
     * Pour une publication avec image seule, utilise le condensat sha256 du fichier image.
     */
    public function computePayloadHash(?string $text, ?string $fileHash = null): string
    {
        $normalizedText = $this->normalizeContent($text);

        if ($normalizedText === '' && $fileHash !== null) {
            return hash('sha256', 'file:' . $fileHash);
        }

        if ($fileHash !== null) {
            return hash('sha256', 'text:' . $normalizedText . '|file:' . $fileHash);
        }

        return hash('sha256', 'text:' . $normalizedText);
    }

    /**
     * Vérifie si une requête idempotente a déjà été enregistrée pour cet utilisateur et cette action.
     */
    public function checkIdempotency(User $user, string $actionType, ?string $idempotencyKey): ?IdempotencyKey
    {
        if (empty($idempotencyKey)) {
            return null;
        }

        return IdempotencyKey::where('user_id', $user->id)
            ->where('action_type', $actionType)
            ->where('idempotency_key', $idempotencyKey)
            ->where('expires_at', '>', Carbon::now('UTC'))
            ->first();
    }

    /**
     * Enregistre le résultat d'une clé d'idempotence.
     */
    public function storeIdempotencyResult(
        User $user,
        string $actionType,
        string $idempotencyKey,
        int $statusCode,
        array $headers = [],
        ?string $body = null
    ): IdempotencyKey {
        $ttlHours = (int) config('insociable.antispam.idempotency_ttl_hours', 24);

        return IdempotencyKey::updateOrCreate(
            [
                'user_id' => $user->id,
                'action_type' => $actionType,
                'idempotency_key' => $idempotencyKey,
            ],
            [
                'status_code' => $statusCode,
                'response_headers' => $headers,
                'response_body' => $body,
                'expires_at' => Carbon::now('UTC')->addHours($ttlHours),
            ]
        );
    }

    /**
     * Valide l'éligibilité de publication d'un utilisateur de manière atomique.
     * Retourne un tableau ['allowed' => bool, 'reason' => ?string, 'retry_after_seconds' => ?int, 'suspended' => bool]
     */
    public function validatePostSubmission(User $user, ?string $text, ?string $fileHash = null, ?string $ip = null): array
    {
        $now = Carbon::now('UTC');

        // 1. Vérification de la suspension d'écriture active
        if ($user->isWritingSuspended()) {
            $formattedEnd = $user->write_suspended_until->timezone(config('app.timezone', 'UTC'))->format('d/m/Y à H:i');
            return [
                'allowed' => false,
                'reason' => "Publication suspendue pour 24 h après des envois répétés. Tu pourras publier à nouveau le {$formattedEnd}. Tu peux continuer à lire.",
                'suspended' => true,
                'retry_after_seconds' => $now->diffInSeconds($user->write_suspended_until, false),
            ];
        }

        $minSpacingSeconds = config('insociable.antispam.min_seconds_between_posts', 60);
        $maxPerDay = config('insociable.antispam.max_posts_per_day', 5);
        $duplicateThreshold = config('insociable.antispam.duplicate_threshold', 3);
        $duplicateWindowMin = config('insociable.antispam.duplicate_window_minutes', 10);
        $burstAttemptsLimit = config('insociable.antispam.burst_attempts_limit', 10);
        $burstWindowMin = config('insociable.antispam.burst_window_minutes', 10);

        $payloadHash = $this->computePayloadHash($text, $fileHash);

        return DB::transaction(function () use (
            $user, $now, $payloadHash, $ip,
            $minSpacingSeconds, $maxPerDay, $duplicateThreshold,
            $duplicateWindowMin, $burstAttemptsLimit, $burstWindowMin
        ) {
            // Recharger l'utilisateur sous verrouillage si disponible
            $freshUser = User::where('id', $user->id)->lockForUpdate()->first() ?? $user;

            if ($freshUser->isWritingSuspended()) {
                $formattedEnd = $freshUser->write_suspended_until->timezone(config('app.timezone', 'UTC'))->format('d/m/Y à H:i');
                return [
                    'allowed' => false,
                    'reason' => "Publication suspendue pour 24 h après des envois répétés. Tu pourras publier à nouveau le {$formattedEnd}. Tu peux continuer à lire.",
                    'suspended' => true,
                    'retry_after_seconds' => $now->diffInSeconds($freshUser->write_suspended_until, false),
                ];
            }

            // Définir la fenêtre de référence pour ne pas recompter les événements d'une sanction passée
            $baselineTime = $freshUser->write_suspended_until && $freshUser->write_suspended_until->lt($now)
                ? $freshUser->write_suspended_until
                : $now->copy()->subDays(2);

            // A. Règle Doublon : même texte soumis 3 fois en 10 minutes (la 1ère comprise) -> refus du 3e et suspension 24h
            $duplicateWindowStart = $now->copy()->subMinutes($duplicateWindowMin);
            if ($duplicateWindowStart->lt($baselineTime)) {
                $duplicateWindowStart = $baselineTime;
            }

            $duplicateCount = RateLimitEvent::where('user_id', $freshUser->id)
                ->where('action_type', 'post')
                ->where('payload_hash', $payloadHash)
                ->where('created_at', '>=', $duplicateWindowStart)
                ->count();

            // S'il a déjà soumis ce même contenu (duplicateCount + 1 >= 3), déclencher suspension 24h
            if ($duplicateCount + 1 >= $duplicateThreshold) {
                $this->suspendUserWriting(
                    $freshUser,
                    24,
                    "Suspension automatique : même contenu soumis {$duplicateThreshold} fois en {$duplicateWindowMin} minutes."
                );

                $this->recordRateLimitEvent($freshUser, 'post', 'rejected', $payloadHash, $ip);

                $formattedEnd = $freshUser->fresh()->write_suspended_until->timezone(config('app.timezone', 'UTC'))->format('d/m/Y à H:i');

                return [
                    'allowed' => false,
                    'reason' => "Publication suspendue pour 24 h après des envois répétés. Tu pourras publier à nouveau le {$formattedEnd}. Tu peux continuer à lire.",
                    'suspended' => true,
                    'retry_after_seconds' => 86400,
                ];
            }

            // B. Règle Espacement : Deux publications espacées de moins de 60 secondes
            $lastAcceptedPost = RateLimitEvent::where('user_id', $freshUser->id)
                ->where('action_type', 'post')
                ->where('status', 'accepted')
                ->where('created_at', '>=', $baselineTime)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastAcceptedPost) {
                $secondsSinceLast = $lastAcceptedPost->created_at->diffInSeconds($now);

                if ($secondsSinceLast < $minSpacingSeconds) {
                    $remainingSeconds = $minSpacingSeconds - $secondsSinceLast;
                    $this->recordRateLimitEvent($freshUser, 'post', 'rejected', $payloadHash, $ip);
                    $this->checkBurstSuspension($freshUser, $now, $baselineTime, $burstAttemptsLimit, $burstWindowMin);

                    return [
                        'allowed' => false,
                        'reason' => "Patiente encore {$remainingSeconds} seconde(s) avant de publier à nouveau.",
                        'suspended' => $freshUser->fresh()->isWritingSuspended(),
                        'retry_after_seconds' => $remainingSeconds,
                    ];
                }
            }

            // C. Règle Quota : Plus de 5 publications acceptées sur 24 heures glissantes
            $postsLast24h = RateLimitEvent::where('user_id', $freshUser->id)
                ->where('action_type', 'post')
                ->where('status', 'accepted')
                ->where('created_at', '>=', $now->copy()->subHours(24))
                ->count();

            if ($postsLast24h >= $maxPerDay) {
                $this->recordRateLimitEvent($freshUser, 'post', 'rejected', $payloadHash, $ip);
                $this->checkBurstSuspension($freshUser, $now, $baselineTime, $burstAttemptsLimit, $burstWindowMin);

                return [
                    'allowed' => false,
                    'reason' => "Tu as atteint la limite de {$maxPerDay} publications par 24 heures. Le quota se libérera progressivement.",
                    'suspended' => $freshUser->fresh()->isWritingSuspended(),
                    'retry_after_seconds' => 3600,
                ];
            }

            // Tout est valide
            return [
                'allowed' => true,
                'reason' => null,
                'suspended' => false,
                'retry_after_seconds' => 0,
                'payload_hash' => $payloadHash,
            ];
        });
    }

    /**
     * Valide l'éligibilité de commentaire d'un utilisateur de manière atomique.
     * Règle : Maximum 5 acceptés par minute et 30 par heure. Même règle de burst de 10 rejets.
     */
    public function validateCommentSubmission(User $user, string $text, ?string $ip = null): array
    {
        $now = Carbon::now('UTC');

        if ($user->isWritingSuspended()) {
            $formattedEnd = $user->write_suspended_until->timezone(config('app.timezone', 'UTC'))->format('d/m/Y à H:i');
            return [
                'allowed' => false,
                'reason' => "Commentaires suspendus pour 24 h après des envois répétés. Tu pourras publier à nouveau le {$formattedEnd}.",
                'suspended' => true,
                'retry_after_seconds' => $now->diffInSeconds($user->write_suspended_until, false),
            ];
        }

        $maxPerMin = config('insociable.antispam.max_comments_per_minute', 5);
        $maxPerHour = config('insociable.antispam.max_comments_per_hour', 30);
        $burstAttemptsLimit = config('insociable.antispam.burst_attempts_limit', 10);
        $burstWindowMin = config('insociable.antispam.burst_window_minutes', 10);

        $payloadHash = $this->computePayloadHash($text);

        return DB::transaction(function () use (
            $user, $now, $payloadHash, $ip,
            $maxPerMin, $maxPerHour, $burstAttemptsLimit, $burstWindowMin
        ) {
            $freshUser = User::where('id', $user->id)->lockForUpdate()->first() ?? $user;

            if ($freshUser->isWritingSuspended()) {
                $formattedEnd = $freshUser->write_suspended_until->timezone(config('app.timezone', 'UTC'))->format('d/m/Y à H:i');
                return [
                    'allowed' => false,
                    'reason' => "Commentaires suspendus pour 24 h après des envois répétés. Tu pourras publier à nouveau le {$formattedEnd}.",
                    'suspended' => true,
                    'retry_after_seconds' => $now->diffInSeconds($freshUser->write_suspended_until, false),
                ];
            }

            $baselineTime = $freshUser->write_suspended_until && $freshUser->write_suspended_until->lt($now)
                ? $freshUser->write_suspended_until
                : $now->copy()->subDays(2);

            // Vérification 5 commentaires / minute
            $minCount = RateLimitEvent::where('user_id', $freshUser->id)
                ->where('action_type', 'comment')
                ->where('status', 'accepted')
                ->where('created_at', '>=', $now->copy()->subMinute())
                ->count();

            if ($minCount >= $maxPerMin) {
                $this->recordRateLimitEvent($freshUser, 'comment', 'rejected', $payloadHash, $ip);
                $this->checkBurstSuspension($freshUser, $now, $baselineTime, $burstAttemptsLimit, $burstWindowMin);

                return [
                    'allowed' => false,
                    'reason' => "Trop de commentaires en une minute (limite : {$maxPerMin}). Merci de ralentir.",
                    'suspended' => $freshUser->fresh()->isWritingSuspended(),
                    'retry_after_seconds' => 60,
                ];
            }

            // Vérification 30 commentaires / heure
            $hourCount = RateLimitEvent::where('user_id', $freshUser->id)
                ->where('action_type', 'comment')
                ->where('status', 'accepted')
                ->where('created_at', '>=', $now->copy()->subHour())
                ->count();

            if ($hourCount >= $maxPerHour) {
                $this->recordRateLimitEvent($freshUser, 'comment', 'rejected', $payloadHash, $ip);
                $this->checkBurstSuspension($freshUser, $now, $baselineTime, $burstAttemptsLimit, $burstWindowMin);

                return [
                    'allowed' => false,
                    'reason' => "Limite de {$maxPerHour} commentaires par heure atteinte.",
                    'suspended' => $freshUser->fresh()->isWritingSuspended(),
                    'retry_after_seconds' => 600,
                ];
            }

            return [
                'allowed' => true,
                'reason' => null,
                'suspended' => false,
                'retry_after_seconds' => 0,
                'payload_hash' => $payloadHash,
            ];
        });
    }

    /**
     * Enregistre un événement de rate limit (accepté ou rejeté).
     */
    public function recordRateLimitEvent(User $user, string $actionType, string $status, ?string $payloadHash = null, ?string $ip = null, ?Carbon $createdAt = null): RateLimitEvent
    {
        return RateLimitEvent::create([
            'user_id' => $user->id,
            'action_type' => $actionType,
            'status' => $status,
            'payload_hash' => $payloadHash,
            'ip_address' => $ip,
            'created_at' => $createdAt ?? Carbon::now('UTC'),
        ]);
    }

    /**
     * Vérifie la règle de burst :
     * 10 tentatives réellement distinctes au-delà des limites en 10 minutes -> suspension 24h.
     */
    public function checkBurstSuspension(User $user, Carbon $now, Carbon $baselineTime, int $limit = 10, int $windowMinutes = 10): void
    {
        $windowStart = $now->copy()->subMinutes($windowMinutes);
        if ($windowStart->lt($baselineTime)) {
            $windowStart = $baselineTime;
        }

        // Compter les tentatives rejetées distinctes par payload_hash ou id
        $rejectedCount = RateLimitEvent::where('user_id', $user->id)
            ->where('status', 'rejected')
            ->where('created_at', '>=', $windowStart)
            ->distinct('payload_hash')
            ->count('payload_hash');

        if ($rejectedCount >= $limit) {
            $this->suspendUserWriting(
                $user,
                24,
                "Suspension automatique : {$rejectedCount} tentatives rejetées distinctes en {$windowMinutes} minutes."
            );
        }
    }

    /**
     * Applique une suspension d'écriture de X heures (par défaut 24h).
     * Les essais pendant une suspension ne la prolongent pas.
     */
    public function suspendUserWriting(User $user, int $hours = 24, ?string $reason = null): void
    {
        // Si déjà suspendu, ne pas prolonger
        if ($user->isWritingSuspended()) {
            return;
        }

        $user->update([
            'write_suspended_until' => Carbon::now('UTC')->addHours($hours),
            'write_suspended_reason' => $reason ?? "Publication et commentaires suspendus pour {$hours} heures après détection d'envois anormaux.",
        ]);
    }

    /**
     * Lève manuellement la suspension d'un utilisateur.
     */
    public function liftSuspension(User $user): void
    {
        $user->update([
            'write_suspended_until' => null,
            'write_suspended_reason' => null,
        ]);
    }
}
