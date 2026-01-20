<?php

namespace App\Http\Controllers;

use App\Models\ContactView;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactViewController extends Controller
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
            'account_id'     => ['nullable', 'integer'],
            'af'             => ['nullable', 'string'],
            'columns'        => ['nullable', 'array'],
            'columns.*'      => ['string'],
            'sort_column'    => ['nullable', 'string'],
            'sort_direction' => ['nullable', 'in:asc,desc'],
            'is_default'     => ['nullable', 'boolean'],
        ]);

        $filters = $request->only([
            'q',
            'account_id',
            'af',
        ]);

        if (! empty($data['is_default'])) {
            ContactView::where('user_id', $user->id)->update(['is_default' => false]);
        }

        $view = ContactView::create([
            'user_id'        => $user->id,
            'name'           => $data['name'],
            'is_default'     => (bool) ($data['is_default'] ?? false),
            'filters'        => $filters,
            'columns'        => $data['columns'] ?? null,
            'sort_column'    => $data['sort_column'] ?? null,
            'sort_direction' => $data['sort_direction'] ?? null,
        ]);

        return redirect()
            ->route('contacts.index', ['vista_id' => $view->id])
            ->with('status', 'Vista de contactos guardada correctamente.');
    }

    public function destroy(ContactView $view): RedirectResponse
    {
        $user = request()->user();

        if (! $user || $view->user_id !== $user->id) {
            abort(403);
        }

        $view->delete();

        return redirect()
            ->route('contacts.index')
            ->with('status', 'Vista eliminada.');
    }
}