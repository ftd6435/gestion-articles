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
        Schema::create('vente_models', function (Blueprint $table) {
            $table->id();
            $table->string('reference', length: 100)->unique();
            $table->foreignId('client_id')->constrained('client_models')->cascadeOnDelete();
            $table->foreignId('devise_id')->constrained('devise_models')->cascadeOnDelete();
            $table->decimal('taux', 15, 6)->default(0);
            $table->integer('remise')->default(0);
            $table->date('date_facture')->useCurrent();
            $table->enum('type_vente', ['GROS', 'DETAIL'])->default('DETAIL');
            $table->enum('status', ['PAYEE', 'PARTIELLE', 'IMPAYEE', 'ANNULEE'])->default('IMPAYEE');
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
        Schema::dropIfExists('vente_models');
    }
};
