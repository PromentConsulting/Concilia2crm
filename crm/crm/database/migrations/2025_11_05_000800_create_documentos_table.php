<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('account_id')->nullable()
                ->constrained('accounts')->nullOnDelete();

            $table->foreignId('solicitud_id')->nullable()
                ->constrained('solicitudes')->nullOnDelete();

            $table->foreignId('peticion_id')->nullable()
                ->constrained('peticiones')->nullOnDelete();

            $table->foreignId('pedido_id')->nullable()
                ->constrained('pedidos')->nullOnDelete();

            $table->foreignId('owner_user_id')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->string('titulo');
            $table->string('tipo', 100)->nullable(); // contrato, oferta, factura...
            $table->text('descripcion')->nullable();

            $table->date('fecha_documento')->nullable();

            // Fichero físico
            $table->string('ruta');                 // path en storage
            $table->string('nombre_original')->nullable();
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('tamano')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
