<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'subject','channel','direction','status','outcome','description',
        'scheduled_at','completed_at','user_id',
        'subject_type','subject_id'
    ];
    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
    public function subject(){ return $this->morphTo(); }
    public function user(){ return $this->belongsTo(\App\Models\User::class); }
}
