<?php

namespace App\Services;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Builder;

class CampaignSegmentBuilder
{
    public function build(array $filters = []): Builder
    {
        $query = Contact::query()
            ->with('primaryAccount')
            ->select('contacts.*')
            ->distinct();

        $query->where(function (Builder $q) {
            $q->whereNull('estado_contacto')
                ->orWhereNotIn('estado_contacto', ['rebotado', 'baja_marketing', 'no_localizable']);
        });

        $accountFilter = function (Builder $q) use ($filters) {
            if (! empty($filters['account_tipo_entidad'])) {
                $q->whereIn('tipo_entidad', (array) $filters['account_tipo_entidad']);
            }

            if (! empty($filters['account_estado'])) {
                $q->whereIn('estado', (array) $filters['account_estado']);
            }

            if (! empty($filters['account_provincia'])) {
                $q->where('provincia', $filters['account_provincia']);
            }

            if (! empty($filters['account_quality'])) {
                $q->where('quality', true);
            }

            if (! empty($filters['account_rse'])) {
                $q->where('rse', true);
            }

            if (! empty($filters['account_intereses'])) {
                foreach ((array) $filters['account_intereses'] as $interesFlag) {
                    $q->where($interesFlag, true);
                }
            }

            if (! empty($filters['account_equality_plan'])) {
                $q->where(function (Builder $builder) {
                    $builder->where('equality_plan', true)
                        ->orWhere('equality_mark', true);
                });
            }
        };

        $query->where(function (Builder $contactQuery) use ($accountFilter) {
            $contactQuery
                ->whereHas('accounts', $accountFilter)
                ->orWhereHas('primaryAccount', $accountFilter);
        });

        return $query;
    }
}