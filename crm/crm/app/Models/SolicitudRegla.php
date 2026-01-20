<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudRegla extends Model
{
    use HasFactory;

    protected $table = 'solicitud_reglas';

    protected $fillable = [
        'nombre',
        'descripcion',
        'origen',
        'prioridad',
        'estado',
        'owner_user_id',
        'activo',
        'orden',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }
}
