<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contact_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')
                ->constrained('contacts')
                ->cascadeOnDelete();

            $table->string('role', 50);
            $table->string('label_personalizado')->nullable();

            $table->timestamps();

            $table->index('role');
            $table->unique(['contact_id', 'role', 'label_personalizado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_roles');
    }
};
