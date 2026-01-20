<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DocumentoController extends Controller
{
    public function index(Request $request): View
    {
        $query = Documento::query()
            ->with(['account', 'solicitud', 'peticion', 'pedido', 'owner']);

        if ($request->filled('account_id')) {
            $query->where('account_id', $request->query('account_id'));
        }
        if ($request->filled('solicitud_id')) {
            $query->where('solicitud_id', $request->query('solicitud_id'));
        }
        if ($request->filled('peticion_id')) {
            $query->where('peticion_id', $request->query('peticion_id'));
        }
        if ($request->filled('pedido_id')) {
            $query->where('pedido_id', $request->query('pedido_id'));
        }

        $documentos = $query->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('documentos.index', compact('documentos'));
    }

    public function create(Request $request): View
    {
        $accounts = Account::orderBy('name')->get(['id', 'name']);

        $prefill = [
            'account_id'   => $request->query('account_id'),
            'solicitud_id' => $request->query('solicitud_id'),
            'peticion_id'  => $request->query('peticion_id'),
            'pedido_id'    => $request->query('pedido_id'),
        ];

        return view('documentos.create', [
            'accounts' => $accounts,
            'prefill'  => $prefill,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'titulo'         => ['required', 'string', 'max:255'],
            'tipo'           => ['nullable', 'string', 'max:100'],
            'descripcion'    => ['nullable', 'string'],
            'fecha_documento'=> ['nullable', 'date'],

            'account_id'     => ['nullable', 'exists:accounts,id'],
            'solicitud_id'   => ['nullable', 'exists:solicitudes,id'],
            'peticion_id'    => ['nullable', 'exists:peticiones,id'],
            'pedido_id'      => ['nullable', 'exists:pedidos,id'],

            'archivo'        => ['required', 'file', 'max:20480'], // 20 MB
        ]);

        if ($request->user()) {
            $data['owner_user_id'] = $request->user()->id;
        }

        $file = $request->file('archivo');
        $path = $file->store('documentos', 'public');

        $data['ruta']            = $path;
        $data['nombre_original'] = $file->getClientOriginalName();
        $data['mime']            = $file->getClientMimeType();
        $data['tamano']          = $file->getSize();

        $documento = Documento::create($data);

        // Redirección inteligente según el origen
        if ($documento->solicitud_id) {
            return redirect()
                ->route('solicitudes.show', $documento->solicitud_id)
                ->with('status', 'Documento subido correctamente.');
        }

        if ($documento->peticion_id) {
            return redirect()
                ->route('peticiones.show', $documento->peticion_id)
                ->with('status', 'Documento subido correctamente.');
        }

        if ($documento->pedido_id) {
            return redirect()
                ->route('pedidos.show', $documento->pedido_id)
                ->with('status', 'Documento subido correctamente.');
        }

        if ($documento->account_id) {
            return redirect()
                ->route('accounts.show', $documento->account_id)
                ->with('status', 'Documento subido correctamente.');
        }

        return redirect()
            ->route('documentos.index')
            ->with('status', 'Documento subido correctamente.');
    }

    public function download(Documento $documento)
    {
        if (! $documento->ruta || ! Storage::disk('public')->exists($documento->ruta)) {
            abort(404, 'El fichero no está disponible.');
        }

        return Storage::disk('public')->download(
            $documento->ruta,
            $documento->nombre_original ?: basename($documento->ruta)
        );
    }

    public function destroy(Documento $documento): RedirectResponse
    {
        $redirectTo = route('documentos.index');

        if ($documento->solicitud_id) {
            $redirectTo = route('solicitudes.show', $documento->solicitud_id);
        } elseif ($documento->peticion_id) {
            $redirectTo = route('peticiones.show', $documento->peticion_id);
        } elseif ($documento->pedido_id) {
            $redirectTo = route('pedidos.show', $documento->pedido_id);
        } elseif ($documento->account_id) {
            $redirectTo = route('accounts.show', $documento->account_id);
        }

        if ($documento->ruta) {
            Storage::disk('public')->delete($documento->ruta);
        }

        $documento->delete();

        return redirect($redirectTo)->with('status', 'Documento eliminado.');
    }
}
