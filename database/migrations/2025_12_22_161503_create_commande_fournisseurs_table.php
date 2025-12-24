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
        Schema::create('commande_fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->string('reference', length: 100)->unique();
            $table->foreignId('fournisseur_id')->constrained('fournisseur_models')->cascadeOnDelete();
            $table->foreignId('devise_id')->constrained('devise_models')->cascadeOnDelete();
            $table->decimal('taux_change', 15, 6)->default(0);
            $table->decimal('remise', 15, 2)->default(0);
            $table->date('date_commande')->useCurrent();
            $table->enum('status', ['EN_COURS', 'PARTIELLE', 'TERMINEE', 'ANNULEE'])->default('EN_COURS');
            // total_commande,
            // total_paye,
            // total_restant
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
        Schema::dropIfExists('commande_fournisseurs');
    }
};
