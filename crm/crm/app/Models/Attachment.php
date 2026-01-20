<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = ['attachable_type','attachable_id','disk','path','original_name','mime_type','size'];
    public function attachable(){ return $this->morphTo(); }
}
