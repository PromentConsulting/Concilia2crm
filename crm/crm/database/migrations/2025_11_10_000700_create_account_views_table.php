<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('account_views', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('name');
            $table->boolean('is_default')->default(false);

            // Filtros simples (q, lifecycle, country, etc.) en JSON
            $table->json('filters')->nullable();

            // Columnas activas (array de keys: ['name','email',...])
            $table->json('columns')->nullable();

            $table->string('sort_column')->nullable();
            $table->string('sort_direction', 4)->default('asc');

            $table->timestamps();

            $table->index(['user_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_views');
    }
};
