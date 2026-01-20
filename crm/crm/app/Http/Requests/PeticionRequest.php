<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PeticionRequest extends FormRequest
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
        $tiposProyecto = array_keys(config('peticiones.tipos_proyecto', []));
        $subvenciones  = array_keys(config('peticiones.subvenciones', []));

        return [
            'solicitud_id'    => ['nullable', 'integer', 'exists:solicitudes,id'],
            'account_id'      => ['nullable', 'integer', 'exists:accounts,id'],
            'contact_id'      => ['nullable', 'integer', 'exists:contacts,id'],
            'owner_user_id'   => ['nullable', 'integer', 'exists:users,id'],
            'created_by_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'codigo'          => [
                'nullable',
                'string',
                'max:24',
                Rule::unique('peticiones', 'codigo')->ignore($this->peticion?->id),
            ],
            'anio'            => ['nullable', 'integer', 'digits:4'],
            'fecha_alta'      => ['nullable', 'date'],
            'titulo'          => ['required', 'string', 'max:255'],
            'descripcion'     => ['nullable', 'string'],
            'memoria'         => ['boolean'],
            'info_cliente'    => ['nullable', 'string'],
            'subvencion_id'   => ['nullable', Rule::in($subvenciones)],
            'tipo_proyecto'   => ['nullable', Rule::in($tiposProyecto)],
            'gasto_subcontratado' => ['nullable', 'string', 'max:255'],
            'info_adicional'  => ['nullable', 'string'],
            'info_facturacion'=> ['nullable', 'string'],
            'comentarios'     => ['nullable', 'string'],
            'importe_total'   => ['nullable', 'numeric', 'min:0'],
            'moneda'          => ['required', 'string', 'size:3'],
            'estado'          => ['required', 'string', 'in:borrador,enviada,aceptada,rechazada,cancelada'],
            'fecha_envio'     => ['nullable', 'date'],
            'fecha_limite_oferta' => ['nullable', 'date'],
            'fecha_respuesta' => ['nullable', 'date'],
        ];
    }
}