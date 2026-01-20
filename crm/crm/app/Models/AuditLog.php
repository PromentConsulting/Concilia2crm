<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = ['user_id','model_type','model_id','event','old_values','new_values','ip','user_agent'];
    protected $casts = ['old_values'=>'array','new_values'=>'array'];
    public $timestamps = ['created_at'];
    const UPDATED_AT = null;
    public function user(){ return $this->belongsTo(\App\Models\User::class); }
}
