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
        Schema::table('paiement_fournisseurs', function (Blueprint $table) {
            $table->string('reference', length: 100)->unique();
            $table->text('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paiement_fournisseurs', function (Blueprint $table) {
            $table->dropColumn(['reference', 'notes']);
        });
    }
};
