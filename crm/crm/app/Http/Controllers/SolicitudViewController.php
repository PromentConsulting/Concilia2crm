<?php

namespace App\Http\Controllers;

use App\Models\SolicitudView;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SolicitudViewController extends Controller
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
            'origen'         => ['nullable', 'string'],
            'prioridad'      => ['nullable', 'string'],
            'owner_user_id'  => ['nullable', 'integer'],
            'is_default'     => ['nullable', 'boolean'],
        ]);

        $filters = $request->only([
            'q',
            'estado',
            'origen',
            'prioridad',
            'owner_user_id',
        ]);

        if (! empty($data['is_default'])) {
            SolicitudView::where('user_id', $user->id)->update(['is_default' => false]);
        }

        $view = SolicitudView::create([
            'user_id'    => $user->id,
            'name'       => $data['name'],
            'is_default' => (bool) ($data['is_default'] ?? false),
            'filters'    => $filters,
        ]);

        return redirect()
            ->route('solicitudes.index', ['vista_id' => $view->id])
            ->with('status', 'Vista de solicitudes guardada correctamente.');
    }

    public function destroy(SolicitudView $view): RedirectResponse
    {
        $user = request()->user();

        if (! $user || $view->user_id !== $user->id) {
            abort(403);
        }

        $view->delete();

        return redirect()
            ->route('solicitudes.index')
            ->with('status', 'Vista eliminada.');
    }
}