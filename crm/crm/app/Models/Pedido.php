<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Tarea;
use App\Models\Factura;
use App\Models\User;
use App\Models\PedidoDocenteHorario;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    protected $fillable = [
        'numero',
        'peticion_id',
        'account_id',
        'contact_id',
        'fecha_pedido',
        'descripcion',
        'estado_pedido',
        'proyecto_justificado',
        'anio',
        'forma_pago',
        'es_formacion',
        'fecha_limite_memoria',
        'dpto_consultor',
        'dpto_comercial',
        'estado_facturacion',
        'subvencion',
        'gasto_subcontratado',
        'fecha_limite_proyecto',
        'proyecto_externo',
        'tipo_pago_proyecto',
        'tipo_proyecto',
        'mostrar_precios',
        'importe_total',
        'moneda',
        'info_adicional',
        'email_facturacion',
        'facturar_primer_plazo',
        'info_facturacion',
        'facturar_segundo_plazo',
    ];

    protected $casts = [
        'fecha_pedido'           => 'date',
        'proyecto_justificado'   => 'boolean',
        'es_formacion'           => 'boolean',
        'fecha_limite_memoria'   => 'date',
        'gasto_subcontratado'    => 'decimal:2',
        'fecha_limite_proyecto'  => 'date',
        'mostrar_precios'        => 'boolean',
        'importe_total'          => 'decimal:2',
        'facturar_primer_plazo'  => 'boolean',
        'facturar_segundo_plazo' => 'boolean',
    ];

    public function peticion(): BelongsTo
    {
        return $this->belongsTo(Peticion::class, 'peticion_id');
    }

    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function contacto(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function lineas(): HasMany
    {
        return $this->hasMany(PedidoLinea::class, 'pedido_id');
    }

    public function plazosPago(): HasMany
    {
        return $this->hasMany(PedidoPlazoPago::class, 'pedido_id');
    }
    public function tareas()
    {
        return $this->hasMany(Tarea::class);
    }
    public function documentos()
    {
        return $this->hasMany(\App\Models\Documento::class);
    }

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'pedido_id');
    }

    public function anticipos(): HasMany
    {
        return $this->hasMany(Anticipo::class, 'pedido_id');
    }

    public function docentes()
    {
        return $this->belongsToMany(User::class, 'pedido_docente')
            ->withTimestamps();
    }
    
    public function docenteHorarios(): HasMany
    {
        return $this->hasMany(PedidoDocenteHorario::class, 'pedido_id');
    }
}