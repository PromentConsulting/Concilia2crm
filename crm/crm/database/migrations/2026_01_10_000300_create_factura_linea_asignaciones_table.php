<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factura_linea_asignaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_linea_id')->constrained('factura_lineas')->cascadeOnDelete();
            $table->foreignId('pedido_linea_id')->constrained('pedido_lineas')->cascadeOnDelete();
            $table->decimal('cantidad', 10, 2);
            $table->decimal('base', 10, 2)->default(0);
            $table->decimal('importe', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_linea_asignaciones');
    }
};