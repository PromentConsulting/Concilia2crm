<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla principal de pedidos
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();

            $table->string('numero')->nullable()->index(); // PD202500007, etc.

            $table->foreignId('peticion_id')
                ->nullable()
                ->constrained('peticiones')
                ->nullOnDelete();

            $table->foreignId('account_id')
                ->nullable()
                ->constrained('accounts')
                ->nullOnDelete();

            $table->foreignId('contact_id')
                ->nullable()
                ->constrained('contacts')
                ->nullOnDelete();

            $table->date('fecha_pedido')->nullable();
            $table->string('descripcion')->nullable();

            // Estado general del pedido (simplificado)
            $table->string('estado_pedido')->default('pendiente'); // pendiente, confirmado, finalizado, borrador…

            $table->boolean('proyecto_justificado')->default(false);
            $table->integer('anio')->nullable();

            $table->string('forma_pago')->nullable();
            $table->boolean('es_formacion')->default(false);

            // Bloque datos proyecto / facturación
            $table->date('fecha_limite_memoria')->nullable();
            $table->string('dpto_consultor')->nullable();
            $table->string('dpto_comercial')->nullable();
            $table->string('estado_facturacion')->nullable();
            $table->string('subvencion')->nullable();
            $table->decimal('gasto_subcontratado', 15, 2)->nullable();

            $table->date('fecha_limite_proyecto')->nullable();
            $table->string('proyecto_externo')->nullable();
            $table->string('tipo_pago_proyecto')->nullable();
            $table->string('tipo_proyecto')->nullable();
            $table->boolean('mostrar_precios')->default(true);

            // Totales rápidos
            $table->decimal('importe_total', 15, 2)->nullable();
            $table->string('moneda', 3)->default('EUR');

            // Información de facturación
            $table->text('info_adicional')->nullable();
            $table->string('email_facturacion')->nullable();
            $table->boolean('facturar_primer_plazo')->default(false);
            $table->text('info_facturacion')->nullable();
            $table->boolean('facturar_segundo_plazo')->default(false);

            $table->timestamps();

            $table->index('estado_pedido');
            $table->index('anio');
        });

        // Líneas de pedido (productos / conceptos)
        Schema::create('pedido_lineas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pedido_id')
                ->constrained('pedidos')
                ->cascadeOnDelete();

            $table->string('referencia')->nullable();
            $table->string('descripcion')->nullable();

            $table->decimal('cantidad', 12, 2)->default(1);
            $table->decimal('precio', 15, 2)->default(0);
            $table->decimal('descuento_porcentaje', 5, 2)->default(0);
            $table->decimal('iva_porcentaje', 5, 2)->default(21);

            $table->date('fecha_limite_factura')->nullable();

            // Totales de la línea (opcionales, se pueden recalcular)
            $table->decimal('subtotal', 15, 2)->nullable();
            $table->decimal('importe_con_iva', 15, 2)->nullable();

            $table->unsignedInteger('orden')->default(0);

            $table->timestamps();
        });

        // Plazos de pago
        Schema::create('pedido_plazos_pago', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pedido_id')
                ->constrained('pedidos')
                ->cascadeOnDelete();

            $table->string('concepto')->nullable();
            $table->decimal('porcentaje', 5, 2)->nullable();
            $table->decimal('importe_a_facturar', 15, 2)->nullable();

            $table->string('numero_factura')->nullable();
            $table->date('fecha_factura')->nullable();
            $table->date('fecha_prev_cobro')->nullable();

            $table->unsignedInteger('orden')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_plazos_pago');
        Schema::dropIfExists('pedido_lineas');
        Schema::dropIfExists('pedidos');
    }
};
