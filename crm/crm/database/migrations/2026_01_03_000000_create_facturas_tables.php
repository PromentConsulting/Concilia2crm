<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->nullable()->index();
            $table->string('descripcion')->nullable();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos')->nullOnDelete();
            $table->date('fecha_factura')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->date('fecha_cobro')->nullable();
            $table->boolean('agrupar_referencias')->default(false);
            $table->boolean('cobrado')->default(false);
            $table->boolean('contabilizado')->default(false);
            $table->string('forma_pago')->nullable();
            $table->string('instruccion_pago')->nullable();
            $table->string('dpto_comercial')->nullable();
            $table->string('email_facturacion')->nullable();
            $table->decimal('importe', 15, 2)->nullable();
            $table->decimal('importe_total', 15, 2)->nullable();
            $table->string('moneda', 3)->default('EUR');
            $table->text('info_adicional')->nullable();
            $table->timestamps();
        });

        Schema::create('factura_lineas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->cascadeOnDelete();
            $table->string('referencia')->nullable();
            $table->string('concepto')->nullable();
            $table->decimal('cantidad', 12, 2)->default(1);
            $table->decimal('precio', 15, 2)->default(0);
            $table->decimal('descuento_porcentaje', 5, 2)->default(0);
            $table->decimal('iva_porcentaje', 5, 2)->default(21);
            $table->decimal('subtotal', 15, 2)->nullable();
            $table->decimal('importe', 15, 2)->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_lineas');
        Schema::dropIfExists('facturas');
    }
};