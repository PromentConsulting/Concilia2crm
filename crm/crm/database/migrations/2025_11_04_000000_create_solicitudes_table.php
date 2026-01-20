<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('account_id')
                ->nullable()
                ->constrained('accounts')
                ->nullOnDelete();

            $table->foreignId('contact_id')
                ->nullable()
                ->constrained('contacts')
                ->nullOnDelete();

            $table->string('asunto');
            $table->text('descripcion')->nullable();

            // email, telefono, web, presencial, otro
            $table->string('origen')->default('otro');

            // abierta, en_progreso, resuelta, cerrada, cancelada
            $table->string('estado')->default('abierta');

            // baja, normal, alta, urgente
            $table->string('prioridad')->default('normal');

            $table->timestamp('fecha_solicitud')->nullable();
            $table->timestamp('fecha_cierre')->nullable();

            $table->timestamps();

            $table->index('estado');
            $table->index('origen');
            $table->index('prioridad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
