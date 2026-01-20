<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoPlazoPago extends Model
{
    use HasFactory;

    protected $table = 'pedido_plazos_pago';

    protected $fillable = [
        'pedido_id',
        'concepto',
        'porcentaje',
        'importe_a_facturar',
        'numero_factura',
        'fecha_factura',
        'fecha_prev_cobro',
        'orden',
    ];

    protected $casts = [
        'porcentaje'        => 'decimal:2',
        'importe_a_facturar'=> 'decimal:2',
        'fecha_factura'     => 'date',
        'fecha_prev_cobro'  => 'date',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
}
