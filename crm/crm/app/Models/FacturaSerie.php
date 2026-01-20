<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacturaSerie extends Model
{
    use HasFactory;

    protected $table = 'factura_series';

    protected $fillable = [
        'nombre',
        'prefijo',
        'siguiente_numero',
        'padding',
        'activa',
    ];

    protected $casts = [
        'siguiente_numero' => 'integer',
        'padding' => 'integer',
        'activa' => 'boolean',
    ];

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'serie_id');
    }
}