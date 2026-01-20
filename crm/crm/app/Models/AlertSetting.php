<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlertSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notify_overdue_tasks',
        'notify_open_solicitudes',
    ];

    protected $casts = [
        'notify_overdue_tasks'   => 'boolean',
        'notify_open_solicitudes'=> 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
