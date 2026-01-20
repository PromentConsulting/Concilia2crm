@php($solicitud = $solicitud ?? null)
@php($cuentas = $cuentas ?? collect())
@php($contactos = $contactos ?? collect())
@php($usuarios = $usuarios ?? collect())
@php($fEstados = [
    'pendiente_asignacion' => 'Pendiente asignación',
    'asignado' => 'Asignado',
    'en_curso' => 'En curso',
    'en_espera' => 'En espera',
    'ganado' => 'Ganado',
    'perdido' => 'Perdido',
])
@php($fOrigenes = ['web' => 'Web', 'mautic' => 'Mautic', 'manual' => 'Manual', 'importacion' => 'Importación', 'api' => 'API', 'otro' => 'Otro'])
@php($fPrioridades = ['baja' => 'Baja', 'media' => 'Media', 'alta' => 'Alta', 'urgente' => 'Urgente'])
<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-4 lg:col-span-2">
        {{-- CUENTA / CONTACTO / PROPIETARIO --}}
        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="block text-sm font-medium text-slate-600">Cuenta</label>
                <select
                    name="account_id"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    <option value="">Sin cuenta</option>
                    @foreach ($cuentas as $cuenta)
                        <option
                            value="{{ $cuenta->id }}"
                            {{ (int) old('account_id', optional($solicitud)->account_id) === $cuenta->id ? 'selected' : '' }}
                        >
                            {{ $cuenta->name }}
                        </option>
                    @endforeach
                </select>
                @error('account_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-600">Contacto</label>
                <select
                    name="contact_id"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    <option value="">Sin contacto</option>
                    @foreach ($contactos as $contacto)
                        <option
                            value="{{ $contacto->id }}"
                            {{ (int) old('contact_id', optional($solicitud)->contact_id) === $contacto->id ? 'selected' : '' }}
                        >
                            {{ trim($contacto->first_name . ' ' . $contacto->last_name) }}
                        </option>
                    @endforeach
                </select>
                @error('contact_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-600">Propietario / Comercial</label>
                <select
                    name="owner_user_id"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    <option value="">Sin asignar</option>
                    @foreach ($usuarios as $usuario)
                        <option
                            value="{{ $usuario->id }}"
                            {{ (int) old('owner_user_id', optional($solicitud)->owner_user_id) === $usuario->id ? 'selected' : '' }}
                        >
                            {{ $usuario->name }}
                        </option>
                    @endforeach
                </select>
                @error('owner_user_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- TÍTULO + DESCRIPCIÓN --}}
        <div>
            <label class="block text-sm font-medium text-slate-600">Título</label>
            <input
                type="text"
                name="titulo"
                value="{{ old('titulo', $solicitud->titulo ?? '') }}"
                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
            >
            @error('titulo')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-600">Descripción</label>
            <textarea
                name="descripcion"
                rows="5"
                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
            >{{ old('descripcion', $solicitud->descripcion ?? '') }}</textarea>
            @error('descripcion')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>

    {{-- COLUMNA DERECHA: METADATOS --}}
    <div class="space-y-4">
        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 space-y-3">
            <div>
                <label class="block text-xs font-medium text-slate-600">Estado</label>
                <select
                    name="estado"
                    class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    @foreach ($fEstados as $value => $label)
                        <option
                            value="{{ $value }}"
                            {{ old('estado', $solicitud->estado ?? 'nueva') === $value ? 'selected' : '' }}
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('estado')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600">Origen</label>
                <select
                    name="origen"
                    class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    @foreach ($fOrigenes as $value => $label)
                        <option
                            value="{{ $value }}"
                            {{ old('origen', $solicitud->origen ?? 'web') === $value ? 'selected' : '' }}
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('origen')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600">Prioridad</label>
                <select
                    name="prioridad"
                    class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    @foreach ($fPrioridades as $value => $label)
                        <option
                            value="{{ $value }}"
                            {{ old('prioridad', $solicitud->prioridad ?? 'media') === $value ? 'selected' : '' }}
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('prioridad')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 space-y-3">
            <div>
                <label class="block text-xs font-medium text-slate-600">Fecha solicitud</label>
                <input
                    type="date"
                    name="fecha_solicitud"
                    value="{{ old('fecha_solicitud', optional($solicitud->fecha_solicitud ?? null)->format('Y-m-d')) }}"
                    class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('fecha_solicitud')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600">Fecha prevista</label>
                <input
                    type="date"
                    name="fecha_prevista"
                    value="{{ old('fecha_prevista', optional($solicitud->fecha_prevista ?? null)->format('Y-m-d')) }}"
                    class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('fecha_prevista')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600">Fecha cierre</label>
                <input
                    type="date"
                    name="fecha_cierre"
                    value="{{ old('fecha_cierre', optional($solicitud->fecha_cierre ?? null)->format('Y-m-d')) }}"
                    class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('fecha_cierre')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600">Importe estimado</label>
                <div class="flex gap-2">
                    <input
                        type="number"
                        step="0.01"
                        name="importe_estimado"
                        value="{{ old('importe_estimado', $solicitud->importe_estimado ?? '') }}"
                        class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    >
                    <input
                        type="text"
                        name="moneda"
                        value="{{ old('moneda', $solicitud->moneda ?? 'EUR') }}"
                        class="mt-1 w-20 rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    >
                </div>
                @error('importe_estimado')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                @error('moneda')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>
</div>
