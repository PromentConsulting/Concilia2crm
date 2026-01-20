<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use HasFactory;

    public const ESTADOS = ['borrador', 'planificada', 'activa', 'pausada', 'finalizada'];

    protected $fillable = [
        'campaign_number',
        'name',
        'descripcion',
        'tipo',
        'estado',
        'email_confirmation_required',
        'owner_user_id',
        'company_size',
        'equality_plan_preference',
        'habitantes',
        'equality_plan_valid_until',
        'equality_mark_preference',
        'origen',
        'segment_definition',
        'static_snapshot',
        'mautic_campaign_id',
        'mautic_segment_id',
        'last_sync_at',
        'planned_start_at',
        'planned_end_at',
    ];

    protected $casts = [
        'segment_definition' => 'array',
        'static_snapshot' => 'boolean',
        'email_confirmation_required' => 'boolean',
        'last_sync_at' => 'datetime',
        'planned_start_at' => 'datetime',
        'planned_end_at' => 'datetime',
        'equality_plan_valid_until' => 'date',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CampaignContact::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(CampaignEvent::class);
    }
}