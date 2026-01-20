<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // Solo la creamos si no existe todavía
            if (! Schema::hasColumn('accounts', 'import_raw')) {
                // Si quieres colocarla justo después de 'notes' y esa columna existe:
                if (Schema::hasColumn('accounts', 'notes')) {
                    $table->json('import_raw')->nullable()->after('notes');
                } else {
                    // Si 'notes' no existe, la añadimos al final de la tabla
                    $table->json('import_raw')->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            if (Schema::hasColumn('accounts', 'import_raw')) {
                $table->dropColumn('import_raw');
            }
        });
    }
};
