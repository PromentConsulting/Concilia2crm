<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Impuesto extends Model
{
    use HasFactory;

    protected $table = 'impuestos';

    protected $fillable = [
        'nombre',
        'porcentaje',
        'activo',
    ];

    protected $casts = [
        'porcentaje' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function servicios(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_impuesto')
            ->withTimestamps();
    }
}