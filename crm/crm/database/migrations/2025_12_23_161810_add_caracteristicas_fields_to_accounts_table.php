<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {

            // Ponemos el bloque después de products_services (existe en tu tabla)
            $after = 'products_services';

            if (!Schema::hasColumn('accounts', 'car_plan_igualdad')) {
                $table->string('car_plan_igualdad', 20)->nullable()->after($after);
                $after = 'car_plan_igualdad';
            }
            if (!Schema::hasColumn('accounts', 'car_plan_igualdad_vigencia')) {
                $table->date('car_plan_igualdad_vigencia')->nullable()->after($after);
                $after = 'car_plan_igualdad_vigencia';
            }

            if (!Schema::hasColumn('accounts', 'car_plan_lgtbi')) {
                $table->string('car_plan_lgtbi', 20)->nullable()->after($after);
                $after = 'car_plan_lgtbi';
            }
            if (!Schema::hasColumn('accounts', 'car_plan_lgtbi_vigencia')) {
                $table->date('car_plan_lgtbi_vigencia')->nullable()->after($after);
                $after = 'car_plan_lgtbi_vigencia';
            }

            if (!Schema::hasColumn('accounts', 'car_protocolo_acoso_sexual')) {
                $table->string('car_protocolo_acoso_sexual', 20)->nullable()->after($after);
                $after = 'car_protocolo_acoso_sexual';
            }
            if (!Schema::hasColumn('accounts', 'car_protocolo_acoso_sexual_revision')) {
                $table->date('car_protocolo_acoso_sexual_revision')->nullable()->after($after);
                $after = 'car_protocolo_acoso_sexual_revision';
            }

            if (!Schema::hasColumn('accounts', 'car_protocolo_acoso_laboral')) {
                $table->string('car_protocolo_acoso_laboral', 20)->nullable()->after($after);
                $after = 'car_protocolo_acoso_laboral';
            }
            if (!Schema::hasColumn('accounts', 'car_protocolo_acoso_laboral_revision')) {
                $table->date('car_protocolo_acoso_laboral_revision')->nullable()->after($after);
                $after = 'car_protocolo_acoso_laboral_revision';
            }

            if (!Schema::hasColumn('accounts', 'car_protocolo_acoso_lgtbi')) {
                $table->string('car_protocolo_acoso_lgtbi', 20)->nullable()->after($after);
                $after = 'car_protocolo_acoso_lgtbi';
            }
            if (!Schema::hasColumn('accounts', 'car_protocolo_acoso_lgtbi_revision')) {
                $table->date('car_protocolo_acoso_lgtbi_revision')->nullable()->after($after);
                $after = 'car_protocolo_acoso_lgtbi_revision';
            }

            if (!Schema::hasColumn('accounts', 'car_vpt')) {
                $table->string('car_vpt', 20)->nullable()->after($after);
                $after = 'car_vpt';
            }

            if (!Schema::hasColumn('accounts', 'car_registro_retributivo')) {
                $table->string('car_registro_retributivo', 20)->nullable()->after($after);
                $after = 'car_registro_retributivo';
            }
            if (!Schema::hasColumn('accounts', 'car_registro_retributivo_revision')) {
                $table->date('car_registro_retributivo_revision')->nullable()->after($after);
                $after = 'car_registro_retributivo_revision';
            }

            if (!Schema::hasColumn('accounts', 'car_plan_igualdad_estrategico')) {
                $table->string('car_plan_igualdad_estrategico', 20)->nullable()->after($after);
                $after = 'car_plan_igualdad_estrategico';
            }
            if (!Schema::hasColumn('accounts', 'car_plan_igualdad_estrategico_vigencia')) {
                $table->date('car_plan_igualdad_estrategico_vigencia')->nullable()->after($after);
                $after = 'car_plan_igualdad_estrategico_vigencia';
            }

            if (!Schema::hasColumn('accounts', 'car_sistema_gestion')) {
                $table->string('car_sistema_gestion', 20)->nullable()->after($after);
            }
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $cols = [
                'car_plan_igualdad',
                'car_plan_igualdad_vigencia',
                'car_plan_lgtbi',
                'car_plan_lgtbi_vigencia',
                'car_protocolo_acoso_sexual',
                'car_protocolo_acoso_sexual_revision',
                'car_protocolo_acoso_laboral',
                'car_protocolo_acoso_laboral_revision',
                'car_protocolo_acoso_lgtbi',
                'car_protocolo_acoso_lgtbi_revision',
                'car_vpt',
                'car_registro_retributivo',
                'car_registro_retributivo_revision',
                'car_plan_igualdad_estrategico',
                'car_plan_igualdad_estrategico_vigencia',
                'car_sistema_gestion',
            ];

            foreach ($cols as $c) {
                if (Schema::hasColumn('accounts', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
