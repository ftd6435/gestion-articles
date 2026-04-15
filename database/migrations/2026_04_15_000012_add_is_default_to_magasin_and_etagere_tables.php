<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('magasin_models', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('localisation');
        });

        Schema::table('etagere_models', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('code_etagere');
        });

        $hasDefaultMagasin = DB::table('magasin_models')->where('is_default', true)->exists();
        if (!$hasDefaultMagasin) {
            $firstMagasinId = DB::table('magasin_models')->orderBy('id')->value('id');
            if ($firstMagasinId) {
                DB::table('magasin_models')->where('id', $firstMagasinId)->update(['is_default' => true]);
            }
        }

        $magasinIds = DB::table('etagere_models')->select('magasin_id')->distinct()->pluck('magasin_id');
        foreach ($magasinIds as $magasinId) {
            $hasDefaultEtagere = DB::table('etagere_models')
                ->where('magasin_id', $magasinId)
                ->where('is_default', true)
                ->exists();

            if ($hasDefaultEtagere) {
                continue;
            }

            $firstEtagereId = DB::table('etagere_models')
                ->where('magasin_id', $magasinId)
                ->orderBy('id')
                ->value('id');

            if ($firstEtagereId) {
                DB::table('etagere_models')->where('id', $firstEtagereId)->update(['is_default' => true]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('etagere_models', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });

        Schema::table('magasin_models', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
};
