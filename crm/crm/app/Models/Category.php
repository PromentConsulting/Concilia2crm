<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name','code','applies_to','selection_type','is_active'];
    public function accounts(){ return $this->morphedByMany(Account::class, 'categorizable'); }
    public function contacts(){ return $this->morphedByMany(Contact::class, 'categorizable'); }
}
