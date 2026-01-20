<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'referencia',
        'descripcion',
        'service_category_id',
        'precio',
        'notas',
        'estado',
        'owner_id',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function impuestos(): BelongsToMany
    {
        return $this->belongsToMany(Impuesto::class, 'service_impuesto')
            ->withTimestamps();
    }
}