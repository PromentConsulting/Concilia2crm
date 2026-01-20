<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedido_docente_horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('inicio');
            $table->dateTime('fin');
            $table->string('nota')->nullable();
            $table->timestamps();
            $table->index(['pedido_id', 'user_id', 'inicio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_docente_horarios');
    }
};