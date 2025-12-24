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
        Schema::create('ligne_commande_fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')->constrained('commande_fournisseurs')->cascadeOnDelete();
            $table->foreignId('article_id')->constrained('article_models')->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ligne_commande_fournisseurs');
    }
};
