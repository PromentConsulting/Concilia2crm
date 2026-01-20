<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peticiones', function (Blueprint $table) {
            if (! Schema::hasColumn('peticiones', 'codigo')) {
                $table->string('codigo', 24)->nullable()->after('id');
            }

            if (! Schema::hasColumn('peticiones', 'anio')) {
                $table->unsignedSmallInteger('anio')->nullable()->after('codigo');
            }

            if (! Schema::hasColumn('peticiones', 'fecha_alta')) {
                $table->date('fecha_alta')->nullable()->after('anio');
            }

            if (! Schema::hasColumn('peticiones', 'fecha_limite_oferta')) {
                $table->date('fecha_limite_oferta')->nullable()->after('fecha_envio');
            }

            if (! Schema::hasColumn('peticiones', 'owner_user_id')) {
                $table->foreignId('owner_user_id')->nullable()->after('contact_id')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('peticiones', 'created_by_user_id')) {
                $table->foreignId('created_by_user_id')->nullable()->after('owner_user_id')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('peticiones', 'memoria')) {
                $table->boolean('memoria')->default(false)->after('descripcion');
            }

            if (! Schema::hasColumn('peticiones', 'info_cliente')) {
                $table->text('info_cliente')->nullable()->after('memoria');
            }

            if (! Schema::hasColumn('peticiones', 'subvencion_id')) {
                $table->unsignedInteger('subvencion_id')->nullable()->after('info_cliente');
            }

            if (! Schema::hasColumn('peticiones', 'tipo_proyecto')) {
                $table->string('tipo_proyecto', 80)->nullable()->after('subvencion_id');
            }

            if (! Schema::hasColumn('peticiones', 'gasto_subcontratado')) {
                $table->string('gasto_subcontratado', 255)->nullable()->after('tipo_proyecto');
            }

            if (! Schema::hasColumn('peticiones', 'info_adicional')) {
                $table->text('info_adicional')->nullable()->after('gasto_subcontratado');
            }

            if (! Schema::hasColumn('peticiones', 'info_facturacion')) {
                $table->text('info_facturacion')->nullable()->after('info_adicional');
            }

            if (! Schema::hasColumn('peticiones', 'comentarios')) {
                $table->text('comentarios')->nullable()->after('info_facturacion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('peticiones', function (Blueprint $table) {
            if (Schema::hasColumn('peticiones', 'codigo')) {
                $table->dropColumn('codigo');
            }
            if (Schema::hasColumn('peticiones', 'anio')) {
                $table->dropColumn('anio');
            }
            if (Schema::hasColumn('peticiones', 'fecha_alta')) {
                $table->dropColumn('fecha_alta');
            }
            if (Schema::hasColumn('peticiones', 'fecha_limite_oferta')) {
                $table->dropColumn('fecha_limite_oferta');
            }
            if (Schema::hasColumn('peticiones', 'owner_user_id')) {
                $table->dropConstrainedForeignId('owner_user_id');
            }
            if (Schema::hasColumn('peticiones', 'created_by_user_id')) {
                $table->dropConstrainedForeignId('created_by_user_id');
            }
            if (Schema::hasColumn('peticiones', 'memoria')) {
                $table->dropColumn('memoria');
            }
            if (Schema::hasColumn('peticiones', 'info_cliente')) {
                $table->dropColumn('info_cliente');
            }
            if (Schema::hasColumn('peticiones', 'subvencion_id')) {
                $table->dropColumn('subvencion_id');
            }
            if (Schema::hasColumn('peticiones', 'tipo_proyecto')) {
                $table->dropColumn('tipo_proyecto');
            }
            if (Schema::hasColumn('peticiones', 'gasto_subcontratado')) {
                $table->dropColumn('gasto_subcontratado');
            }
            if (Schema::hasColumn('peticiones', 'info_adicional')) {
                $table->dropColumn('info_adicional');
            }
            if (Schema::hasColumn('peticiones', 'info_facturacion')) {
                $table->dropColumn('info_facturacion');
            }
            if (Schema::hasColumn('peticiones', 'comentarios')) {
                $table->dropColumn('comentarios');
            }
        });
    }
};