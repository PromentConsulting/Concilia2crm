<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->string('estado')->default('borrador')->after('numero');
            $table->string('tipo')->default('normal')->after('estado');
            $table->foreignId('serie_id')->nullable()->constrained('factura_series')->nullOnDelete()->after('tipo');
            $table->string('numero_serie')->nullable()->after('serie_id');
            $table->foreignId('factura_rectificada_id')->nullable()->constrained('facturas')->nullOnDelete()->after('numero_serie');
            $table->string('payment_state')->default('pendiente')->after('contabilizado');
            $table->decimal('descuento_global', 10, 2)->default(0)->after('email_facturacion');
            $table->decimal('redondeo', 10, 2)->default(0)->after('descuento_global');
            $table->timestamp('publicada_en')->nullable()->after('info_adicional');
            $table->timestamp('cancelada_en')->nullable()->after('publicada_en');
        });
    }

    public function down(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->dropForeign(['serie_id']);
            $table->dropForeign(['factura_rectificada_id']);
            $table->dropColumn([
                'estado',
                'tipo',
                'serie_id',
                'numero_serie',
                'factura_rectificada_id',
                'payment_state',
                'descuento_global',
                'redondeo',
                'publicada_en',
                'cancelada_en',
            ]);
        });
    }
};