<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Service;

class FacturaLinea extends Model
{
    use HasFactory;

    protected $table = 'factura_lineas';

    protected $fillable = [
        'factura_id',
        'service_id',
        'referencia',
        'concepto',
        'cantidad',
        'precio',
        'descuento_porcentaje',
        'iva_porcentaje',
        'subtotal',
        'importe',
        'orden',
    ];

    protected $casts = [
        'cantidad'             => 'decimal:2',
        'precio'               => 'decimal:2',
        'descuento_porcentaje' => 'decimal:2',
        'iva_porcentaje'       => 'decimal:2',
        'subtotal'             => 'decimal:2',
        'importe'              => 'decimal:2',
    ];

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'factura_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(FacturaLineaAsignacion::class, 'factura_linea_id');
    }

    public function impuestos(): BelongsToMany
    {
        return $this->belongsToMany(Impuesto::class, 'factura_linea_impuesto')
            ->withPivot(['base', 'importe'])
            ->withTimestamps();
    }
}