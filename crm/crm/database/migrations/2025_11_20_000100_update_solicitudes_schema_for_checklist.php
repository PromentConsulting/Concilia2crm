<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            if (! Schema::hasColumn('solicitudes', 'tipo_servicio')) {
                $table->string('tipo_servicio')->nullable()->after('origen');
            }

            if (! Schema::hasColumn('solicitudes', 'titulo')) {
                $table->string('titulo')->nullable()->after('tipo_servicio');
            }

            if (! Schema::hasColumn('solicitudes', 'texto_peticion')) {
                $table->text('texto_peticion')->nullable()->after('descripcion');
            }

            if (! Schema::hasColumn('solicitudes', 'tipo_entidad')) {
                $table->string('tipo_entidad')->nullable()->after('texto_peticion');
            }

            if (! Schema::hasColumn('solicitudes', 'razon_social')) {
                $table->string('razon_social')->nullable()->after('tipo_entidad');
            }

            if (! Schema::hasColumn('solicitudes', 'provincia')) {
                $table->string('provincia')->nullable()->after('razon_social');
            }

            if (! Schema::hasColumn('solicitudes', 'num_plantilla')) {
                $table->unsignedInteger('num_plantilla')->nullable()->after('provincia');
            }

            if (! Schema::hasColumn('solicitudes', 'num_puesto_trabajo')) {
                $table->unsignedInteger('num_puesto_trabajo')->nullable()->after('num_plantilla');
            }

            if (Schema::hasColumn('solicitudes', 'asunto')) {
                $table->renameColumn('asunto', 'asunto_original');
            }

            if (! Schema::hasColumn('solicitudes', 'motivo_cierre')) {
                $table->string('motivo_cierre')->nullable()->after('prioridad');
            }

            if (! Schema::hasColumn('solicitudes', 'motivo_cierre_detalle')) {
                $table->text('motivo_cierre_detalle')->nullable()->after('motivo_cierre');
            }

            if (! Schema::hasColumn('solicitudes', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('fecha_cierre');
            }

            if (! Schema::hasColumn('solicitudes', 'source_external_id')) {
                $table->string('source_external_id')->nullable()->after('closed_at');
            }

            if (Schema::hasColumn('solicitudes', 'estado')) {
                $table->string('estado')->default('pendiente_asignacion')->change();
            }

            if (Schema::hasColumn('solicitudes', 'prioridad')) {
                $table->string('prioridad')->default('media')->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $columns = [
                'tipo_servicio',
                'titulo',
                'texto_peticion',
                'tipo_entidad',
                'razon_social',
                'provincia',
                'num_plantilla',
                'num_puesto_trabajo',
                'motivo_cierre',
                'motivo_cierre_detalle',
                'closed_at',
                'source_external_id',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('solicitudes', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('solicitudes', 'asunto_original')) {
                $table->renameColumn('asunto_original', 'asunto');
            }
        });
    }
};