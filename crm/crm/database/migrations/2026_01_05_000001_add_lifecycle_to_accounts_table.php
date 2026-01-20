<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // Si no existe, añadimos lifecycle como string nullable + index
            if (!Schema::hasColumn('accounts', 'lifecycle')) {
                $table->string('lifecycle')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            if (Schema::hasColumn('accounts', 'lifecycle')) {
                // Drop index (Laravel crea uno automático para index())
                $table->dropIndex(['lifecycle']);
                $table->dropColumn('lifecycle');
            }
        });
    }
};
