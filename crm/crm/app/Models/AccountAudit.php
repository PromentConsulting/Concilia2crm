<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountAudit extends Model
{
    protected $fillable = [
        'account_id',
        'user_id',
        'field',
        'old_value',
        'new_value',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
