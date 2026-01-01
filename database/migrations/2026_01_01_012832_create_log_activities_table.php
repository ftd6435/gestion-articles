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
        Schema::create('log_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('ip')->nullable();
            $table->string('machine')->nullable(); // device name / type
            $table->string('system')->nullable();  // OS (Windows, Linux, macOS, Android, iOS)
            $table->string('browser')->nullable(); // Chrome, Firefox, Safari, etc.

            $table->string('model')->nullable();   // App\Models\Post
            $table->string('action')->nullable();  // created, updated, deleted, login, etc.

            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_activities');
    }
};
