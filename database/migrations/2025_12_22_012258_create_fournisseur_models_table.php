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
        Schema::create('fournisseur_models', function (Blueprint $table) {
            $table->id();
            $table->string('name', length: 100);
            $table->string('telephone', length: 30)->unique();
            $table->string('adresse', length: 100)->nullable();
            $table->boolean('status')->default(true);
            $table->string('image', length: 100)->default('avatar.png');
            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fournisseur_models');
    }
};
