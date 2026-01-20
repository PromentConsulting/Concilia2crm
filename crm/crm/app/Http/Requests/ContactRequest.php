<?php

namespace App\Http\Requests;

use App\Models\ContactEmail;
use App\Support\Concerns\InteractsWithContactsTable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactRequest extends FormRequest
{
    use InteractsWithContactsTable;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $contact   = $this->route('contact');
        $contactId = $contact?->id;

        $currentEstado = $contact?->estado_contacto;
        $nuevoEstado   = $this->input('estado_contacto');

        $estadoChangeRules = ['nullable', 'string'];

        if (($contact && $nuevoEstado !== null && $nuevoEstado !== $currentEstado) || (! $contact && $nuevoEstado && $nuevoEstado !== 'activo')) {
            $estadoChangeRules[] = 'required';
        }

        $emailValue = strtolower(trim((string) ($this->input('primary_email') ?? $this->input('email'))));

        $rules = [
            'email'      => ['required', 'email', 'max:255', Rule::unique('contacts', 'email')->ignore($contactId)],
            'primary_email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('contacts', 'primary_email')->ignore($contactId),
                function ($attribute, $value, $fail) use ($contactId) {
                    $exists = ContactEmail::query()
                        ->where('email', strtolower($value))
                        ->when($contactId, fn ($q) => $q->where('contact_id', '!=', $contactId))
                        ->exists();

                    if ($exists) {
                        $fail('El email ya está asociado a otro contacto.');
                    }
                },
            ],
            'account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'phone'      => ['nullable', 'string', 'max:255'],
            'mobile'     => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'job_title'  => ['nullable', 'string', 'max:255'],
            'notes'      => ['nullable', 'string'],
            'role'       => ['nullable', 'string', 'max:255'],
            'role_otro'  => ['nullable', 'string', 'max:255'],
            'is_primary' => ['sometimes', 'boolean'],
            'comentarios'=> ['nullable', 'string'],
            'flag_campanas'    => ['sometimes', 'boolean'],
            'flag_facturacion' => ['sometimes', 'boolean'],
            'nivel_decision'   => ['nullable', Rule::in(['alto', 'medio', 'bajo'])],
            'estado_rgpd'      => ['nullable', Rule::in(['consentimiento_otorgado', 'no_otorgado', 'revocado'])],
            'canal_preferido'  => ['nullable', Rule::in(['email', 'telefono', 'movil', 'otro'])],
            'mensajeria_instantanea' => ['nullable', 'string', 'max:255'],
            'estado_contacto'  => ['required', Rule::in(['activo', 'inactivo', 'rebotado', 'baja_marketing', 'no_localizable'])],
            'motivo_cambio_estado' => $estadoChangeRules,
            // RGPD
            'consent_email'     => ['boolean'],
            'consent_phone'     => ['boolean'],
            'consent_sms'       => ['boolean'],
            'consent_marketing' => ['boolean'],
            'consent_date'      => ['nullable', 'date'],
        ];

        if ($this->hasContactColumn('name') && ! ($this->hasContactColumn('first_name') || $this->hasContactColumn('last_name'))) {
            $rules['name'] = ['required', 'string', 'max:255'];
        }

        if ($this->hasContactColumn('first_name')) {
            $rules['first_name'] = ['required', 'string', 'max:255'];
        }

        if ($this->hasContactColumn('last_name')) {
            $rules['last_name'] = ['required', 'string', 'max:255'];
        }

        // Para coherencia con la regla global de emails
        $this->merge([
            'primary_email' => $emailValue,
            'email'         => $emailValue,
        ]);

        return $rules;
    }
}