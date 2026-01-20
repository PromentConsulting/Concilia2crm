<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\FacturaLinea;
use App\Models\Account;
use App\Models\Pedido;

class Factura extends Model
{
    use HasFactory;

    public const ESTADO_BORRADOR = 'borrador';
    public const ESTADO_PUBLICADA = 'publicada';
    public const ESTADO_CANCELADA = 'cancelada';

    public const TIPO_NORMAL = 'normal';
    public const TIPO_RECTIFICATIVA = 'rectificativa';
    public const TIPO_ANTICIPO = 'anticipo';

    public const PAYMENT_PENDIENTE = 'pendiente';
    public const PAYMENT_PARCIAL = 'parcial';
    public const PAYMENT_PAGADO = 'pagado';

    protected $table = 'facturas';

    protected $fillable = [
        'idempotency_key',
        'numero',
        'numero_serie',
        'serie_id',
        'estado',
        'tipo',
        'factura_rectificada_id',
        'descripcion',
        'account_id',
        'pedido_id',
        'fecha_factura',
        'fecha_vencimiento',
        'fecha_cobro',
        'agrupar_referencias',
        'cobrado',
        'contabilizado',
        'payment_state',
        'forma_pago',
        'instruccion_pago',
        'dpto_comercial',
        'email_facturacion',
        'descuento_global',
        'redondeo',
        'importe',
        'importe_total',
        'moneda',
        'info_adicional',
        'publicada_en',
        'cancelada_en',
    ];

    protected $casts = [
        'fecha_factura'       => 'date',
        'fecha_vencimiento'   => 'date',
        'fecha_cobro'         => 'date',
        'agrupar_referencias' => 'boolean',
        'cobrado'             => 'boolean',
        'contabilizado'       => 'boolean',
        'importe'             => 'decimal:2',
        'importe_total'       => 'decimal:2',
        'descuento_global'    => 'decimal:2',
        'redondeo'            => 'decimal:2',
        'publicada_en'        => 'datetime',
        'cancelada_en'        => 'datetime',
    ];

    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function serie(): BelongsTo
    {
        return $this->belongsTo(FacturaSerie::class, 'serie_id');
    }

    public function rectificada(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'factura_rectificada_id');
    }

    public function lineas(): HasMany
    {
        return $this->hasMany(FacturaLinea::class, 'factura_id')->orderBy('orden');
    }

    public function anticipos(): HasMany
    {
        return $this->hasMany(Anticipo::class, 'factura_id');
    }

    public function isEditable(): bool
    {
        return $this->estado === self::ESTADO_BORRADOR;
    }
}