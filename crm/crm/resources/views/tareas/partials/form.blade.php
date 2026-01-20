@php
    $tarea   = $tarea ?? null;
    $prefill = $prefill ?? [];
@endphp

<div class="space-y-6">
    {{-- Datos básicos --}}
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
            Datos de la tarea
        </h2>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700">Tipo</label>
                <select
                    name="tipo"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    @foreach (['tarea' => 'Tarea', 'llamada' => 'Llamada', 'reunion' => 'Reunión', 'email' => 'Email'] as $value => $label)
                        <option value="{{ $value }}"
                            {{ old('tipo', $tarea->tipo ?? 'tarea') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('tipo')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Estado</label>
                <select
                    name="estado"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    @foreach (['pendiente' => 'Pendiente', 'en_progreso' => 'En progreso', 'completada' => 'Completada', 'cancelada' => 'Cancelada'] as $value => $label)
                        <option value="{{ $value }}"
                            {{ old('estado', $tarea->estado ?? 'pendiente') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('estado')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700">Título</label>
                <input
                    type="text"
                    name="titulo"
                    value="{{ old('titulo', $tarea->titulo ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    required
                >
                @error('titulo')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700">Descripción</label>
                <textarea
                    name="descripcion"
                    rows="3"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >{{ old('descripcion', $tarea->descripcion ?? '') }}</textarea>
                @error('descripcion')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    {{-- Fechas y propietario --}}
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
            Planificación
        </h2>

        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="block text-sm font-medium text-slate-700">Fecha inicio</label>
                <input
                    type="datetime-local"
                    name="fecha_inicio"
                    value="{{ old('fecha_inicio', optional($tarea->fecha_inicio ?? null)->format('Y-m-d\TH:i')) }}"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('fecha_inicio')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Fecha vencimiento</label>
                <input
                    type="datetime-local"
                    name="fecha_vencimiento"
                    value="{{ old('fecha_vencimiento', optional($tarea->fecha_vencimiento ?? null)->format('Y-m-d\TH:i')) }}"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('fecha_vencimiento')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Comercial responsable</label>
                <select
                    name="owner_user_id"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    <option value="">(Usuario actual o sin asignar)</option>
                    @foreach ($users as $user)
                        <option
                            value="{{ $user->id }}"
                            {{ (int) old('owner_user_id', $tarea->owner_user_id ?? '') === $user->id ? 'selected' : '' }}
                        >
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
                @error('owner_user_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    {{-- Vinculaciones --}}
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
            Relacionar con módulos
        </h2>

        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="block text-sm font-medium text-slate-700">Cuenta</label>
                <select
                    name="account_id"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    <option value="">(Sin cuenta)</option>
                    @foreach ($accounts as $account)
                        <option
                            value="{{ $account->id }}"
                            {{ (int) old('account_id', $prefill['account_id'] ?? $tarea->account_id ?? '') === $account->id ? 'selected' : '' }}
                        >
                            {{ $account->name }}
                        </option>
                    @endforeach
                </select>
                @error('account_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Contacto</label>
                <select
                    name="contact_id"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    <option value="">(Sin contacto)</option>
                    @foreach ($contacts as $contact)
                        @php
                            $displayName = $contact->name
                                ?? trim(($contact->first_name ?? '').' '.($contact->last_name ?? ''))
                                ?: 'Contacto #'.$contact->id;
                        @endphp
                        <option
                            value="{{ $contact->id }}"
                            {{ (int) old('contact_id', $prefill['contact_id'] ?? $tarea->contact_id ?? '') === $contact->id ? 'selected' : '' }}
                        >
                            {{ $displayName }}
                        </option>
                    @endforeach
                </select>
                @error('contact_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Vínculos ocultos para solicitudes / peticiones / pedidos (vienen por query) --}}
        <input type="hidden" name="solicitud_id" value="{{ old('solicitud_id', $prefill['solicitud_id'] ?? $tarea->solicitud_id ?? '') }}">
        <input type="hidden" name="peticion_id"  value="{{ old('peticion_id',  $prefill['peticion_id']  ?? $tarea->peticion_id  ?? '') }}">
        <input type="hidden" name="pedido_id"    value="{{ old('pedido_id',    $prefill['pedido_id']    ?? $tarea->pedido_id    ?? '') }}">
    </section>
</div>
