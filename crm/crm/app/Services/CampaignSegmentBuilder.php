<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Builder;

class CampaignSegmentBuilder
{
    public function build(array $filters = []): Builder
    {
        $query = Contact::query()->with('primaryAccount');

        $query->where(function (Builder $q) {
            $q->whereNull('estado_contacto')
                ->orWhereNotIn('estado_contacto', ['rebotado', 'baja_marketing', 'no_localizable']);
        });

        if (! empty($filters['estado_rgpd'])) {
            $query->where('estado_rgpd', $filters['estado_rgpd']);
        }

        if (! empty($filters['roles'])) {
            $query->whereHas('roles', fn (Builder $q) => $q->whereIn('role', (array) $filters['roles']));
        }

        if (! empty($filters['niveles_decision'])) {
            $query->whereIn('nivel_decision', (array) $filters['niveles_decision']);
        }

        if (! empty($filters['idioma'])) {
            $query->where('idioma', $filters['idioma']);
        }

        if (! empty($filters['estado_contacto'])) {
            $query->whereIn('estado_contacto', (array) $filters['estado_contacto']);
        }

        if (! empty($filters['account_estado'])) {
            $query->whereHas('primaryAccount', fn (Builder $q) => $q->whereIn('estado', (array) $filters['account_estado']));
        }

        if (! empty($filters['account_sector'])) {
            $query->whereHas('primaryAccount', fn (Builder $q) => $q->whereIn('industry', (array) $filters['account_sector']));
        }

        if (! empty($filters['account_comunidad'])) {
            $query->whereHas('primaryAccount', fn (Builder $q) => $q->whereIn('state', (array) $filters['account_comunidad']));
        }

        if (! empty($filters['account_provincia'])) {
            $query->whereHas('primaryAccount', fn (Builder $q) => $q->whereIn('provincia', (array) $filters['account_provincia']));
        }

        return $query;
    }
}