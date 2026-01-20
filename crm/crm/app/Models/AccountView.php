<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountView extends Model
{
    use HasFactory;

    protected $table = 'account_views';

    protected $fillable = [
        'user_id',
        'name',
        'is_default',
        'filters',
        'columns',
        'sort_column',
        'sort_direction',
    ];

    protected $casts = [
        'is_default'      => 'boolean',
        'filters'         => 'array',
        'columns'         => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
