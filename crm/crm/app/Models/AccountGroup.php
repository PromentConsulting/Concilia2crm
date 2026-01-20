<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountGroup extends Model
{
    protected $fillable = ['name','description','parent_id'];
    public function parent(){ return $this->belongsTo(AccountGroup::class,'parent_id'); }
    public function children(){ return $this->hasMany(AccountGroup::class,'parent_id'); }
    public function accounts(){ return $this->belongsToMany(Account::class,'account_group_members'); }
}
