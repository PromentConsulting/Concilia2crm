<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeticionLinea extends Model
{
    protected $table = 'peticion_lineas';

    protected $fillable = [
        'peticion_id',
        'service_id',
        'concepto',
        'descripcion',
        'cantidad',
        'precio_unitario',
        'descuento_porcentaje',
        'importe_total',
    ];

    public function peticion(): BelongsTo
    {
        return $this->belongsTo(Peticion::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}