<?php

namespace App\Http\Controllers;

use App\Models\Peticion;
use App\Models\PeticionLinea;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PeticionLineaController extends Controller
{
    public function store(Request $request, Peticion $peticion): RedirectResponse
    {
        $data = $request->validate([
            'service_id'            => ['nullable', 'integer', 'exists:services,id'],
            'concepto'              => ['required_without:service_id', 'string', 'max:255'],
            'descripcion'           => ['nullable', 'string'],
            'cantidad'              => ['required', 'numeric', 'min:0'],
            'precio_unitario'       => ['required_without:service_id', 'numeric', 'min:0'],
            'descuento_porcentaje'  => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $service = isset($data['service_id']) ? Service::find($data['service_id']) : null;
        $cantidad    = (float) $data['cantidad'];
        $precio      = $service ? (float) $service->precio : (float) $data['precio_unitario'];
        $descuento   = isset($data['descuento_porcentaje'])
            ? (float) $data['descuento_porcentaje']
            : 0.0;

        $importe = $cantidad * $precio * (1 - ($descuento / 100));

        $linea = new PeticionLinea();
        $linea->peticion_id           = $peticion->id;
        $linea->service_id            = $service?->id;
        $linea->concepto              = $service?->referencia ?? $data['concepto'];
        $linea->descripcion           = $data['descripcion'] ?? $service?->descripcion;
        $linea->cantidad              = $cantidad;
        $linea->precio_unitario       = $precio;
        $linea->descuento_porcentaje  = $descuento;
        $linea->importe_total         = $importe;
        $linea->save();

        $this->recalcularImporteTotal($peticion);

        return redirect()
            ->route('peticiones.show', $peticion)
            ->with('status', 'Línea añadida correctamente.');
    }

    public function destroy(Peticion $peticion, PeticionLinea $linea): RedirectResponse
    {
        // Seguridad: aseguramos que la línea pertenece a la petición
        if ($linea->peticion_id !== $peticion->id) {
            abort(404);
        }

        $linea->delete();

        $this->recalcularImporteTotal($peticion);

        return redirect()
            ->route('peticiones.show', $peticion)
            ->with('status', 'Línea eliminada correctamente.');
    }

    private function recalcularImporteTotal(Peticion $peticion): void
    {
        $total = $peticion->lineas()->sum('importe_total');

        $peticion->importe_total = $total ?: 0;
        $peticion->save();
    }
}
