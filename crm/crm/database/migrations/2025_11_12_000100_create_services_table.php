<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('referencia')->unique();
            $table->text('descripcion');
            $table->foreignId('service_category_id')
                ->nullable()
                ->constrained('service_categories')
                ->nullOnDelete();
            $table->decimal('precio', 12, 2)->default(0);
            $table->text('notas')->nullable();
            $table->string('estado')->default('activo');
            $table->foreignId('owner_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};