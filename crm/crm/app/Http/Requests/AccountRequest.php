<?php

namespace App\Http\Requests;

use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $nombreAbreviado = $this->input('nombre_abreviado');
        $name            = $this->input('name');
        $estado          = $this->input('estado');
        $tipoRelacion    = $this->input('tipo_relacion_grupo');

        // Calcula el valor definitivo de nombre_abreviado priorizando nombre comercial.
        $nombreAbreviado = $nombreAbreviado ?: $name;

        // Fallback de estado: usar status heredado o el valor del modelo en edición.
        if (! $estado) {
            $estado = $this->input('status');
        }

        if (! $estado && $this->route('account') instanceof Account) {
            $estado = $this->route('account')->estado;
        }

        if (! $tipoRelacion && $this->route('account') instanceof Account) {
            $tipoRelacion = $this->route('account')->tipo_relacion_grupo;
        }

        if ($nombreAbreviado) {
            $this->merge([
                'nombre_abreviado' => $nombreAbreviado,
            ]);
        }

        if ($estado) {
            $this->merge([
                'estado' => strtolower($estado),
            ]);
        }

        if ($tipoRelacion !== 'filial') {
            $this->merge([
                'parent_account_id' => null,
            ]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // ID de la cuenta (para la regla de unique en customer_code)
        $accountId = $this->route('account'); // puede ser null en create, ID o modelo en update
        $accountIdValue = $accountId instanceof Account ? $accountId->id : $accountId;

        return [
            // --- Básicos ---
            'name'      => ['required', 'string', 'max:255'],
            'nombre_abreviado' => ['required', 'string', 'max:255'],
            'email'     => ['nullable', 'email', 'max:255'],
            'phone'     => ['nullable', 'string', 'max:255', function ($attribute, $value, $fail) {
            $digits = preg_replace('/\D+/', '', (string) $value);
            if ($digits !== '' && strlen($digits) < 9) {
                $fail('El teléfono debe tener al menos 9 dígitos.');
            }
        }],
            'website'   => ['nullable', 'string', 'max:255'],
            'tax_id'    => ['nullable', 'string', 'max:255', 'unique:accounts,tax_id,' . ($accountId instanceof \App\Models\Account ? $accountId->id : $accountId)],
            'estado'    => ['required', 'in:activo,inactivo'],
            'tipo_entidad' => ['required', 'in:empresa_privada,aapp,sin_animo_de_lucro,corporacion_derecho_publico,particular'],
            'lifecycle' => ['nullable', 'in:prospect,customer'],

            // --- Dirección principal ---
            'address'           => ['nullable', 'string', 'max:255'],
            'direccion'         => ['nullable', 'string', 'max:255'],
            'city'              => ['nullable', 'string', 'max:255'],
            'localidad'         => ['nullable', 'string', 'max:255'],
            'state'             => ['nullable', 'string', 'max:255'],
            'provincia'         => ['nullable', 'string', 'max:255'],
            'postal_code'       => ['nullable', 'string', 'max:20'],
            'codigo_postal'     => ['nullable', 'string', 'max:20'],
            'country'           => ['nullable', 'string', 'max:255'],
            'pais'              => ['nullable', 'string', 'max:255'],

            // --- Dirección de facturación (ya existente) ---
            'billing_address'    => ['nullable', 'string', 'max:255'],
            'billing_city'       => ['nullable', 'string', 'max:255'],
            'billing_state'      => ['nullable', 'string', 'max:255'],
            'billing_postal_code'=> ['nullable', 'string', 'max:20'],
            'billing_country'    => ['nullable', 'string', 'max:255'],
            'billing_email'      => ['nullable', 'email', 'max:255'],
            'billing_contact_id' => ['nullable', 'integer', 'exists:contacts,id'],
            'billing_has_payment_issues'=> ['sometimes', 'boolean'],
            'billing_notes'      => ['nullable', 'string'],
            'is_billable'        => ['sometimes', 'boolean'],
            'billing_legal_name' => ['required_if:is_billable,1', 'nullable', 'string', 'max:255'],
            'customer_code'      => [
                'nullable',
                'string',
                'max:100',
                'unique:accounts,customer_code,' . ($accountId instanceof \App\Models\Account ? $accountId->id : $accountId),
            ],

            // --- Perfil compañía / datos corporativos ---
            'industry'          => ['nullable', 'string', 'max:255'],
            'employee_count'    => ['nullable', 'integer'],
            'annual_revenue'    => ['nullable', 'numeric'],
            'legal_name'        => ['nullable', 'string', 'max:255'],
            'fax'               => ['nullable', 'string', 'max:255'],
            'company_type'      => ['nullable', 'string', 'max:255'],
            'products_services' => ['nullable', 'string'],
            'company_size_min'  => ['nullable', 'integer'],
            'company_size_max'  => ['nullable', 'integer'],
            'founded_year'      => ['nullable', 'integer'],
            'habitantes'        => ['nullable', 'integer'],

            // --- Características / certificaciones (existentes) ---
            'public_contracts'      => ['sometimes', 'boolean'],
            'equality_plan'         => ['sometimes', 'boolean'],
            'equality_plan_valid_until' => ['nullable', 'date'],
            'equality_mark'         => ['sometimes', 'boolean'],
            'interest_local'        => ['sometimes', 'boolean'],
            'interest_regional'     => ['sometimes', 'boolean'],
            'interest_national'     => ['sometimes', 'boolean'],
            'no_interest'           => ['sometimes', 'boolean'],
            'quality'               => ['sometimes', 'boolean'],
            'rse'                   => ['sometimes', 'boolean'],
            'otras_certificaciones' => ['nullable', 'string'],

            // --- Gestión comercial ---
            'main_contact_role' => ['nullable', 'string', 'max:255'],
            'legacy_updated_at' => ['nullable', 'date'],
            'sales_department'  => ['nullable', 'string', 'max:255'],
            'cnae'              => ['nullable', 'string', 'max:255'],
            'notes'             => ['nullable', 'string'],
            'tipo_relacion_grupo' => ['nullable', 'in:independiente,matriz,filial'],
            'parent_account_id'   => [
                'nullable',
                'integer',
                'exists:accounts,id',
                Rule::notIn(array_filter([$accountIdValue])),
            ],

            // =========================
            // NUEVOS CAMPOS AÑADIDOS
            // =========================

            // --- Perfil extra ---
            'group_name'         => ['nullable', 'string', 'max:255'], // Campo "Grupo"
            'email_confirmed_at' => ['nullable', 'date'],              // Fecha confirmación email empresa
            'odoo_id'            => ['nullable', 'string', 'max:100'],

            // --- Características (NUEVO BLOQUE "CARACTERÍSTICAS") ---
            // Valores permitidos: si | no | desconocido | null
            'car_plan_igualdad' => ['nullable', 'in:si,no,desconocido'],
            'car_plan_igualdad_vigencia' => ['nullable', 'date'],

            'car_plan_lgtbi' => ['nullable', 'in:si,no,desconocido'],
            'car_plan_lgtbi_vigencia' => ['nullable', 'date'],

            'car_protocolo_acoso_sexual' => ['nullable', 'in:si,no,desconocido'],
            'car_protocolo_acoso_sexual_revision' => ['nullable', 'date'],

            'car_protocolo_acoso_laboral' => ['nullable', 'in:si,no,desconocido'],
            'car_protocolo_acoso_laboral_revision' => ['nullable', 'date'],

            'car_protocolo_acoso_lgtbi' => ['nullable', 'in:si,no,desconocido'],
            'car_protocolo_acoso_lgtbi_revision' => ['nullable', 'date'],

            'car_vpt' => ['nullable', 'in:si,no,desconocido'],

            'car_registro_retributivo' => ['nullable', 'in:si,no,desconocido'],
            'car_registro_retributivo_revision' => ['nullable', 'date'],

            'car_plan_igualdad_estrategico' => ['nullable', 'in:si,no,desconocido'],
            'car_plan_igualdad_estrategico_vigencia' => ['nullable', 'date'],

            'car_sistema_gestion' => ['nullable', 'in:si,no,desconocido'],
        ];
    }
}
