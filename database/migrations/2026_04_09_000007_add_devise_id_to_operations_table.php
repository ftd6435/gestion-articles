<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operations', function (Blueprint $table) {
            $table->foreignId('devise_id')->nullable()->after('type_operation_id')->constrained('devise_models')->nullOnDelete();
        });

        $defaultDeviseId = DB::table('devise_models')->where('is_default', true)->value('id');
        if ($defaultDeviseId) {
            DB::table('operations')->whereNull('devise_id')->update(['devise_id' => $defaultDeviseId]);
        }
    }

    public function down(): void
    {
        Schema::table('operations', function (Blueprint $table) {
            $table->dropForeign(['devise_id']);
            $table->dropColumn('devise_id');
        });
    }
};
