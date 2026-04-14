<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('legacy_fournisseur_debt_payments');
        Schema::dropIfExists('legacy_fournisseur_debts');
        Schema::dropIfExists('legacy_client_debt_payments');
        Schema::dropIfExists('legacy_client_debts');

        Schema::create('legacy_client_debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('client_models')->cascadeOnDelete();
            $table->foreignId('devise_id')->constrained('devise_models')->restrictOnDelete();
            $table->decimal('due_amount', 15, 2);
            $table->date('debt_date')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('legacy_client_debt_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_client_debt_id');
            $table->date('date_paiement')->useCurrent();
            $table->decimal('montant', 15, 2);
            $table->string('mode_paiement')->default('cash');
            $table->string('reference', 100)->unique();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('legacy_client_debt_id', 'lcdp_debt_fk')
                ->references('id')
                ->on('legacy_client_debts')
                ->cascadeOnDelete();
        });

        Schema::create('legacy_fournisseur_debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fournisseur_id')->constrained('fournisseur_models')->cascadeOnDelete();
            $table->foreignId('devise_id')->constrained('devise_models')->restrictOnDelete();
            $table->decimal('due_amount', 15, 2);
            $table->date('debt_date')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('legacy_fournisseur_debt_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_fournisseur_debt_id');
            $table->date('date_paiement')->useCurrent();
            $table->decimal('montant', 15, 2);
            $table->string('mode_paiement')->default('cash');
            $table->string('reference', 100)->unique();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('legacy_fournisseur_debt_id', 'lfdp_debt_fk')
                ->references('id')
                ->on('legacy_fournisseur_debts')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_fournisseur_debt_payments');
        Schema::dropIfExists('legacy_fournisseur_debts');
        Schema::dropIfExists('legacy_client_debt_payments');
        Schema::dropIfExists('legacy_client_debts');
    }
};
