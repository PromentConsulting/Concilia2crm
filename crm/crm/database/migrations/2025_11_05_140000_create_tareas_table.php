<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tareas', function (Blueprint $table) {
            $table->id();

            // Datos básicos de la tarea
            $table->string('tipo', 50)->default('tarea'); // tarea, llamada, reunion, email...
            $table->string('titulo');
            $table->text('descripcion')->nullable();

            $table->string('estado', 50)->default('pendiente'); // pendiente, en_progreso, completada, cancelada

            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_vencimiento')->nullable();
            $table->timestamp('fecha_completada')->nullable();

            // Propietario
            $table->foreignId('owner_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('owner_team_id')
                ->nullable()
                ->constrained('teams')
                ->nullOnDelete();

            // Vínculos con otros módulos
            $table->foreignId('account_id')
                ->nullable()
                ->constrained('accounts')
                ->nullOnDelete();

            $table->foreignId('contact_id')
                ->nullable()
                ->constrained('contacts')
                ->nullOnDelete();

            $table->foreignId('solicitud_id')
                ->nullable()
                ->constrained('solicitudes')
                ->nullOnDelete();

            $table->foreignId('peticion_id')
                ->nullable()
                ->constrained('peticiones')
                ->nullOnDelete();

            $table->foreignId('pedido_id')
                ->nullable()
                ->constrained('pedidos')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tareas');
    }
};
