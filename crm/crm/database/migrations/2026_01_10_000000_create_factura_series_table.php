<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factura_series', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('prefijo')->nullable();
            $table->unsignedInteger('siguiente_numero')->default(1);
            $table->unsignedTinyInteger('padding')->default(4);
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_series');
    }
};