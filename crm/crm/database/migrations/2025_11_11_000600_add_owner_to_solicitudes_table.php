<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            if (! Schema::hasColumn('solicitudes', 'owner_user_id')) {
                $table->foreignId('owner_user_id')
                    ->nullable()
                    ->after('contact_id')   // ajusta la posición si quieres
                    ->constrained('users')
                    ->nullOnDelete();
            }

            // Opcional: si estás usando equipos (teams) como en cuentas/contactos
            if (Schema::hasTable('teams') && ! Schema::hasColumn('solicitudes', 'owner_team_id')) {
                $table->foreignId('owner_team_id')
                    ->nullable()
                    ->after('owner_user_id')
                    ->constrained('teams')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            if (Schema::hasColumn('solicitudes', 'owner_user_id')) {
                $table->dropConstrainedForeignId('owner_user_id');
            }

            if (Schema::hasColumn('solicitudes', 'owner_team_id')) {
                $table->dropConstrainedForeignId('owner_team_id');
            }
        });
    }
};
