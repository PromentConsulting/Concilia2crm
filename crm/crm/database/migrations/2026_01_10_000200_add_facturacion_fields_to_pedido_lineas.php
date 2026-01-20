<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedido_lineas', function (Blueprint $table) {
            $table->decimal('qty_facturada', 10, 2)->default(0)->after('importe_con_iva');
            $table->decimal('base_facturada', 10, 2)->default(0)->after('qty_facturada');
            $table->decimal('importe_facturado', 10, 2)->default(0)->after('base_facturada');
        });
    }

    public function down(): void
    {
        Schema::table('pedido_lineas', function (Blueprint $table) {
            $table->dropColumn(['qty_facturada', 'base_facturada', 'importe_facturado']);
        });
    }
};