<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('accounts', 'cnae')) {
            // Ampliar a VARCHAR(255) sin necesitar doctrine/dbal
            DB::statement('ALTER TABLE accounts MODIFY cnae VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('accounts', 'cnae')) {
            // Volver a 64 si hiciera falta
            DB::statement('ALTER TABLE accounts MODIFY cnae VARCHAR(64) NULL');
        }
    }
};
