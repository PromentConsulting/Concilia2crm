<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoLinea extends Model
{
    use HasFactory;

    protected $table = 'pedido_lineas';

    protected $fillable = [
        'pedido_id',
        'service_id',
        'referencia',
        'descripcion',
        'cantidad',
        'precio',
        'descuento_porcentaje',
        'iva_porcentaje',
        'fecha_limite_factura',
        'subtotal',
        'importe_con_iva',
        'qty_facturada',
        'base_facturada',
        'importe_facturado',
        'orden',
    ];

    protected $casts = [
        'cantidad'             => 'decimal:2',
        'precio'               => 'decimal:2',
        'descuento_porcentaje' => 'decimal:2',
        'iva_porcentaje'       => 'decimal:2',
        'fecha_limite_factura' => 'date',
        'subtotal'             => 'decimal:2',
        'importe_con_iva'      => 'decimal:2',
        'qty_facturada'        => 'decimal:2',
        'base_facturada'       => 'decimal:2',
        'importe_facturado'    => 'decimal:2',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function asignaciones()
    {
        return $this->hasMany(FacturaLineaAsignacion::class, 'pedido_linea_id');
    }
}
