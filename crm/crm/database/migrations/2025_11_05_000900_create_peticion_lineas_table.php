<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peticion_lineas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peticion_id')
                ->constrained('peticiones')
                ->onDelete('cascade');

            $table->string('concepto');                 // Nombre del servicio / línea
            $table->text('descripcion')->nullable();   // Detalle opcional

            $table->decimal('cantidad', 10, 2)->default(1);
            $table->decimal('precio_unitario', 10, 2)->default(0);
            $table->decimal('descuento_porcentaje', 5, 2)->default(0);
            $table->decimal('importe_total', 12, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peticion_lineas');
    }
};
