<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Profils utilisateurs
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('handle', 24)->unique();
            $table->string('handle_lower', 24)->unique()->index();
            $table->string('display_name', 60)->nullable();
            $table->string('bio', 240)->nullable();
            $table->string('avatar_path')->nullable();
            $table->timestamps();
        });

        // Publications
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->boolean('comments_allowed')->default(true);
            $table->string('status', 20)->default('published')->index(); // 'published', 'hidden', 'removed'
            $table->string('content_hash', 64)->nullable()->index();
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();

            // Index pour le fil commun chronologique strict
            $table->index(['created_at', 'id']);
            $table->index(['user_id', 'created_at', 'id']);
        });

        // Médias (stockage privé)
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('disk', 30)->default('local');
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type', 50);
            $table->unsignedBigInteger('file_size');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt_text', 255)->nullable();
            $table->string('file_hash', 64)->index();
            $table->timestamps();
        });

        // Commentaires (un seul niveau, chronologique ascendant)
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->string('status', 20)->default('published')->index();
            $table->timestamps();

            $table->index(['post_id', 'created_at', 'id']);
            $table->index(['user_id', 'created_at']);
        });

        // Sauvegardes privées (bookmarks)
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'post_id']);
            $table->index(['user_id', 'created_at']);
        });

        // Blocages bidirectionnels
        Schema::create('blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blocker_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('blocked_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['blocker_id', 'blocked_id']);
            $table->index(['blocker_id', 'blocked_id']);
            $table->index(['blocked_id', 'blocker_id']);
        });

        // Signalements
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->string('target_type', 30)->index(); // 'post', 'comment', 'user'
            $table->unsignedBigInteger('target_id')->index();
            $table->string('reason', 100);
            $table->text('details')->nullable();
            $table->string('status', 20)->default('open')->index(); // 'open', 'resolved', 'dismissed'
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_note')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        // Preuves de signalement (snapshot immuable)
        Schema::create('report_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('reports')->cascadeOnDelete();
            $table->json('snapshot');
            $table->timestamps();
        });

        // Journal des décisions de modération
        Schema::create('moderation_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moderator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('target_type', 30)->nullable();
            $table->string('action', 50); // 'suspend_write_24h', 'lift_suspension', 'ban_account', 'unban_account', 'remove_post', 'restore_post', 'dismiss_report'
            $table->text('reason');
            $table->timestamps();

            $table->index(['moderator_id', 'created_at']);
        });

        // Événements antispam & rate limiting
        Schema::create('rate_limit_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action_type', 30); // 'post', 'comment'
            $table->string('status', 20); // 'accepted', 'rejected'
            $table->string('payload_hash', 64)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'action_type', 'created_at']);
            $table->index(['user_id', 'payload_hash', 'created_at']);
            $table->index(['created_at']);
        });

        // Clés d'idempotence
        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action_type', 30);
            $table->string('idempotency_key', 64);
            $table->integer('status_code')->default(200);
            $table->json('response_headers')->nullable();
            $table->longText('response_body')->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamps();

            $table->unique(['user_id', 'action_type', 'idempotency_key'], 'idempotency_user_action_key_unique');
        });

        // Contestations (Appeals) de suspension
        Schema::create('appeals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->string('status', 20)->default('pending')->index(); // 'pending', 'accepted', 'rejected'
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appeals');
        Schema::dropIfExists('idempotency_keys');
        Schema::dropIfExists('rate_limit_events');
        Schema::dropIfExists('moderation_actions');
        Schema::dropIfExists('report_evidence');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('blocks');
        Schema::dropIfExists('bookmarks');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('media');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('profiles');
    }
};
