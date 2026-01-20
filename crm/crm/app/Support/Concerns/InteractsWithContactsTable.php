<?php

namespace App\Support\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation; // 👈 IMPORTANTE
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait InteractsWithContactsTable
{
    private ?array $contactColumnsCache = null;

    protected function contactColumns(): array
    {
        if ($this->contactColumnsCache !== null) {
            return $this->contactColumnsCache;
        }

        if (! Schema::hasTable('contacts')) {
            return $this->contactColumnsCache = [];
        }

        return $this->contactColumnsCache = Schema::getColumnListing('contacts');
    }

    protected function hasContactColumn(string $column): bool
    {
        return in_array($column, $this->contactColumns(), true);
    }

    protected function orderContactsByName(Builder|Relation $query): Builder
    {
        $builder = $query instanceof Relation ? $query->getQuery() : $query;

        if ($this->hasContactColumn('name')) {
            return $builder->orderBy('name');
        }

        if ($this->hasContactColumn('last_name')) {
            $builder->orderBy('last_name');
        }

        if ($this->hasContactColumn('first_name')) {
            $builder->orderBy('first_name');
        }

        return $builder->orderBy('id');
    }

    protected function applyContactSearch(Builder $query, string $term): void
    {
        $query->where(function (Builder $sub) use ($term) {
            if ($this->hasContactColumn('name')) {
                $sub->orWhere('name', 'like', "%{$term}%");
            } else {
                if ($this->hasContactColumn('first_name')) {
                    $sub->orWhere('first_name', 'like', "%{$term}%");
                }

                if ($this->hasContactColumn('last_name')) {
                    $sub->orWhere('last_name', 'like', "%{$term}%");
                }

                if ($this->hasContactColumn('first_name') && $this->hasContactColumn('last_name')) {
                    $sub->orWhere(DB::raw("concat_ws(' ', first_name, last_name)"), 'like', "%{$term}%");
                }
            }

            if ($this->hasContactColumn('email')) {
                $sub->orWhere('email', 'like', "%{$term}%");
            }

            if ($this->hasContactColumn('phone')) {
                $sub->orWhere('phone', 'like', "%{$term}%");
            }

            if ($this->hasContactColumn('mobile')) {
                $sub->orWhere('mobile', 'like', "%{$term}%");
            }
        });
    }
}
