<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SolicitudRequest extends FormRequest
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
            'account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'contact_id' => ['nullable', 'integer', 'exists:contacts,id'],

            'titulo'          => ['nullable', 'string', 'max:255'],
            'descripcion'     => ['nullable', 'string'],
            'texto_peticion'  => ['nullable', 'string'],

            'estado' => ['required', 'string', 'in:pendiente_asignacion,asignado,en_curso,en_espera,ganado,perdido'],
            'origen' => ['required', 'string', 'in:web,mautic,manual,importacion,api,otro'],
            'tipo_servicio' => ['nullable', 'string', 'max:255'],
            'prioridad' => ['required', 'string', 'in:baja,media,alta,urgente'],

            'canal'              => ['nullable', 'string', 'max:255'],
            'tipo_entidad'       => ['nullable', 'string', 'max:100'],
            'razon_social'       => ['nullable', 'string', 'max:255'],
            'provincia'          => ['nullable', 'string', 'max:255'],
            'num_plantilla'      => ['nullable', 'integer'],
            'num_puesto_trabajo' => ['nullable', 'integer'],
            'motivo_cierre'      => ['nullable', 'string', 'max:255'],
            'motivo_cierre_detalle' => ['nullable', 'string'],
            'source_external_id' => ['nullable', 'string', 'max:255'],

            'fecha_solicitud' => ['nullable', 'date'],
            'fecha_prevista'  => ['nullable', 'date'],
            'fecha_cierre'    => ['nullable', 'date'],
            'closed_at'       => ['nullable', 'date'],
            'importe_estimado'=> ['nullable', 'numeric'],
            'moneda'          => ['nullable', 'string', 'max:10'],

            'owner_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'owner_team_id' => ['nullable', 'integer', 'exists:teams,id'],

            'email' => ['nullable', 'email'],
            'estado_rgpd' => ['nullable', 'string', 'max:100'],
            'telefono' => ['nullable', 'string'],
            'mobile' => ['nullable', 'string'],
            'nombre' => ['nullable', 'string', 'max:255'],
            'apellidos' => ['nullable', 'string', 'max:255'],
            'empresa' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'account_id'      => 'cuenta',
            'contact_id'      => 'contacto',
            'titulo'          => 'título',
            'descripcion'     => 'descripción',
            'estado'          => 'estado',
            'origen'          => 'origen',
            'prioridad'       => 'prioridad',
            'fecha_solicitud' => 'fecha de solicitud',
            'fecha_prevista'  => 'fecha prevista',
            'fecha_cierre'    => 'fecha de cierre',
            'importe_estimado'=> 'importe estimado',
            'owner_user_id'   => 'propietario',
        ];
    }
}
