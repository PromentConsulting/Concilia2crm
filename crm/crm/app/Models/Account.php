<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class Account extends Model
{
    use HasFactory;

    protected $table = 'accounts';

    protected $fillable = [
        // Datos básicos
        'name',
        'nombre_abreviado',
        'email',
        'phone',
        'website',
        'tax_id',
        'estado',
        'tipo_entidad',

        // Perfil / actividad
        'industry',
        'employee_count',
        'annual_revenue',
        'notes',
        'habitantes',
        'departamento_comercial',

        // Dirección principal
        'address',
        'direccion',
        'city',
        'localidad',
        'state',
        'provincia',
        'postal_code',
        'codigo_postal',
        'country',
        'pais',

        // Dirección de facturación
        'billing_address',
        'billing_city',
        'billing_state',
        'billing_postal_code',
        'billing_country',
        'billing_email',
        'billing_contact_id',
        'billing_has_payment_issues',
        'billing_notes',
        'is_billable',
        'billing_legal_name',
        'customer_code',

        // Lifecycle / tipo de entidad
        'lifecycle',
        'logo_path',
        'legal_name',
        'fax',
        'company_type',
        'products_services',
        'company_size_min',
        'company_size_max',
        'founded_year',
        'group_name',
        'tipo_relacion_grupo',
        'parent_account_id',

        // Igualdad / calidad / RSE
        'public_contracts',
        'equality_plan',
        'equality_mark',
        'equality_plan_valid_until',
        'interest_local',
        'interest_regional',
        'interest_national',
        'no_interest',
        'quality',
        'rse',
        'otras_certificaciones',

        // Gestión interna
        'main_contact_role',
        'legacy_updated_at',
        'sales_department',
        'cnae',
        'import_raw',

        // Perfil extra
        'email_confirmed_at',

        // Facturación ampliada
        'odoo_id',

        // ==========================
        // NUEVO: Características
        // ==========================
        'car_plan_igualdad',
        'car_plan_igualdad_vigencia',

        'car_plan_lgtbi',
        'car_plan_lgtbi_vigencia',

        'car_protocolo_acoso_sexual',
        'car_protocolo_acoso_sexual_revision',

        'car_protocolo_acoso_laboral',
        'car_protocolo_acoso_laboral_revision',

        'car_protocolo_acoso_lgtbi',
        'car_protocolo_acoso_lgtbi_revision',

        'car_vpt',

        'car_registro_retributivo',
        'car_registro_retributivo_revision',

        'car_plan_igualdad_estrategico',
        'car_plan_igualdad_estrategico_vigencia',

        'car_sistema_gestion',
    ];

    protected $casts = [
        'employee_count'      => 'integer',
        'annual_revenue'      => 'float',
        'company_size_min'    => 'integer',
        'company_size_max'    => 'integer',
        'founded_year'        => 'integer',
        'habitantes'          => 'integer',
        'legacy_updated_at'   => 'date',
        'import_raw'          => 'array',

        'email_confirmed_at'          => 'date',
        'is_billable'                 => 'boolean',
        'billing_has_payment_issues'  => 'boolean',
        'public_contracts'            => 'boolean',
        'equality_plan'               => 'boolean',
        'equality_mark'               => 'boolean',
        'interest_local'              => 'boolean',
        'interest_regional'           => 'boolean',
        'interest_national'           => 'boolean',
        'no_interest'                 => 'boolean',
        'quality'                     => 'boolean',
        'rse'                         => 'boolean',
        'equality_plan_valid_until'   => 'date',

        // ==========================
        // NUEVO: fechas de Características
        // ==========================
        'car_plan_igualdad_vigencia'               => 'date',
        'car_plan_lgtbi_vigencia'                  => 'date',
        'car_protocolo_acoso_sexual_revision'      => 'date',
        'car_protocolo_acoso_laboral_revision'     => 'date',
        'car_protocolo_acoso_lgtbi_revision'       => 'date',
        'car_registro_retributivo_revision'        => 'date',
        'car_plan_igualdad_estrategico_vigencia'   => 'date',
    ];

    /*
     |--------------------------------------------------------------------------
     | Boot / Auditoría
     |--------------------------------------------------------------------------
     */

    protected static function booted(): void
    {
        static::updated(function (Account $account) {
            // Campos que no queremos auditar
            $ignoredFields = ['updated_at', 'created_at', 'import_raw'];

            $changes  = $account->getChanges();   // nuevos valores
            $original = $account->getOriginal();  // valores antiguos
            $userId   = Auth::id();

            foreach ($changes as $field => $newValue) {
                if (in_array($field, $ignoredFields, true)) {
                    continue;
                }

                $oldValue = $original[$field] ?? null;

                // Si no hay cambio "real", saltamos
                if ($oldValue == $newValue) {
                    continue;
                }

                AccountAudit::create([
                    'account_id' => $account->id,
                    'user_id'    => $userId,
                    'field'      => $field,
                    'old_value'  => is_scalar($oldValue) || $oldValue === null ? $oldValue : json_encode($oldValue),
                    'new_value'  => is_scalar($newValue) || $newValue === null ? $newValue : json_encode($newValue),
                ]);
            }
        });
    }

    /*
     |--------------------------------------------------------------------------
     | Mutators / Normalización de datos
     |--------------------------------------------------------------------------
     */

    public function setTaxIdAttribute($value): void
    {
        if ($value === null) {
            $this->attributes['tax_id'] = null;
            return;
        }

        $value = strtoupper(trim($value));
        $value = str_replace([' ', '-', '.'], '', $value);

        $this->attributes['tax_id'] = $value;
    }

    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = $value
            ? strtolower(trim($value))
            : null;
    }

    public function setBillingEmailAttribute($value): void
    {
        $this->attributes['billing_email'] = $value
            ? strtolower(trim($value))
            : null;
    }

    public function setNombreAbreviadoAttribute($value): void
    {
        $this->attributes['nombre_abreviado'] = $value ? trim($value) : null;
    }

    private function toBoolean($value): bool
    {
        if (is_string($value)) {
            $value = strtolower(trim($value));
            return in_array($value, ['1', 'true', 'si', 'sí', 'on'], true);
        }

        return (bool) $value;
    }

    public function setInterestLocalAttribute($value): void
    {
        $this->attributes['interest_local'] = $this->toBoolean($value);

        if ($this->attributes['interest_local']) {
            $this->attributes['no_interest'] = false;
        }
    }

    public function setInterestRegionalAttribute($value): void
    {
        $this->attributes['interest_regional'] = $this->toBoolean($value);

        if ($this->attributes['interest_regional']) {
            $this->attributes['no_interest'] = false;
        }
    }

    public function setInterestNationalAttribute($value): void
    {
        $this->attributes['interest_national'] = $this->toBoolean($value);

        if ($this->attributes['interest_national']) {
            $this->attributes['no_interest'] = false;
        }
    }

    public function setNoInterestAttribute($value): void
    {
        $flag = $this->toBoolean($value);
        $this->attributes['no_interest'] = $flag;

        if ($flag) {
            $this->attributes['interest_local']    = false;
            $this->attributes['interest_regional'] = false;
            $this->attributes['interest_national'] = false;
        }
    }

    public function setWebsiteAttribute($value): void
    {
        if ($value === null) {
            $this->attributes['website'] = null;
            return;
        }

        $value = trim($value);

        if ($value === '') {
            $this->attributes['website'] = null;
            return;
        }

        if (! Str::startsWith($value, ['http://', 'https://'])) {
            $value = 'https://' . $value;
        }

        $this->attributes['website'] = $value;
    }

    protected function normalizePhone(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        // Elimina espacios, guiones, puntos y paréntesis
        $value = preg_replace('/[ \-().]/', '', $value);

        return $value;
    }

    public function setPhoneAttribute($value): void
    {
        $this->attributes['phone'] = $this->normalizePhone($value);
    }

    public function setFaxAttribute($value): void
    {
        $this->attributes['fax'] = $this->normalizePhone($value);
    }

    /**
     * Normaliza valores del tipo: sí/no/desconocido (o vacío).
     * Devuelve: 'si' | 'no' | 'desconocido' | null
     */
    private function normalizeTriState($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $v = is_string($value) ? strtolower(trim($value)) : $value;

        if ($v === '' || $v === false) {
            return null;
        }

        // aceptar variantes
        if ($v === 'sí') $v = 'si';

        if (in_array($v, ['si', 'no', 'desconocido'], true)) {
            return $v;
        }

        // fallback: si viene algo raro, lo dejamos como string “limpio”
        return is_string($value) ? trim($value) : (string) $value;
    }

    private function shouldKeepDateForTriState(?string $value): bool
    {
        // Solo mantenemos la fecha si el valor es “si”
        return $value === 'si';
    }

    // ==========================
    // Mutators: Características
    // Limpian fechas si no es "si"
    // ==========================

    public function setCarPlanIgualdadAttribute($value): void
    {
        $v = $this->normalizeTriState($value);
        $this->attributes['car_plan_igualdad'] = $v;

        if (! $this->shouldKeepDateForTriState($v)) {
            $this->attributes['car_plan_igualdad_vigencia'] = null;
        }
    }

    public function setCarPlanLgtbiAttribute($value): void
    {
        $v = $this->normalizeTriState($value);
        $this->attributes['car_plan_lgtbi'] = $v;

        if (! $this->shouldKeepDateForTriState($v)) {
            $this->attributes['car_plan_lgtbi_vigencia'] = null;
        }
    }

    public function setCarProtocoloAcosoSexualAttribute($value): void
    {
        $v = $this->normalizeTriState($value);
        $this->attributes['car_protocolo_acoso_sexual'] = $v;

        if (! $this->shouldKeepDateForTriState($v)) {
            $this->attributes['car_protocolo_acoso_sexual_revision'] = null;
        }
    }

    public function setCarProtocoloAcosoLaboralAttribute($value): void
    {
        $v = $this->normalizeTriState($value);
        $this->attributes['car_protocolo_acoso_laboral'] = $v;

        if (! $this->shouldKeepDateForTriState($v)) {
            $this->attributes['car_protocolo_acoso_laboral_revision'] = null;
        }
    }

    public function setCarProtocoloAcosoLgtbiAttribute($value): void
    {
        $v = $this->normalizeTriState($value);
        $this->attributes['car_protocolo_acoso_lgtbi'] = $v;

        if (! $this->shouldKeepDateForTriState($v)) {
            $this->attributes['car_protocolo_acoso_lgtbi_revision'] = null;
        }
    }

    public function setCarRegistroRetributivoAttribute($value): void
    {
        $v = $this->normalizeTriState($value);
        $this->attributes['car_registro_retributivo'] = $v;

        if (! $this->shouldKeepDateForTriState($v)) {
            $this->attributes['car_registro_retributivo_revision'] = null;
        }
    }

    public function setCarPlanIgualdadEstrategicoAttribute($value): void
    {
        $v = $this->normalizeTriState($value);
        $this->attributes['car_plan_igualdad_estrategico'] = $v;

        if (! $this->shouldKeepDateForTriState($v)) {
            $this->attributes['car_plan_igualdad_estrategico_vigencia'] = null;
        }
    }

    public function setCarVptAttribute($value): void
    {
        $this->attributes['car_vpt'] = $this->normalizeTriState($value);
    }

    public function setCarSistemaGestionAttribute($value): void
    {
        $this->attributes['car_sistema_gestion'] = $this->normalizeTriState($value);
    }

    /*
     |--------------------------------------------------------------------------
     | Relaciones jerárquicas (matriz / filial)
     |--------------------------------------------------------------------------
     */

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_account_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_account_id');
    }

    /*
     |--------------------------------------------------------------------------
     | Relaciones con contactos (N:M)
     |--------------------------------------------------------------------------
     */

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'account_contact')
            ->withPivot(['categoria', 'es_principal', 'observaciones'])
            ->withTimestamps();
    }

    public function contactosFacturacion(): BelongsToMany
    {
        return $this->contacts()->wherePivot('categoria', 'facturacion');
    }

    public function contactosComercial(): BelongsToMany
    {
        return $this->contacts()->wherePivot('categoria', 'comercial');
    }

    public function contactosDireccion(): BelongsToMany
    {
        return $this->contacts()->wherePivot('categoria', 'direccion');
    }

    /**
     * Devuelve el contacto de facturación principal (o el primero disponible).
     */
    public function billingContact()
    {
        $contacts = $this->contactosFacturacion;

        if (! $contacts) {
            return null;
        }

        return $contacts->firstWhere('pivot.es_principal', true)
            ?? $contacts->first();
    }

    /*
     |--------------------------------------------------------------------------
     | Relaciones con grupos / delegaciones / owner / tareas / docs / auditoría
     |--------------------------------------------------------------------------
     */

    public function delegaciones(): HasMany
    {
        return $this->hasMany(AccountDelegation::class);
    }

    public function groups(): BelongsToMany
    {
        // pivot account_group_members (account_id, account_group_id)
        return $this->belongsToMany(AccountGroup::class, 'account_group_members');
    }

    public function ownerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function ownerTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'owner_team_id');
    }

    public function tareas(): HasMany
    {
        return $this->hasMany(Tarea::class);
    }

    /*
     |--------------------------------------------------------------------------
     | Relaciones comerciales / operaciones
     |--------------------------------------------------------------------------
     */

    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class);
    }

    public function peticiones(): HasMany
    {
        return $this->hasMany(Peticion::class);
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    /*
     |--------------------------------------------------------------------------
     | Grupo empresarial (matriz / filial)
     |--------------------------------------------------------------------------
     */

    /**
     * Devuelve la matriz (root) del grupo.
     * - Si la cuenta es matriz, es ella misma.
     * - Si es filial, se recorre parent_account_id hasta el root.
     */
    public function groupRoot(): self
    {
        $root = $this;

        // Seguridad para evitar bucles infinitos ante datos corruptos.
        $guard = 0;
        while ($root->parent_account_id && $guard < 25) {
            $root = $root->relationLoaded('parent') ? ($root->parent ?? $root) : $root->parent()->first() ?? $root;
            $guard++;
        }

        return $root;
    }

    /**
     * IDs de cuentas que pertenecen al mismo grupo (root + filiales).
     */
    public function groupAccountIds(): array
    {
        $root = $this->groupRoot();

        // Cargamos hijos recursivamente (hasta 5 niveles por seguridad)
        $ids = collect([$root->id]);
        $queue = collect([$root]);
        $depth = 0;

        while ($queue->isNotEmpty() && $depth < 5) {
            $next = collect();
            foreach ($queue as $node) {
                $children = $node->relationLoaded('children') ? $node->children : $node->children()->get();
                foreach ($children as $child) {
                    $ids->push($child->id);
                    $next->push($child);
                }
            }
            $queue = $next;
            $depth++;
        }

        return $ids->unique()->values()->all();
    }

    /*
     |--------------------------------------------------------------------------
     | Facturación: herencia desde matriz (si aplica)
     |--------------------------------------------------------------------------
     */

    public function effectiveBillingField(string $field, mixed $fallback = '—'): mixed
    {
        // Si el campo existe y tiene valor local, siempre prioriza el valor local.
        $local = $this->{$field} ?? null;
        if (!is_null($local) && $local !== '') {
            return $local;
        }

        // Si no pertenece a un grupo, usa fallback.
        if (!$this->parent_account_id && ($this->tipo_relacion_grupo !== 'filial')) {
            return $fallback;
        }

        // Hereda desde la matriz
        $root = $this->groupRoot();
        $rootValue = $root->{$field} ?? null;
        if (!is_null($rootValue) && $rootValue !== '') {
            return $rootValue;
        }

        return $fallback;
    }


    /**
     * Alias compatible con vistas: devuelve el valor efectivo de un campo de facturación
     * (prioriza el valor local y, si está vacío, hereda desde la cuenta matriz del grupo).
     */
    public function billingValue(string $field, mixed $fallback = '—'): mixed
    {
        return $this->effectiveBillingField($field, $fallback);
    }

    /**
     * Indica si el valor mostrado para un campo de facturación viene heredado de la matriz.
     */
    public function billingIsInherited(string $field): bool
    {
        return $this->effectiveBillingIsInherited($field);
    }

    /**
     * Devuelve true si el campo está vacío en esta cuenta pero la matriz del grupo tiene valor.
     */
    public function effectiveBillingIsInherited(string $field): bool
    {
        $local = $this->{$field} ?? null;
        if (!is_null($local) && $local !== '') {
            return false;
        }

        if (!$this->parent_account_id && ($this->tipo_relacion_grupo !== 'filial')) {
            return false;
        }

        $root = $this->groupRoot();
        $rootValue = $root->{$field} ?? null;

        return !is_null($rootValue) && $rootValue !== '';
    }

    public function effectiveBillingLegalName(): string
    {
        // billing_legal_name -> legal_name (local) -> herencia (root) -> name
        $local = $this->billing_legal_name ?: ($this->legal_name ?: null);
        if ($local) {
            return $local;
        }

        $root = $this->groupRoot();
        return (string) ($root->billing_legal_name ?: ($root->legal_name ?: ($root->name ?: '—')));
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(AccountAudit::class);
    }
}
