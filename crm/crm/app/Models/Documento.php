<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    protected $table = 'documentos';

    protected $fillable = [
        'account_id',
        'solicitud_id',
        'peticion_id',
        'pedido_id',
        'owner_user_id',
        'titulo',
        'tipo',
        'descripcion',
        'fecha_documento',
        'ruta',
        'nombre_original',
        'mime',
        'tamano',
    ];

    protected $casts = [
        'fecha_documento' => 'date',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
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

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }
}
