<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Contact extends Model
{
    use HasFactory;

    protected $table = 'contacts';

    protected $fillable = [
        'account_id',
        'primary_account_id',
        'owner_user_id',
        'owner_team_id',
        'first_name',
        'last_name',
        'name',           // por si existe columna name
        'email',          // legacy
        'primary_email',
        'phone',
        'mobile',
        'role',
        'role_otro',
        'job_title',
        'notes',
        'comentarios',
        'is_primary',
        'primary',
        'estado_contacto',
        'motivo_cambio_estado',
        'estado_cambiado_en',
        'estado_cambiado_por',
        'flag_campanas',
        'flag_facturacion',
        'nivel_decision',
        'estado_rgpd',
        'canal_preferido',
        'mensajeria_instantanea',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'primary'    => 'boolean',
        'flag_campanas'    => 'boolean',
        'flag_facturacion' => 'boolean',
        'estado_cambiado_en' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Contact $contact) {
            // Forzamos que siempre exista un email principal coherente
            if (empty($contact->primary_email) && ! empty($contact->email)) {
                $contact->primary_email = $contact->email;
            }

            if (empty($contact->email) && ! empty($contact->primary_email)) {
                $contact->email = $contact->primary_email;
            }

            if ($contact->isDirty('estado_contacto')) {
                $contact->estado_cambiado_en  = now();
                $contact->estado_cambiado_por = optional(auth()->user())->id;
            }
        });
    }

    /*
     |----------------------------------------------------------------------
     | Relaciones con cuentas (N:M) + principal
     |----------------------------------------------------------------------
     */

    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class, 'account_contact')
            ->withPivot(['categoria', 'es_principal', 'observaciones'])
            ->withTimestamps();
    }

    public function primaryAccount(): BelongsTo
    {
        $column = Schema::hasColumn($this->getTable(), 'primary_account_id')
            ? 'primary_account_id'
            : 'account_id';

        return $this->belongsTo(Account::class, $column);
    }

    /**
     * Alias usado por el dashboard: Contact::with('account')
     */
    public function account(): BelongsTo
    {
        return $this->primaryAccount();
    }

    public function getEmpresaPrincipalAttribute()
    {
        return $this->primaryAccount
            ?? $this->accounts()->wherePivot('es_principal', true)->first();
    }

    /*
     |----------------------------------------------------------------------
     | Emails
     |----------------------------------------------------------------------
     */

    public function emails(): HasMany
    {
        return $this->hasMany(ContactEmail::class);
    }

    public function primaryEmailAlias(): HasOne
    {
        return $this->hasOne(ContactEmail::class)->where('is_primary', true);
    }

    // "email efectivo" que utiliza el sistema
    public function getEmailAttribute($value)
    {
        if (!empty($this->primary_email)) {
            return $this->primary_email;
        }

        if ($primaryAlias = $this->primaryEmailAlias()->first()) {
            return $primaryAlias->email;
        }

        return $value;
    }

    /*
     |----------------------------------------------------------------------
     | Roles (segmentación)
     |----------------------------------------------------------------------
     */

    public function roles(): HasMany
    {
        return $this->hasMany(ContactRole::class);
    }

    public function hasRole(string $role): bool
    {
        return $this->roles->contains('role', $role);
    }

    /*
     |----------------------------------------------------------------------
     | Owner
     |----------------------------------------------------------------------
     */

    public function ownerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function ownerTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'owner_team_id');
    }

    /*
     |----------------------------------------------------------------------
     | Normalización de datos
     |----------------------------------------------------------------------
     */

    private function normalizePhone(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        // Conserva el prefijo internacional si existe
        $value = preg_replace('/[^\d+]/', '', $value);

        return $value;
    }

    private function normalizeName(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        return Str::title(Str::lower($value));
    }

    public function setFirstNameAttribute($value): void
    {
        $this->attributes['first_name'] = $this->normalizeName($value);
    }

    public function setLastNameAttribute($value): void
    {
        $this->attributes['last_name'] = $this->normalizeName($value);
    }

    public function setPhoneAttribute($value): void
    {
        $this->attributes['phone'] = $this->normalizePhone($value);
    }

    public function setMobileAttribute($value): void
    {
        $this->attributes['mobile'] = $this->normalizePhone($value);
    }

    public function setPrimaryEmailAttribute($value): void
    {
        $this->attributes['primary_email'] = $value ? strtolower(trim($value)) : null;
    }

    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = $value ? strtolower(trim($value)) : null;
    }

    public function setEstadoContactoAttribute($value): void
    {
        $this->attributes['estado_contacto'] = $value ? strtolower($value) : null;
    }
}