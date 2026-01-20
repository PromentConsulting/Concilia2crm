<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impuestos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->decimal('porcentaje', 10, 2);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('service_impuesto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('impuesto_id')->constrained('impuestos')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('factura_linea_impuesto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_linea_id')->constrained('factura_lineas')->cascadeOnDelete();
            $table->foreignId('impuesto_id')->constrained('impuestos')->cascadeOnDelete();
            $table->decimal('base', 10, 2)->default(0);
            $table->decimal('importe', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('factura_impuesto_totales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->cascadeOnDelete();
            $table->foreignId('impuesto_id')->constrained('impuestos')->cascadeOnDelete();
            $table->decimal('base', 10, 2)->default(0);
            $table->decimal('importe', 10, 2)->default(0);
            $table->timestamps();
            $table->unique(['factura_id', 'impuesto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_impuesto_totales');
        Schema::dropIfExists('factura_linea_impuesto');
        Schema::dropIfExists('service_impuesto');
        Schema::dropIfExists('impuestos');
    }
};