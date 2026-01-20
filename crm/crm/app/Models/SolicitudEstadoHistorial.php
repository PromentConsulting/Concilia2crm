<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudEstadoHistorial extends Model
{
    use HasFactory;

    protected $table = 'solicitud_estado_historial';

    protected $fillable = [
        'solicitud_id',
        'estado_anterior',
        'estado_nuevo',
        'user_id',
        'motivo_cierre',
        'motivo_cierre_detalle',
        'cambio_en',
    ];

    protected $casts = [
        'cambio_en' => 'datetime',
    ];

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}