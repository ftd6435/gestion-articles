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
        Schema::create('etagere_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('magasin_id')->constrained('magasin_models')->cascadeOnDelete();
            $table->string('code_etagere', length: 100)->unique();
            $table->boolean('status')->default(true);
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
        Schema::dropIfExists('etagere_models');
    }
};
