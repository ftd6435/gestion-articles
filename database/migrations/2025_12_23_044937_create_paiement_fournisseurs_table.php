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
        Schema::create('paiement_fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')->constrained('commande_fournisseurs')->cascadeOnDelete();
            $table->foreignId('reception_id')->constrained('reception_fournisseurs')->cascadeOnDelete();
            $table->date('date_paiement')->useCurrent();
            $table->decimal('montant', 15, 2)->default(0);
            $table->string('mode_paiement')->default('cash');
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
        Schema::dropIfExists('paiement_fournisseurs');
    }
};
