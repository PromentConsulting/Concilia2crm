<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacturaLineaAsignacion extends Model
{
    use HasFactory;

    protected $table = 'factura_linea_asignaciones';

    protected $fillable = [
        'factura_linea_id',
        'pedido_linea_id',
        'cantidad',
        'base',
        'importe',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'base' => 'decimal:2',
        'importe' => 'decimal:2',
    ];

    public function facturaLinea(): BelongsTo
    {
        return $this->belongsTo(FacturaLinea::class, 'factura_linea_id');
    }

    public function pedidoLinea(): BelongsTo
    {
        return $this->belongsTo(PedidoLinea::class, 'pedido_linea_id');
    }
}