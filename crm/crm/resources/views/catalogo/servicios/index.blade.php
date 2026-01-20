@extends('layouts.app')

@section('title', 'Catálogo de servicios')

@php use Illuminate\Support\Str; @endphp

@section('content')
<div class="space-y-6">
    <header class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Catálogo de servicios</h1>
            <p class="text-sm text-slate-500">Gestiona las categorías y los servicios disponibles para peticiones y pedidos.</p>
        </div>

        <a
            href="{{ route('catalogo.servicios.create') }}"
            class="inline-flex items-center rounded-lg bg-[#9d1872] px-3 py-2 text-xs font-semibold text-white shadow hover:bg-[#86145f]"
        >
            + Nuevo servicio
        </a>
    </header>

    @if (session('status'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[320px,1fr]">
        {{-- Categorías --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Categorías</h2>
            </div>

            <form method="POST" action="{{ route('catalogo.categorias.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-600">Nombre</label>
                    <input type="text" name="nombre" required
                           class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]">
                    @error('nombre')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600">Depende de</label>
                    <select name="parent_id" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]">
                        <option value="">Sin categoría superior</option>
                        @foreach ($todasCategorias as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">
                    Guardar categoría
                </button>
            </form>

            <div class="space-y-2">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Árbol de categorías</h3>

                @php
                    $renderTree = function ($categorias, $nivel = 0) use (&$renderTree) {
                        echo '<ul class="space-y-1">';
                        foreach ($categorias as $cat) {
                            echo '<li class="flex items-center justify-between gap-2">';
                                echo '<div class="flex items-center gap-2">';
                                    echo '<span class="text-sm text-slate-800">' . e($cat->nombre) . '</span>';
                                echo '</div>';
                                echo '<form method="POST" action="' . route('catalogo.categorias.destroy', $cat) . '" onsubmit="return confirm(\'¿Eliminar categoría?\');" class="text-xs">';
                                    echo csrf_field() . method_field('DELETE');
                                    echo '<button type="submit" class="text-rose-600 hover:underline">Eliminar</button>';
                                echo '</form>';
                            echo '</li>';

                            if ($cat->children && $cat->children->count()) {
                                echo '<li class="ml-4">';
                                    $renderTree($cat->children, $nivel + 1);
                                echo '</li>';
                            }
                        }
                        echo '</ul>';
                    };
                @endphp

                @if($categorias->isEmpty())
                    <p class="text-sm text-slate-500">Aún no hay categorías creadas.</p>
                @else
                    {!! $renderTree($categorias) !!}
                @endif
            </div>
        </section>

        {{-- Servicios --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Servicios</h2>

                <form method="GET" action="{{ route('catalogo.servicios.index') }}" class="flex flex-wrap items-center gap-2 text-sm">
                    <input type="text" name="q" value="{{ $filtros['q'] }}" placeholder="Buscar" class="w-48 rounded-lg border border-slate-200 px-3 py-2 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]">
                    <select name="categoria" class="rounded-lg border border-slate-200 px-3 py-2 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]">
                        <option value="">Todas las categorías</option>
                        @foreach ($todasCategorias as $cat)
                            <option value="{{ $cat->id }}" @selected($filtros['categoria'] === $cat->id)>{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Filtrar</button>
                </form>
            </div>

            <div class="overflow-x-auto rounded-lg border border-slate-100">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-2 text-left">Referencia</th>
                            <th class="px-3 py-2 text-left">Descripción</th>
                            <th class="px-3 py-2 text-left">Categoría</th>
                            <th class="px-3 py-2 text-right">Precio</th>
                            <th class="px-3 py-2 text-left">Estado</th>
                            <th class="px-3 py-2 text-left">Propietario</th>
                            <th class="px-3 py-2 text-right"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 bg-white">
                        @forelse ($servicios as $servicio)
                            <tr>
                                <td class="px-3 py-2 font-semibold text-slate-900">{{ $servicio->referencia }}</td>
                                <td class="px-3 py-2 text-slate-700">{{ Str::limit($servicio->descripcion, 80) }}</td>
                                <td class="px-3 py-2">{{ optional($servicio->category)->nombre ?: '—' }}</td>
                                <td class="px-3 py-2 text-right tabular-nums">{{ number_format($servicio->precio, 2, ',', '.') }} €</td>
                                <td class="px-3 py-2">
                                    <span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] text-slate-700">{{ ucfirst($servicio->estado) }}</span>
                                </td>
                                <td class="px-3 py-2 text-slate-700">{{ optional($servicio->owner)->name ?: '—' }}</td>
                                <td class="px-3 py-2 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('catalogo.servicios.edit', $servicio) }}" class="text-xs text-slate-700 hover:underline">Editar</a>
                                        <form method="POST" action="{{ route('catalogo.servicios.destroy', $servicio) }}" onsubmit="return confirm('¿Eliminar este servicio?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-rose-600 hover:underline">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-4 text-sm text-slate-500">No hay servicios registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $servicios->links() }}
        </section>
    </div>
</div>
@endsection