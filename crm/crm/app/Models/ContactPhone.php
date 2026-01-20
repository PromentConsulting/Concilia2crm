<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactPhone extends Model
{
    protected $fillable = ['contact_id','phone','type','is_primary','extension'];
    protected $casts = ['is_primary'=>'boolean'];
    public function contact(){ return $this->belongsTo(Contact::class); }
}
