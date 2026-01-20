<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitud_reglas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();

            // Criterios básicos
            $table->string('origen')->nullable()->index();    // web, telefono, email, otro
            $table->string('prioridad')->nullable()->index(); // alta, normal, baja
            $table->string('estado')->nullable()->index();    // nueva, abierta, etc.

            // Asignación
            $table->foreignId('owner_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->boolean('activo')->default(true);
            $table->unsignedInteger('orden')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitud_reglas');
    }
};
