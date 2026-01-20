<?php

namespace App\Http\Controllers;

use App\Models\PedidoView;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PedidoViewController extends Controller
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
            'estado'         => ['nullable', 'string'],
            'anio'           => ['nullable', 'string'],
            'af'             => ['nullable', 'string'],
            'columns'        => ['nullable', 'array'],
            'columns.*'      => ['string'],
            'sort_column'    => ['nullable', 'string'],
            'sort_direction' => ['nullable', 'in:asc,desc'],
            'is_default'     => ['nullable', 'boolean'],
        ]);

        $filters = $request->only([
            'q',
            'estado',
            'anio',
            'af',
        ]);

        if (! empty($data['is_default'])) {
            PedidoView::where('user_id', $user->id)->update(['is_default' => false]);
        }

        $view = PedidoView::create([
            'user_id'        => $user->id,
            'name'           => $data['name'],
            'is_default'     => (bool) ($data['is_default'] ?? false),
            'filters'        => $filters,
            'columns'        => $data['columns'] ?? null,
            'sort_column'    => $data['sort_column'] ?? null,
            'sort_direction' => $data['sort_direction'] ?? null,
        ]);

        return redirect()
            ->route('pedidos.index', ['vista_id' => $view->id])
            ->with('status', 'Vista de pedidos guardada correctamente.');
    }

    public function destroy(PedidoView $view): RedirectResponse
    {
        $user = request()->user();

        if (! $user || $view->user_id !== $user->id) {
            abort(403);
        }

        $view->delete();

        return redirect()
            ->route('pedidos.index')
            ->with('status', 'Vista eliminada.');
    }
}