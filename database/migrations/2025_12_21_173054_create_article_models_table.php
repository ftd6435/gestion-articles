<?php

use App\Models\Category;
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
        Schema::create('article_models', function (Blueprint $table) {
            $table->id();
            $table->string('reference', length: 100)->unique();
            $table->foreignIdFor(Category::class)->constrained()->cascadeOnDelete();
            $table->foreignId('devise_id')->constrained('devise_models')->cascadeOnDelete();
            $table->string('designation', length: 160)->nullable();
            $table->text('description')->nullable();
            $table->decimal('prix_achat', 15, 0)->default(0);
            $table->decimal('prix_vente', 15, 0)->default(0);
            $table->string('unite')->default('pièce');
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
        Schema::dropIfExists('article_models');
    }
};
