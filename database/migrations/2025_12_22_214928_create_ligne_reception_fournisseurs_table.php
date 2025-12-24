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
        Schema::create('ligne_reception_fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reception_id')->constrained('reception_fournisseurs')->cascadeOnDelete();
            $table->foreignId('article_id')->constrained('article_models')->cascadeOnDelete();
            $table->foreignId('magasin_id')->constrained('magasin_models')->cascadeOnDelete();
            $table->foreignId('etagere_id')->constrained('etagere_models')->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->date('date_expiration')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ligne_reception_fournisseurs');
    }
};
