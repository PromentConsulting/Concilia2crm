<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Account;
use App\Models\Contact;
use App\Models\ContactRole;
use App\Models\ContactEmail;
use App\Support\Concerns\InteractsWithContactsTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ContactController extends Controller
{
    use InteractsWithContactsTable;

    public function index(Request $request): View
    {
        $search    = trim((string) $request->query('q', '')) ?: null;
        $accountId = $request->integer('account_id') ?: null;
        $advanced  = $request->query('af');

        // Vistas guardadas de contactos
        if (class_exists(\App\Models\ContactView::class)) {
            $views = \App\Models\ContactView::query()
                ->where('user_id', optional($request->user())->id)
                ->orderBy('name')
                ->get();
        } else {
            $views = collect();
        }

        $activeView = null;
        $vistaId    = $request->query('vista_id');

        if ($vistaId && $views->isNotEmpty()) {
            $activeView = $views->firstWhere('id', (int) $vistaId);
        } elseif ($views->isNotEmpty()) {
            $activeView = $views->firstWhere('is_default', true);
        }

        // Si no vienen parámetros, aplicamos filtros guardados en la vista
        if (! $search && ! $accountId && ! $advanced && $activeView && is_array($activeView->filters)) {
            $search    = $activeView->filters['q'] ?? null;
            $accountId = $activeView->filters['account_id'] ?? null;
            $advanced  = $activeView->filters['af'] ?? null;
        }

        $contacts = Contact::query()
            ->with([
                'accounts:id,name',
                'primaryAccount:id,name',
            ])
            ->when($search, fn (Builder $query) => $this->applyContactSearch($query, $search))
            ->when($accountId, fn (Builder $query) => $query->whereHas('accounts', function (Builder $q) use ($accountId) {
                $q->where('accounts.id', $accountId);
            }));

        $contacts = $this->applyAdvancedFilters($contacts, $advanced);

        $contacts = $this->orderContactsByName($contacts)
            ->paginate(25)
            ->withQueryString();

        $accounts = Account::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->limit(200)
            ->get();

        return view('contacts.index', [
            'contacts' => $contacts,
            'accounts' => $accounts,
            'filters'  => [
                'q'          => $search,
                'account_id' => $accountId,
                'af'         => $advanced,
            ],
            'views'        => $views,
            'activeView'   => $activeView,
        ]);
    }

    public function create(Request $request): View
    {
        $accounts = Account::query()->orderBy('name')->get(['id', 'name']);

        return view('contacts.create', [
            'accounts'       => $accounts,
            'defaultAccount' => $request->integer('account_id'),
        ]);
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        $contact = Contact::create($this->persistableAttributes($request));

        $this->syncPrimaryEmail($contact, $request);
        $this->syncAccountsPivot($contact, $request);
        $this->syncRoles($contact, $request);

        return redirect()
            ->route('contacts.show', $contact)
            ->with('status', 'Contacto creado correctamente.');
    }

    public function show(Contact $contact): View
    {
        $contact->load([
            'accounts',
            'primaryAccount',
            'roles',
            'emails',
        ]);

        return view('contacts.show', [
            'contact' => $contact,
        ]);
    }

    public function edit(Contact $contact): View
    {
        $accounts = Account::query()->orderBy('name')->get(['id', 'name']);

        $contact->load(['accounts', 'roles', 'emails', 'primaryAccount']);

        return view('contacts.edit', [
            'contact'  => $contact,
            'accounts' => $accounts,
        ]);
    }

    public function update(ContactRequest $request, Contact $contact): RedirectResponse
    {
        $contact->update($this->persistableAttributes($request));

        $this->syncPrimaryEmail($contact, $request);
        $this->syncAccountsPivot($contact, $request);
        $this->syncRoles($contact, $request);

        return redirect()
            ->route('contacts.show', $contact)
            ->with('status', 'Contacto actualizado correctamente.');
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $contact->delete();

        return redirect()
            ->route('contacts.index')
            ->with('status', 'Contacto eliminado.');
    }

   public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action'             => ['required', 'string', 'in:assign_account,delete'],
            'select_all'         => ['nullable', 'boolean'],
            'ids'                => ['nullable', 'array', 'required_without:select_all'],
            'ids.*'              => ['integer'],
            'target_account_id'  => ['nullable', 'integer', 'exists:accounts,id'],
            'q'                  => ['nullable', 'string'],
            'af'                 => ['nullable', 'string'],
            'account_id'         => ['nullable', 'integer'],
        ]);

        if ($validated['action'] === 'assign_account' && empty($validated['target_account_id'])) {
            return back()->withErrors(['target_account_id' => 'Selecciona una cuenta destino.']);
        }

        $query = $this->selectionQuery($request, $validated);

        $updated = match ($validated['action']) {
            'assign_account' => $this->bulkAssignAccount($query, (int) $validated['target_account_id']),
            'delete' => $query->delete(),
            default => 0,
        };

        return back()->with('status', "Acción aplicada sobre {$updated} contactos.");
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'format'      => ['sometimes', 'string', 'in:csv,xlsx'],
            'select_all'  => ['nullable', 'boolean'],
            'ids'         => ['nullable', 'array', 'required_without:select_all'],
            'ids.*'       => ['integer'],
            'q'           => ['nullable', 'string'],
            'af'          => ['nullable', 'string'],
            'account_id'  => ['nullable', 'integer'],
        ]);

        $format = $validated['format'] ?? 'csv';

        $roleColumn        = $this->hasContactColumn('role') ? 'role' : ($this->hasContactColumn('job_title') ? 'job_title' : null);
        $phoneColumn       = $this->hasContactColumn('phone') ? 'phone' : ($this->hasContactColumn('mobile') ? 'mobile' : null);
        $nameColumn        = $this->hasContactColumn('name') ? 'name' : null;
        $emailColumn       = $this->hasContactColumn('email') ? 'email' : null;
        $createdAtColumn   = $this->hasContactColumn('created_at') ? 'created_at' : null;
        $primaryAccountCol = $this->hasContactColumn('primary_account_id') ? 'primary_account_id' : null;

        $columns = array_values(array_filter([
            'id',
            $nameColumn,
            'first_name',
            'last_name',
            $emailColumn,
            $phoneColumn,
            $roleColumn,
            $createdAtColumn,
            $primaryAccountCol,
        ]));

        $contacts = $this->selectionQuery($request, $validated)
            ->with(['primaryAccount:id,name'])
            ->orderBy('id')
            ->get($columns);

        $headers = [
            ['label' => 'ID', 'present' => true],
            ['label' => 'Nombre', 'present' => true],
            ['label' => 'Email', 'present' => (bool) $emailColumn],
            ['label' => 'Teléfono', 'present' => (bool) $phoneColumn],
            ['label' => 'Cargo', 'present' => (bool) $roleColumn],
            ['label' => 'Cuenta principal', 'present' => (bool) $primaryAccountCol],
            ['label' => 'Creado el', 'present' => (bool) $createdAtColumn],
        ];

        $filename = 'contacts_export_' . now()->format('Ymd_His') . '.' . $format;

        return response()->stream(function () use ($contacts, $headers, $roleColumn, $phoneColumn, $nameColumn, $emailColumn, $createdAtColumn, $primaryAccountCol) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, array_column(array_filter($headers, fn ($h) => $h['present']), 'label'), ';');

            foreach ($contacts as $contact) {
                $displayName = trim(($contact->first_name ?? '') . ' ' . ($contact->last_name ?? ''));

                if ($displayName === '' && $nameColumn) {
                    $displayName = $contact->{$nameColumn} ?? '';
                }

                $role  = $roleColumn ? ($contact->{$roleColumn} ?? null) : null;
                $phone = $phoneColumn ? ($contact->{$phoneColumn} ?? null) : null;

                $row = [
                    $contact->id,
                    $displayName,
                ];

                if ($emailColumn) {
                    $row[] = $contact->{$emailColumn} ?? null;
                }

                if ($phoneColumn) {
                    $row[] = $phone;
                }

                if ($roleColumn) {
                    $row[] = $role;
                }

                if ($primaryAccountCol ?? false) {
                    $row[] = optional($contact->primaryAccount)->name;
                }

                if ($createdAtColumn) {
                    $row[] = optional($contact->{$createdAtColumn})?->format('Y-m-d');
                }

                fputcsv($handle, $row, ';');
            }

            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function persistableAttributes(ContactRequest $request): array
    {
        $data = $request->validated();

        if ($data === []) {
            return [];
        }

        $attributes = [];

        if ($this->hasContactColumn('account_id')) {
            $attributes['account_id'] = $request->integer('account_id') ?: null;
        }

        if (array_key_exists('email', $data) && $this->hasContactColumn('email')) {
            $attributes['email'] = $data['email'];
        }

        if (array_key_exists('primary_email', $data) && $this->hasContactColumn('primary_email')) {
            $attributes['primary_email'] = $data['primary_email'];
        }

        if (array_key_exists('phone', $data)) {
            if ($this->hasContactColumn('phone')) {
                $attributes['phone'] = $data['phone'];
            } elseif ($this->hasContactColumn('mobile')) {
                $attributes['mobile'] = $data['phone'];
            }
        }

        if ($this->hasContactColumn('role')) {
            $attributes['role'] = $data['role'] ?? null;
        } elseif ($this->hasContactColumn('job_title')) {
            $attributes['job_title'] = $data['role'] ?? null;
        }

        if ($this->hasContactColumn('notes')) {
            $attributes['notes'] = $data['notes'] ?? null;
        }

        $isPrimary = $request->boolean('is_primary');
        if ($this->hasContactColumn('is_primary')) {
            $attributes['is_primary'] = $isPrimary;
        } elseif ($this->hasContactColumn('primary')) {
            $attributes['primary'] = $isPrimary;
        }

        $firstName = trim((string) ($data['first_name'] ?? ''));
        $lastName  = trim((string) ($data['last_name'] ?? ''));

        if ($this->hasContactColumn('first_name')) {
            $attributes['first_name'] = $firstName;
        }

        if ($this->hasContactColumn('last_name')) {
            $attributes['last_name'] = $lastName;
        }

        if (array_key_exists('name', $data)) {
            $name = trim((string) $data['name']);

            if ($this->hasContactColumn('name')) {
                $attributes['name'] = $name;
            }
        }

        if (! array_key_exists('name', $data) && $this->hasContactColumn('name') && ($firstName !== '' || $lastName !== '')) {
            $attributes['name'] = trim($firstName . ' ' . $lastName);
        }

        return $attributes;
    }

   private function selectionQuery(Request $request, array $validated): Builder
    {
        $search    = trim((string) $validated['q'] ?? '') ?: null;
        $accountId = $validated['account_id'] ?? $request->integer('account_id') ?: null;
        $advanced  = $validated['af'] ?? $request->query('af');

        $query = Contact::query()
            ->when($search, fn (Builder $q) => $this->applyContactSearch($q, $search))
            ->when($accountId, fn (Builder $q) => $q->whereHas('accounts', function (Builder $qa) use ($accountId) {
                $qa->where('accounts.id', $accountId);
            }));

        $query = $this->applyAdvancedFilters($query, $advanced);

        if (! ($validated['select_all'] ?? false)) {
            $ids = $validated['ids'] ?? [];
            $query->whereIn('id', $ids);
        }

        return $query;
    }

    private function bulkAssignAccount(Builder $query, int $accountId): int
    {
        $account = Account::find($accountId);

        if (! $account) {
            return 0;
        }

        $updated = 0;

        $query->chunkById(200, function ($contacts) use (&$updated, $account) {
            foreach ($contacts as $contact) {
                $contact->accounts()->syncWithoutDetaching([$account->id => ['es_principal' => true]]);
                $updated++;
            }
        });

        return $updated;
    }

    private function applyAdvancedFilters(Builder $query, ?string $raw): Builder
    {
        if (! $raw) {
            return $query;
        }

        $decoded = json_decode($raw, true);

        if (! is_array($decoded) || ! isset($decoded['rules']) || ! is_array($decoded['rules'])) {
            return $query;
        }

        $match = ($decoded['match'] ?? 'all') === 'any' ? 'or' : 'and';
        $rules = $decoded['rules'];

        $query->where(function (Builder $builder) use ($rules, $match) {
            foreach ($rules as $rule) {
                if (! is_array($rule) || empty($rule['field']) || empty($rule['operator'])) {
                    continue;
                }

                $method = $match === 'or' ? 'orWhere' : 'where';

                $builder->{$method}(function (Builder $sub) use ($rule) {
                    $this->applyRule($sub, $rule);
                });
            }
        });

        return $query;
    }

    private function applyRule(Builder $query, array $rule): void
    {
        $field    = $rule['field'];
        $operator = $rule['operator'];
        $value    = $rule['value'] ?? null;
        $value2   = $rule['value2'] ?? null;

        $columnMap = [
            'name'       => $this->hasContactColumn('name') ? 'name' : null,
            'email'      => $this->hasContactColumn('email') ? 'email' : null,
            'phone'      => $this->hasContactColumn('phone') ? 'phone' : ($this->hasContactColumn('mobile') ? 'mobile' : null),
            'role'       => $this->hasContactColumn('role') ? 'role' : ($this->hasContactColumn('job_title') ? 'job_title' : null),
            'created_at' => $this->hasContactColumn('created_at') ? 'created_at' : null,
        ];

        if ($field === 'account') {
            $this->applyStringRuleToRelation($query, 'accounts', 'name', $operator, $value, $value2);
            return;
        }

        if (! array_key_exists($field, $columnMap) || $columnMap[$field] === null) {
            return;
        }

        $column = $columnMap[$field];

        if ($field === 'created_at') {
            $this->applyDateRule($query, $column, $operator, $value, $value2);
            return;
        }

        $this->applyStringRule($query, $column, $operator, $value, $value2);
    }

    private function applyStringRule(Builder $query, string $column, string $operator, ?string $value, ?string $value2): void
    {
        $value = $value ?? '';

        match ($operator) {
            'contains'      => $query->where($column, 'like', "%{$value}%"),
            'not_contains'  => $query->where($column, 'not like', "%{$value}%"),
            'equals'        => $query->where($column, '=', $value),
            'not_equals'    => $query->where($column, '!=', $value),
            'starts_with'   => $query->where($column, 'like', "{$value}%"),
            'ends_with'     => $query->where($column, 'like', "%{$value}"),
            'is_empty'      => $query->whereNull($column)->orWhere($column, ''),
            'is_not_empty'  => $query->whereNotNull($column)->where($column, '!=', ''),
            default => null,
        };
    }

    private function applyStringRuleToRelation(
        Builder $query,
        string $relation,
        string $column,
        string $operator,
        ?string $value,
        ?string $value2
    ): void {
        $value = $value ?? '';

        $query->whereHas($relation, function (Builder $rel) use ($column, $operator, $value, $value2) {
            $this->applyStringRule($rel, $column, $operator, $value, $value2);
        });
    }

    private function applyDateRule(Builder $query, string $column, string $operator, ?string $value, ?string $value2): void
    {
        match ($operator) {
            'on'      => $query->whereDate($column, $value),
            'before'  => $query->whereDate($column, '<', $value),
            'after'   => $query->whereDate($column, '>', $value),
            'between' => $query->whereBetween($column, [$value, $value2]),
            'is_empty' => $query->whereNull($column),
            'is_not_empty' => $query->whereNotNull($column),
            default => null,
        };
    }


    private function syncPrimaryEmail(Contact $contact, Request $request): void
    {
        $primaryEmail = trim((string) $request->input('primary_email', ''));

        if ($primaryEmail === '') {
            return;
        }

        if (Schema::hasColumn('contacts', 'primary_email')) {
            $contact->primary_email = $primaryEmail;
            $contact->save();
        }

        // Mantenemos alias principal en contact_emails
        ContactEmail::updateOrCreate(
            [
                'contact_id'  => $contact->id,
                'is_primary'  => true,
            ],
            [
                'email'       => $primaryEmail,
            ]
        );
    }

    private function syncAccountsPivot(Contact $contact, Request $request): void
    {
        $rows = $request->input('accounts', []);

        if (!is_array($rows)) {
            $rows = [];
        }

        if ($rows === []) {
            $accountId = $request->integer('account_id');

            if ($accountId) {
                $rows = [[
                    'account_id'   => $accountId,
                    'categoria'    => 'otros',
                    'es_principal' => $request->boolean('is_primary'),
                ]];
            }
        }

        $syncData         = [];
        $primaryAccountId = null;

        foreach ($rows as $row) {
            $accountId = isset($row['account_id']) ? (int) $row['account_id'] : null;
            if (!$accountId) {
                continue;
            }

            $categoria = $row['categoria'] ?? 'otros';
            $esPrincipal = filter_var($row['es_principal'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $syncData[$accountId] = [
                'categoria'    => $categoria,
                'es_principal' => $esPrincipal,
            ];

            if ($esPrincipal) {
                $primaryAccountId = $accountId;
            }
        }

        if ($syncData === []) {
            $contact->accounts()->detach();
            $contact->primary_account_id = null;
            $contact->save();

            return;
        }

        $contact->accounts()->sync($syncData);

        if ($primaryAccountId === null) {
            $primaryAccountId = array_key_first($syncData);
        }

        if (Schema::hasColumn('contacts', 'primary_account_id')) {
            $contact->primary_account_id = $primaryAccountId;
            $contact->save();
        }
    }

    private function syncRoles(Contact $contact, Request $request): void
    {
        $roles = $request->input('roles', []);
        $otro  = trim((string) $request->input('role_otro', ''));

        if (!is_array($roles)) {
            $roles = [];
        }

        $contact->roles()->delete();

        foreach ($roles as $roleKey) {
            $roleKey = (string) $roleKey;
            if ($roleKey === '') {
                continue;
            }

            ContactRole::create([
                'contact_id'         => $contact->id,
                'role'               => $roleKey,
                'label_personalizado'=> null,
            ]);
        }

        if ($otro !== '') {
            ContactRole::create([
                'contact_id'         => $contact->id,
                'role'               => 'otro',
                'label_personalizado'=> $otro,
            ]);
        }
    }
}
