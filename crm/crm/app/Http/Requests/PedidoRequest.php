<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'numero'              => ['nullable', 'string', 'max:50'],
            'peticion_id'         => ['nullable', 'integer', 'exists:peticiones,id'],
            'account_id'          => ['nullable', 'integer', 'exists:accounts,id'],
            'contact_id'          => ['nullable', 'integer', 'exists:contacts,id'],
            'fecha_pedido'        => ['nullable', 'date'],
            'descripcion'         => ['nullable', 'string', 'max:255'],
            'estado_pedido'       => ['required', 'string', 'max:50'],
            'proyecto_justificado'=> ['boolean'],
            'anio'                => ['nullable', 'integer'],
            'forma_pago'          => ['nullable', 'string', 'max:100'],
            'es_formacion'        => ['boolean'],

            'fecha_limite_memoria'=> ['nullable', 'date'],
            'dpto_consultor'      => ['nullable', 'string', 'max:255'],
            'dpto_comercial'      => ['nullable', 'string', 'max:255'],
            'estado_facturacion'  => ['nullable', 'string', 'max:255'],
            'subvencion'          => ['nullable', 'string', 'max:255'],
            'gasto_subcontratado' => ['nullable', 'numeric', 'min:0'],

            'fecha_limite_proyecto'=> ['nullable', 'date'],
            'proyecto_externo'     => ['nullable', 'string', 'max:255'],
            'tipo_pago_proyecto'   => ['nullable', 'string', 'max:255'],
            'tipo_proyecto'        => ['nullable', 'string', 'max:255'],
            'mostrar_precios'      => ['boolean'],

            'importe_total'        => ['nullable', 'numeric', 'min:0'],
            'moneda'               => ['required', 'string', 'size:3'],

            'info_adicional'       => ['nullable', 'string'],
            'email_facturacion'    => ['nullable', 'email', 'max:255'],
            'facturar_primer_plazo'=> ['boolean'],
            'info_facturacion'     => ['nullable', 'string'],
            'facturar_segundo_plazo'=> ['boolean'],

            'lineas'                                  => ['sometimes', 'array'],
            'lineas.*.referencia'                     => ['nullable', 'string', 'max:255'],
            'lineas.*.descripcion'                    => ['nullable', 'string'],
            'lineas.*.cantidad'                       => ['nullable', 'numeric', 'min:0'],
            'lineas.*.precio'                         => ['nullable', 'numeric', 'min:0'],
            'lineas.*.descuento_porcentaje'           => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lineas.*.iva_porcentaje'                 => ['nullable', 'numeric', 'min:0'],
            'lineas.*.fecha_limite_factura'           => ['nullable', 'date'],
            'lineas.*.service_id'                     => ['nullable', 'integer', 'exists:services,id'],
        ];
    }
}