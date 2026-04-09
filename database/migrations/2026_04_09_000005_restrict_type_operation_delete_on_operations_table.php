<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operations', function (Blueprint $table) {
            $table->dropForeign(['type_operation_id']);
            $table->foreign('type_operation_id')
                ->references('id')
                ->on('type_operations')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('operations', function (Blueprint $table) {
            $table->dropForeign(['type_operation_id']);
            $table->foreign('type_operation_id')
                ->references('id')
                ->on('type_operations')
                ->cascadeOnDelete();
        });
    }
};
