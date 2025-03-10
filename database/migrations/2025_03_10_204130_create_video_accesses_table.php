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
        Schema::create('video_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained()->onDelete('cascade');
            $table->string('user_email');
            $table->string('token_id', 32)->unique();
            $table->integer('views_count')->default(0);
            $table->ipAddress('ip_address');
            $table->timestamp('last_viewed_at');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['token_id', 'user_email']);
            $table->index(['user_email', 'video_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_accesses');
    }
};
