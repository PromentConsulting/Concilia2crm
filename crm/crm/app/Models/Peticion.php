<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use App\Models\Tarea;
use App\Models\PeticionLinea;
use Illuminate\Support\Str;

class Peticion extends Model
{
    use HasFactory;

    protected $table = 'peticiones';

    protected $fillable = [
        'solicitud_id',
        'account_id',
        'contact_id',
        'owner_user_id',
        'created_by_user_id',
        'codigo',
        'anio',
        'fecha_alta',
        'titulo',
        'descripcion',
        'memoria',
        'info_cliente',
        'subvencion_id',
        'tipo_proyecto',
        'gasto_subcontratado',
        'info_adicional',
        'info_facturacion',
        'comentarios',
        'importe_total',
        'moneda',
        'estado',
        'fecha_envio',
        'fecha_limite_oferta',
        'fecha_respuesta',
    ];

    protected $casts = [
        'fecha_alta'      => 'date',
        'fecha_envio'     => 'datetime',
        'fecha_limite_oferta' => 'date',
        'fecha_respuesta' => 'datetime',
        'memoria'         => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Peticion $peticion) {
            $anio = $peticion->anio ?? now()->year;
            $peticion->anio = $anio;

            if (! $peticion->codigo) {
                $peticion->codigo = self::generarCodigoParaAnio($anio);
            }

            if (! $peticion->fecha_alta) {
                $peticion->fecha_alta = now();
            }

            $currentUserId = Auth::id();

            if (! $peticion->created_by_user_id && $currentUserId) {
                $peticion->created_by_user_id = $currentUserId;
            }

            if (! $peticion->owner_user_id && $currentUserId) {
                $peticion->owner_user_id = $currentUserId;
            }
        });
    }

    public static function generarCodigoParaAnio(int $anio): string
    {
        $prefijo = (string) $anio;

        $ultimoCodigo = static::query()
            ->where('codigo', 'like', $prefijo . '%')
            ->orderByDesc('codigo')
            ->value('codigo');

        $secuencia = 1;
        if ($ultimoCodigo && Str::startsWith($ultimoCodigo, $prefijo)) {
            $numero = (int) Str::substr($ultimoCodigo, strlen($prefijo));
            $secuencia = $numero + 1;
        }

        return $prefijo . str_pad((string) $secuencia, 5, '0', STR_PAD_LEFT);
    }

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_id');
    }

    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function contacto(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
    public function pedidos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Pedido::class, 'peticion_id');
    }
        public function tareas()
    {
        return $this->hasMany(Tarea::class);
    }
    public function documentos()
    {
        return $this->hasMany(\App\Models\Documento::class);
    }
    public function lineas()
    {
        return $this->hasMany(PeticionLinea::class);
    }

}
