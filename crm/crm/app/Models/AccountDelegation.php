<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountDelegation extends Model
{
    protected $fillable = [
        'account_id','name','phone','email',
        'street','street2','postal_code','city','state','country_code'
    ];
    public function account(){ return $this->belongsTo(Account::class); }
}
