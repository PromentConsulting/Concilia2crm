<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudView extends Model
{
    use HasFactory;

    protected $table = 'solicitud_views';

    protected $fillable = [
        'user_id',
        'name',
        'is_default',
        'filters',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'filters'    => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}