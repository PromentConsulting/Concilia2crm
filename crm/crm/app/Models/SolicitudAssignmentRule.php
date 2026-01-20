<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudAssignmentRule extends Model
{
    protected $table = 'solicitud_assignment_rules';

    protected $fillable = [
        'name',
        'field',
        'operator',
        'value',
        'owner_user_id',
        'priority',
        'active',
    ];

    protected $casts = [
        'active'   => 'bool',
        'priority' => 'int',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }
}
