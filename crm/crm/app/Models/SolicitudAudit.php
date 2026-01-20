<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudAudit extends Model
{
    protected $fillable = [
        'solicitud_id',
        'user_id',
        'field',
        'old_value',
        'new_value',
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
