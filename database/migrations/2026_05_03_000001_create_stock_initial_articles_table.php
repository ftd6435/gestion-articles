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
        Schema::create('stock_initial_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('article_models')->cascadeOnDelete();
            $table->foreignId('magasin_id')->constrained('magasin_models')->cascadeOnDelete();
            $table->foreignId('etagere_id')->constrained('etagere_models')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->date('date_expiration')->nullable()->comment('Expiration date if product is expirable');
            $table->date('date_inventaire')->comment('Date when inventory count was taken');
            $table->text('notes')->nullable()->comment('e.g., Opening inventory, Physical count');
            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            // Ensure unique combination: one initial stock entry per article per shelf
            $table->unique(['article_id', 'etagere_id'], 'unique_article_etagere');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_initial_articles');
    }
};
