<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitud_assignment_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');                // Nombre visible de la regla
            $table->string('field');               // origen, estado, prioridad, account_country, etc.
            $table->string('operator');            // equals, contains, etc.
            $table->string('value')->nullable();   // valor a comparar
            $table->foreignId('owner_user_id')     // comercial asignado
                ->constrained('users')
                ->cascadeOnDelete();
            $table->unsignedInteger('priority')    // 1 = más importante
                ->default(100);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitud_assignment_rules');
    }
};
