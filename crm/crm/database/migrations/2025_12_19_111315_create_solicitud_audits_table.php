<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('solicitud_audits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('solicitud_id')
                ->constrained('solicitudes')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('field');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();

            $table->timestamps();

            $table->index(['solicitud_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitud_audits');
    }
};
