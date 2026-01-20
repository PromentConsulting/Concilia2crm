<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Solicitud;
use Illuminate\Support\Arr;

class SolicitudService
{
    public function createFromPayload(array $data): Solicitud
    {
        $payload = $this->normalizePayload($data);
        $contact = $this->resolveContact($payload);
        $account = $this->resolveAccount($payload, $contact);

        $solicitudData = array_merge($payload, [
            'contact_id' => $contact?->id,
            'account_id' => $account?->id,
        ]);

        $solicitud = Solicitud::create($solicitudData);
        $solicitud->logEstado(null, $solicitud->estado, [
            'user_id' => optional(auth()->user())->id,
        ]);

        return $solicitud;
    }

    protected function normalizePayload(array $data): array
    {
        $defaults = [
            'estado' => 'pendiente_asignacion',
            'prioridad' => 'media',
            'origen' => 'manual',
        ];

        if (! empty($data['telefono'])) {
            $data['telefono'] = preg_replace('/[^\d+]/', '', (string) $data['telefono']);
        }

        return array_merge($defaults, Arr::only($data, [
            'titulo',
            'descripcion',
            'texto_peticion',
            'estado',
            'origen',
            'prioridad',
            'tipo_servicio',
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
            'email',
            'estado_rgpd',
            'telefono',
            'mobile',
            'nombre',
            'apellidos',
            'empresa',
        ]));
    }

    protected function resolveContact(array $payload): ?Contact
    {
        $email = Arr::get($payload, 'email');
        if (! $email) {
            return null;
        }

        $contact = Contact::query()
            ->where('primary_email', $email)
            ->orWhere('email', $email)
            ->first();

        if ($contact) {
            $this->mergeContactData($contact, $payload);
            return $contact;
        }

        return Contact::create([
            'first_name' => Arr::get($payload, 'nombre') ?? Arr::get($payload, 'first_name'),
            'last_name'  => Arr::get($payload, 'apellidos') ?? Arr::get($payload, 'last_name'),
            'primary_email' => $email,
            'estado_rgpd' => Arr::get($payload, 'estado_rgpd'),
            'estado_contacto' => 'activo',
            'phone' => Arr::get($payload, 'telefono'),
            'mobile' => Arr::get($payload, 'mobile'),
        ]);
    }

    protected function mergeContactData(Contact $contact, array $payload): void
    {
        $camposActualizables = ['first_name', 'last_name', 'primary_email', 'phone', 'mobile', 'estado_rgpd'];

        foreach ($camposActualizables as $campo) {
            $nuevoValor = Arr::get($payload, $campo);
            if ($nuevoValor && empty($contact->{$campo})) {
                $contact->{$campo} = $nuevoValor;
            }
        }

        $contact->save();
    }

    protected function resolveAccount(array $payload, ?Contact $contact): ?Account
    {
        if ($contact && $contact->primaryAccount) {
            return $contact->primaryAccount;
        }

        $name = Arr::get($payload, 'razon_social') ?? Arr::get($payload, 'empresa');
        if (! $name) {
            return null;
        }

        $account = Account::query()->where('name', $name)->first();
        if ($account) {
            return $account;
        }

        $account = Account::create([
            'name' => $name,
            'estado' => 'prospecto',
            'tipo_entidad' => Arr::get($payload, 'tipo_entidad'),
            'provincia' => Arr::get($payload, 'provincia'),
        ]);

        if ($contact) {
            $contact->account_id = $account->id;
            $contact->save();
        }

        return $account;
    }
}