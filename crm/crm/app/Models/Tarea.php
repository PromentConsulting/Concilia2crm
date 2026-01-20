<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarea extends Model
{
    use HasFactory;

    protected $table = 'tareas';

    protected $fillable = [
        'tipo',
        'titulo',
        'descripcion',
        'estado',
        'fecha_inicio',
        'fecha_vencimiento',
        'fecha_completada',
        'owner_user_id',
        'owner_team_id',
        'account_id',
        'contact_id',
        'solicitud_id',
        'peticion_id',
        'pedido_id',
    ];

    protected $casts = [
        'fecha_inicio'      => 'datetime',
        'fecha_vencimiento' => 'datetime',
        'fecha_completada'  => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function peticion()
    {
        return $this->belongsTo(Peticion::class);
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}
