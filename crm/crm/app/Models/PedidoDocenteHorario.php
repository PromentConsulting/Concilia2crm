<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoDocenteHorario extends Model
{
    protected $table = 'pedido_docente_horarios';

    protected $fillable = [
        'pedido_id',
        'user_id',
        'inicio',
        'fin',
        'nota',
    ];

    protected $casts = [
        'inicio' => 'datetime',
        'fin' => 'datetime',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}