@php
    $estados = ['activo' => 'Activo', 'inactivo' => 'Inactivo'];
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-slate-600">Referencia *</label>
        <input type="text" name="referencia" value="{{ old('referencia', $servicio->referencia ?? '') }}" required
               class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]">
        @error('referencia')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-600">Categoría</label>
        <select name="service_category_id" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]">
            <option value="">Sin categoría</option>
            @foreach ($categorias as $cat)
                <option value="{{ $cat->id }}" @selected(old('service_category_id', $servicio->service_category_id ?? null) == $cat->id)>
                    {{ $cat->nombre }}
                </option>
            @endforeach
        </select>
        @error('service_category_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-600">Precio *</label>
        <input type="number" step="0.01" min="0" name="precio" value="{{ old('precio', $servicio->precio ?? 0) }}" required
               class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]">
        @error('precio')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-600">Estado *</label>
        <select name="estado" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]">
            @foreach ($estados as $value => $label)
                <option value="{{ $value }}" @selected(old('estado', $servicio->estado ?? 'activo') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('estado')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-600">Descripción (SPA) *</label>
        <textarea name="descripcion" rows="4" required
                  class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]">{{ old('descripcion', $servicio->descripcion ?? '') }}</textarea>
        @error('descripcion')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-600">Notas internas</label>
        <textarea name="notas" rows="3"
                  class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]">{{ old('notas', $servicio->notas ?? '') }}</textarea>
        @error('notas')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
</div>