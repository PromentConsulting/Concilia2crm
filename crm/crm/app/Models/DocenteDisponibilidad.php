<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocenteDisponibilidad extends Model
{
    protected $table = 'docente_disponibilidades';

    protected $fillable = [
        'user_id',
        'inicio',
        'fin',
        'tipo',
        'nota',
    ];

    protected $casts = [
        'inicio' => 'datetime',
        'fin' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}