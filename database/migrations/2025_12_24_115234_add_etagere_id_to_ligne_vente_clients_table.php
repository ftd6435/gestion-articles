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
        Schema::table('ligne_vente_clients', function (Blueprint $table) {
            $table->foreignId('etagere_id')->constrained('etagere_models')->cascadeOnDelete();
            $table->foreignId('magasin_id')->constrained('magasin_models')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ligne_vente_clients', function (Blueprint $table) {
            $table->dropForeign(['etagere_id', 'magasin_id']);
            $table->dropColumn(['etagere_id', 'magasin_id']);
        });
    }
};
