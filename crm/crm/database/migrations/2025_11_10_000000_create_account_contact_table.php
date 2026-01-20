<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('account_contact', function (Blueprint $table) {
            $table->id();

            $table->foreignId('account_id')
                ->constrained('accounts')
                ->cascadeOnDelete();

            $table->foreignId('contact_id')
                ->constrained('contacts')
                ->cascadeOnDelete();

            $table->enum('categoria', ['facturacion','comercial','direccion','otros'])
                ->default('otros');

            $table->boolean('es_principal')->default(false);

            $table->timestamps();

            $table->unique(['account_id', 'contact_id']);
            $table->index(['contact_id', 'es_principal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_contact');
    }
};
