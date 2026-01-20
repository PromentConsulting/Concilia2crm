<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $busqueda     = trim((string) $request->query('q', '')) ?: null;
        $categoriaId  = $request->integer('categoria') ?: null;

        $servicios = Service::query()
            ->with(['category', 'owner'])
            ->when($busqueda, function ($q) use ($busqueda) {
                $q->where(function ($sub) use ($busqueda) {
                    $sub->where('referencia', 'like', "%{$busqueda}%")
                        ->orWhere('descripcion', 'like', "%{$busqueda}%")
                        ->orWhere('notas', 'like', "%{$busqueda}%");
                });
            })
            ->when($categoriaId, fn ($q) => $q->where('service_category_id', $categoriaId))
            ->orderBy('referencia')
            ->paginate(25)
            ->withQueryString();

        $categoriasRaiz = ServiceCategory::with('children')
            ->whereNull('parent_id')
            ->orderBy('nombre')
            ->get();

        $todasCategorias = ServiceCategory::orderBy('nombre')->get();

        return view('catalogo.servicios.index', [
            'servicios'        => $servicios,
            'categorias'       => $categoriasRaiz,
            'todasCategorias'  => $todasCategorias,
            'filtros'          => [
                'q'         => $busqueda,
                'categoria' => $categoriaId,
            ],
        ]);
    }

    public function create(): View
    {
        $categorias = ServiceCategory::orderBy('nombre')->get();

        return view('catalogo.servicios.create', [
            'categorias' => $categorias,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'referencia'            => ['required', 'string', 'max:255', 'unique:services,referencia'],
            'descripcion'           => ['required', 'string'],
            'service_category_id'   => ['nullable', 'exists:service_categories,id'],
            'precio'                => ['required', 'numeric', 'min:0'],
            'notas'                 => ['nullable', 'string'],
            'estado'                => ['required', 'string', 'max:50'],
        ]);

        $data['owner_id'] = Auth::id();

        Service::create($data);

        return redirect()
            ->route('catalogo.servicios.index')
            ->with('status', 'Servicio creado correctamente.');
    }

    public function edit(Service $servicio): View
    {
        $categorias = ServiceCategory::orderBy('nombre')->get();

        return view('catalogo.servicios.edit', [
            'servicio'   => $servicio,
            'categorias' => $categorias,
        ]);
    }

    public function update(Request $request, Service $servicio): RedirectResponse
    {
        $data = $request->validate([
            'referencia'            => ['required', 'string', 'max:255', 'unique:services,referencia,' . $servicio->id],
            'descripcion'           => ['required', 'string'],
            'service_category_id'   => ['nullable', 'exists:service_categories,id'],
            'precio'                => ['required', 'numeric', 'min:0'],
            'notas'                 => ['nullable', 'string'],
            'estado'                => ['required', 'string', 'max:50'],
        ]);

        $servicio->update($data);

        return redirect()
            ->route('catalogo.servicios.index')
            ->with('status', 'Servicio actualizado correctamente.');
    }

    public function destroy(Service $servicio): RedirectResponse
    {
        $servicio->delete();

        return redirect()
            ->route('catalogo.servicios.index')
            ->with('status', 'Servicio eliminado.');
    }
}