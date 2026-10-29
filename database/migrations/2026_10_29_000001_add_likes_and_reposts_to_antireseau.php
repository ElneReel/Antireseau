<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->timestamp('bumped_at')->nullable()->after('edited_at');
            $table->foreignId('last_reposted_by_id')->nullable()->after('bumped_at')->constrained('users')->nullOnDelete();
            $table->index(['status', 'bumped_at', 'id']);
        });

        // Initialiser bumped_at avec created_at pour les posts existants
        DB::table('posts')->whereNull('bumped_at')->update([
            'bumped_at' => DB::raw('created_at'),
        ]);

        Schema::create('post_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'post_id']);
            $table->index(['post_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('post_reposts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'post_id']);
            $table->index(['post_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_reposts');
        Schema::dropIfExists('post_likes');

        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['last_reposted_by_id']);
            $table->dropColumn(['bumped_at', 'last_reposted_by_id']);
        });
    }
};
