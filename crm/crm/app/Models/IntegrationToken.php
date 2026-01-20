<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'token',
        'scopes',
        'user_id',
        'last_used_at',
    ];

    protected $casts = [
        'scopes'       => 'array',
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
