<?php

namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre'     => ['required', 'string', 'max:255'],
            'parent_id'  => ['nullable', 'exists:service_categories,id'],
        ]);

        ServiceCategory::create($data);

        return redirect()
            ->back()
            ->with('status', 'Categoría guardada correctamente.');
    }

    public function destroy(ServiceCategory $categoria): RedirectResponse
    {
        $categoria->delete();

        return redirect()
            ->back()
            ->with('status', 'Categoría eliminada.');
    }
}