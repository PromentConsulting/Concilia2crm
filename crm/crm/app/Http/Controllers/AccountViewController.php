<?php

namespace App\Http\Controllers;

use App\Models\AccountView;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AccountViewController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'q'              => ['nullable', 'string'],
            'lifecycle'      => ['nullable', 'string'],
            'country'        => ['nullable', 'string'],
            'columns'        => ['nullable', 'array'],
            'columns.*'      => ['string'],
            'sort_column'    => ['nullable', 'string'],
            'sort_direction' => ['nullable', 'in:asc,desc'],
            'is_default'     => ['nullable', 'boolean'],
        ]);

        // Guardamos también filtros avanzados directamente desde el request
        $filters = $request->only([
            'q',
            'lifecycle',
            'country',
            'f_name',
            'f_email',
            'f_phone',
            'f_tax_id',
            'f_website',
            'f_created_from',
            'f_created_to',
        ]);

        if (! empty($data['is_default'])) {
            AccountView::where('user_id', $user->id)->update(['is_default' => false]);
        }

        $view = AccountView::create([
            'user_id'        => $user->id,
            'name'           => $data['name'],
            'is_default'     => (bool) ($data['is_default'] ?? false),
            'filters'        => $filters,
            'columns'        => $data['columns'] ?? null,
            'sort_column'    => $data['sort_column'] ?? 'name',
            'sort_direction' => $data['sort_direction'] ?? 'asc',
        ]);

        return redirect()
            ->route('accounts.index', ['vista_id' => $view->id])
            ->with('status', 'Vista guardada correctamente.');
    }

    public function destroy(AccountView $view): RedirectResponse
    {
        $user = request()->user();
        if (! $user || $view->user_id !== $user->id) {
            abort(403);
        }

        $view->delete();

        return redirect()
            ->route('accounts.index')
            ->with('status', 'Vista eliminada.');
    }
}
