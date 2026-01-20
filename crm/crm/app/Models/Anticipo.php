<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Anticipo extends Model
{
    use HasFactory;

    protected $table = 'anticipos';

    protected $fillable = [
        'pedido_id',
        'factura_id',
        'estado',
        'descripcion',
        'importe',
        'aplicado_en',
    ];

    protected $casts = [
        'importe' => 'decimal:2',
        'aplicado_en' => 'datetime',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'factura_id');
    }
}