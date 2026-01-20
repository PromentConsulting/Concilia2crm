<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            // Título visible de la solicitud (además de "asunto")
            if (! Schema::hasColumn('solicitudes', 'titulo')) {
                $table->string('titulo')->nullable()->after('asunto');
            }

            // Fecha prevista de gestión / cierre
            if (! Schema::hasColumn('solicitudes', 'fecha_prevista')) {
                $table->date('fecha_prevista')->nullable()->after('fecha_solicitud');
            }

            // Importe estimado asociado a la solicitud (si aplica)
            if (! Schema::hasColumn('solicitudes', 'importe_estimado')) {
                $table->decimal('importe_estimado', 15, 2)->nullable()->after('fecha_prevista');
            }

            // Moneda del importe estimado
            if (! Schema::hasColumn('solicitudes', 'moneda')) {
                $table->string('moneda', 10)->nullable()->default('EUR')->after('importe_estimado');
            }
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            if (Schema::hasColumn('solicitudes', 'titulo')) {
                $table->dropColumn('titulo');
            }
            if (Schema::hasColumn('solicitudes', 'fecha_prevista')) {
                $table->dropColumn('fecha_prevista');
            }
            if (Schema::hasColumn('solicitudes', 'importe_estimado')) {
                $table->dropColumn('importe_estimado');
            }
            if (Schema::hasColumn('solicitudes', 'moneda')) {
                $table->dropColumn('moneda');
            }
        });
    }
};
