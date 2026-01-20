<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FacturaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'idempotency_key'     => ['nullable', 'string', 'max:64'],
            'numero'               => ['nullable', 'string', 'max:255'],
            'numero_serie'         => ['nullable', 'string', 'max:255'],
            'serie_id'             => ['nullable', 'integer', 'exists:factura_series,id'],
            'estado'               => ['nullable', 'in:borrador,publicada,cancelada'],
            'tipo'                 => ['nullable', 'in:normal,rectificativa,anticipo'],
            'descripcion'          => ['nullable', 'string', 'max:255'],
            'account_id'           => ['nullable', 'integer', 'exists:accounts,id'],
            'pedido_id'            => ['nullable', 'integer', 'exists:pedidos,id'],
            'fecha_factura'        => ['nullable', 'date'],
            'fecha_vencimiento'    => ['nullable', 'date'],
            'fecha_cobro'          => ['nullable', 'date'],
            'agrupar_referencias'  => ['boolean'],
            'cobrado'              => ['boolean'],
            'contabilizado'        => ['boolean'],
            'payment_state'        => ['nullable', 'in:pendiente,parcial,pagado'],
            'forma_pago'           => ['nullable', 'string', 'max:255'],
            'instruccion_pago'     => ['nullable', 'string', 'max:255'],
            'dpto_comercial'       => ['nullable', 'string', 'max:255'],
            'email_facturacion'    => ['nullable', 'email', 'max:255'],
            'descuento_global'     => ['nullable', 'numeric', 'min:0'],
            'redondeo'             => ['nullable', 'numeric'],
            'importe'              => ['nullable', 'numeric'],
            'importe_total'        => ['nullable', 'numeric'],
            'moneda'               => ['nullable', 'string', 'max:3'],
            'info_adicional'       => ['nullable', 'string'],
            'lineas'                              => ['sometimes', 'array'],
            'lineas.*.referencia'                 => ['nullable', 'string', 'max:255'],
            'lineas.*.concepto'                   => ['nullable', 'string'],
            'lineas.*.cantidad'                   => ['nullable', 'numeric', 'min:0'],
            'lineas.*.precio'                     => ['nullable', 'numeric', 'regex:/^-?\d+(?:\.\d{1,2})?$/'],
            'lineas.*.descuento_porcentaje'       => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lineas.*.iva_porcentaje'             => ['nullable', 'numeric', 'min:0'],
            'lineas.*.service_id'                 => ['nullable', 'integer', 'exists:services,id'],
            'lineas.*.pedido_linea_id'            => ['nullable', 'integer', 'exists:pedido_lineas,id'],
            'lineas.*.impuestos'                  => ['nullable', 'array'],
            'lineas.*.impuestos.*'                => ['integer', 'exists:impuestos,id'],
        ];
    }
}