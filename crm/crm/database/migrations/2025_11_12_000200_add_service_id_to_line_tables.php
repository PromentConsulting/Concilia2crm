<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peticion_lineas', function (Blueprint $table) {
            $table->foreignId('service_id')
                ->nullable()
                ->after('peticion_id')
                ->constrained('services')
                ->nullOnDelete();
        });

        Schema::table('pedido_lineas', function (Blueprint $table) {
            $table->foreignId('service_id')
                ->nullable()
                ->after('pedido_id')
                ->constrained('services')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('peticion_lineas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_id');
        });

        Schema::table('pedido_lineas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_id');
        });
    }
};