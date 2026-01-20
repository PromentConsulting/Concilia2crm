<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    use HasFactory;

    public const ESTADOS = [
        'pendiente_asignacion',
        'asignado',
        'en_curso',
        'en_espera',
        'ganado',
        'perdido',
    ];

    public const PRIORIDADES = ['baja', 'media', 'alta', 'urgente'];

    protected $table = 'solicitudes';

    protected $fillable = [
        'account_id',
        'contact_id',
        'titulo',
        'descripcion',
        'texto_peticion',
        'estado',
        'origen',
        'tipo_servicio',
        'prioridad',
        'canal',
        'tipo_entidad',
        'razon_social',
        'provincia',
        'num_plantilla',
        'num_puesto_trabajo',
        'motivo_cierre',
        'motivo_cierre_detalle',
        'source_external_id',
        'fecha_solicitud',
        'fecha_prevista',
        'fecha_cierre',
        'closed_at',
        'importe_estimado',
        'moneda',
        'owner_user_id',
        'owner_team_id',
    ];

    protected $casts = [
        'fecha_solicitud' => 'datetime',
        'fecha_prevista'  => 'datetime',
        'fecha_cierre'    => 'datetime',
        'closed_at'       => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function (self $solicitud) {

            // 1) Auditoría general de cambios (para "Historial de cambios")
            $dirty = $solicitud->getDirty();
            unset($dirty['updated_at']); // no interesa guardar updated_at como cambio

            foreach ($dirty as $field => $newValue) {
                $oldValue = $solicitud->getOriginal($field);

                // Evita falsos positivos (ej: "1" vs 1)
                if ($oldValue == $newValue) {
                    continue;
                }

                // OJO: evita romper si por lo que sea no existe la relación (pero debería existir)
                try {
                    $solicitud->audits()->create([
                        'user_id'   => optional(auth()->user())->id,
                        'field'     => $field,
                        'old_value' => self::auditValue($oldValue),
                        'new_value' => self::auditValue($newValue),
                    ]);
                } catch (\Throwable $e) {
                    // Si alguna vez prefieres que falle "duro", elimina este try/catch.
                    // De momento lo dejamos para no bloquear updates por auditoría.
                }
            }

            // 2) Tu historial específico de cambios de ESTADO (lo mantengo tal cual)
            if ($solicitud->isDirty('estado')) {
                $solicitud->logEstado($solicitud->getOriginal('estado'), $solicitud->estado, [
                    'motivo_cierre'         => $solicitud->motivo_cierre,
                    'motivo_cierre_detalle' => $solicitud->motivo_cierre_detalle,
                ]);
            }
        });
    }

    protected static function auditValue($value): ?string
    {
        if ($value === null) return null;

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_bool($value)) return $value ? '1' : '0';

        if (is_scalar($value)) return (string) $value;

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    // Relaciones básicas
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    // Alias opcional (por si alguna vista usa $solicitud->cuenta)
    public function cuenta()
    {
        return $this->account();
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    // Alias opcional (por si alguna vista usa $solicitud->contacto)
    public function contacto()
    {
        return $this->contact();
    }

    public function historialEstados()
    {
        return $this->hasMany(SolicitudEstadoHistorial::class);
    }

    // Comercial / propietario
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function ownerTeam()
    {
        return $this->belongsTo(Team::class, 'owner_team_id');
    }

    public function tareas()
    {
        return $this->hasMany(Tarea::class);
    }

    public function documentos()
    {
        return $this->hasMany(\App\Models\Documento::class);
    }

    // Auditoría (Historial de cambios)
    public function audits()
    {
        return $this->hasMany(\App\Models\SolicitudAudit::class, 'solicitud_id')->latest();
    }

    public function logEstado(?string $estadoAnterior, string $estadoNuevo, array $extra = []): void
    {
        $this->historialEstados()->create(array_merge([
            'estado_anterior'        => $estadoAnterior,
            'estado_nuevo'           => $estadoNuevo,
            'user_id'                => optional(auth()->user())->id,
            'motivo_cierre'          => $this->motivo_cierre,
            'motivo_cierre_detalle'  => $this->motivo_cierre_detalle,
            'cambio_en'              => now(),
        ], $extra));
    }

    public function actualizarEstado(string $estado, ?string $motivoCierre = null, ?string $detalle = null): void
    {
        if (! in_array($estado, self::ESTADOS, true)) {
            throw new \InvalidArgumentException("Estado de solicitud no válido: {$estado}");
        }

        $this->estado = $estado;
        $this->motivo_cierre = $motivoCierre;
        $this->motivo_cierre_detalle = $detalle;

        if (in_array($estado, ['ganado', 'perdido'], true)) {
            $this->closed_at = $this->closed_at ?? now();
        }

        $this->save();
    }
}
