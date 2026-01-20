<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peticiones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('solicitud_id')
                ->nullable()
                ->constrained('solicitudes')
                ->nullOnDelete();

            $table->foreignId('account_id')
                ->nullable()
                ->constrained('accounts')
                ->nullOnDelete();

            $table->foreignId('contact_id')
                ->nullable()
                ->constrained('contacts')
                ->nullOnDelete();

            $table->string('titulo');
            $table->text('descripcion')->nullable();

            $table->decimal('importe_total', 15, 2)->nullable();
            $table->string('moneda', 3)->default('EUR');

            // borrador, enviada, aceptada, rechazada, cancelada
            $table->string('estado')->default('borrador');

            $table->timestamp('fecha_envio')->nullable();
            $table->timestamp('fecha_respuesta')->nullable();

            $table->timestamps();

            $table->index('estado');
            $table->index('solicitud_id');
            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peticiones');
    }
};
