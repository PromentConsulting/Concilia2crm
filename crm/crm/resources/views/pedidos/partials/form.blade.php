<div class="grid gap-6 lg:grid-cols-3">
    {{-- COLUMNA IZQUIERDA: datos generales --}}
    <div class="space-y-4 lg:col-span-2">
        <div class="grid gap-4 md:grid-cols-2">
            {{-- Nº Pedido --}}
            <div>
                <label class="block text-sm font-medium text-slate-600">Nº Pedido</label>
                <input
                    type="text"
                    name="numero"
                    value="{{ old('numero', optional($pedido)->numero) }}"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('numero')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            {{-- Fecha de pedido --}}
            <div>
                <label class="block text-sm font-medium text-slate-600">Fecha de pedido</label>
                <input
                    type="date"
                    name="fecha_pedido"
                    value="{{ old('fecha_pedido', optional(optional($pedido)->fecha_pedido)->format('Y-m-d')) }}"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('fecha_pedido')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            {{-- Descripción --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-600">Descripción</label>
                <input
                    type="text"
                    name="descripcion"
                    value="{{ old('descripcion', optional($pedido)->descripcion ?? optional($peticion)->titulo) }}"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('descripcion')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            {{-- Cuenta --}}
            <div>
                <label class="block text-sm font-medium text-slate-600">Cuenta (cliente)</label>
                <select
                    name="account_id"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    <option value="">Sin cuenta</option>
                    @foreach ($cuentas as $cuenta)
                        <option value="{{ $cuenta->id }}"
                            {{ (int) old(
                                'account_id',
                                optional($pedido)->account_id ?? optional($peticion)->account_id
                            ) === $cuenta->id ? 'selected' : '' }}>
                            {{ $cuenta->name }}
                        </option>
                    @endforeach
                </select>
                @error('account_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            {{-- Contacto --}}
            <div>
                <label class="block text-sm font-medium text-slate-600">Contacto</label>
                <select
                    name="contact_id"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    <option value="">Sin contacto</option>
                    @foreach ($contactos as $contacto)
                        @php
                            $nombre = trim(($contacto->first_name ?? '') . ' ' . ($contacto->last_name ?? ''));
                            if ($nombre === '') {
                                $nombre = $contacto->email ?? 'Sin nombre';
                            }
                        @endphp
                        <option value="{{ $contacto->id }}"
                            {{ (int) old(
                                'contact_id',
                                optional($pedido)->contact_id ?? optional($peticion)->contact_id
                            ) === $contacto->id ? 'selected' : '' }}>
                            {{ $nombre }}
                        </option>
                    @endforeach
                </select>
                @error('contact_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            {{-- Estado pedido --}}
            <div>
                <label class="block text-sm font-medium text-slate-600">Estado del pedido</label>
                @php($estadoActual = old('estado_pedido', optional($pedido)->estado_pedido ?? 'pendiente'))
                <select
                    name="estado_pedido"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    required
                >
                    <option value="pendiente"  {{ $estadoActual === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="confirmado" {{ $estadoActual === 'confirmado' ? 'selected' : '' }}>Confirmado</option>
                    <option value="finalizado" {{ $estadoActual === 'finalizado' ? 'selected' : '' }}>Finalizado</option>
                    <option value="borrador"   {{ $estadoActual === 'borrador' ? 'selected' : '' }}>Borrador</option>
                </select>
                @error('estado_pedido')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            {{-- Año --}}
            <div>
                <label class="block text-sm font-medium text-slate-600">Año</label>
                <input
                    type="number"
                    name="anio"
                    value="{{ old('anio', optional($pedido)->anio ?? now()->year) }}"
                    class="mt-1 w-28 rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('anio')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            {{-- Forma de pago --}}
            <div>
                <label class="block text-sm font-medium text-slate-600">Forma de pago</label>
                <input
                    type="text"
                    name="forma_pago"
                    value="{{ old('forma_pago', optional($pedido)->forma_pago) }}"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('forma_pago')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            {{-- Checkboxes proyecto / formación --}}
            <div class="flex items-center gap-2">
                <input
                    type="checkbox"
                    name="proyecto_justificado"
                    value="1"
                    {{ old('proyecto_justificado', optional($pedido)->proyecto_justificado) ? 'checked' : '' }}
                    class="h-4 w-4 rounded border-slate-300 text-[#9d1872] focus:ring-[#9d1872]"
                >
                <span class="text-sm text-slate-700">Proyecto justificado</span>
            </div>

            <div class="flex items-center gap-2">
                <input
                    type="checkbox"
                    name="es_formacion"
                    value="1"
                    {{ old('es_formacion', optional($pedido)->es_formacion) ? 'checked' : '' }}
                    class="h-4 w-4 rounded border-slate-300 text-[#9d1872] focus:ring-[#9d1872]"
                >
                <span class="text-sm text-slate-700">Pedido de formación</span>
            </div>
        </div>

        {{-- Bloque fechas / proyecto / facturación --}}
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-600">Fecha límite memoria</label>
                <input
                    type="date"
                    name="fecha_limite_memoria"
                    value="{{ old('fecha_limite_memoria', optional(optional($pedido)->fecha_limite_memoria)->format('Y-m-d')) }}"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-600">Fecha límite proyecto</label>
                <input
                    type="date"
                    name="fecha_limite_proyecto"
                    value="{{ old('fecha_limite_proyecto', optional(optional($pedido)->fecha_limite_proyecto)->format('Y-m-d')) }}"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-600">Dpto. Consultor</label>
                <input
                    type="text"
                    name="dpto_consultor"
                    value="{{ old('dpto_consultor', optional($pedido)->dpto_consultor) }}"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-600">Dpto. Comercial</label>
                <input
                    type="text"
                    name="dpto_comercial"
                    value="{{ old('dpto_comercial', optional($pedido)->dpto_comercial) }}"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-600">Estado de facturación</label>
                <input
                    type="text"
                    name="estado_facturacion"
                    value="{{ old('estado_facturacion', optional($pedido)->estado_facturacion) }}"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-600">Subvención</label>
                <input
                    type="text"
                    name="subvencion"
                    value="{{ old('subvencion', optional($pedido)->subvencion) }}"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-600">Gasto subcontratado</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="gasto_subcontratado"
                    value="{{ old('gasto_subcontratado', optional($pedido)->gasto_subcontratado) }}"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-600">Proyecto externo</label>
                <input
                    type="text"
                    name="proyecto_externo"
                    value="{{ old('proyecto_externo', optional($pedido)->proyecto_externo) }}"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
            </div>
        </div>
    </div>

    {{-- COLUMNA DERECHA: totales y facturación --}}
    <div class="space-y-4">
        {{-- Totales --}}
        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-3">
                Totales
            </h3>
            <div class="space-y-3 text-sm">
                <div>
                    <label class="block text-sm font-medium text-slate-600">Importe total</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="importe_total"
                        value="{{ old('importe_total', optional($pedido)->importe_total) }}"
                        class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600">Moneda</label>
                    <input
                        type="text"
                        name="moneda"
                        value="{{ old('moneda', optional($pedido)->moneda ?? 'EUR') }}"
                        class="mt-1 w-20 rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    >
                </div>
                <div class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        name="mostrar_precios"
                        value="1"
                        {{ old('mostrar_precios', optional($pedido)->mostrar_precios ?? true) ? 'checked' : '' }}
                        class="h-4 w-4 rounded border-slate-300 text-[#9d1872] focus:ring-[#9d1872]"
                    >
                    <span>Mostrar precios en documentos</span>
                </div>
            </div>
        </div>

        {{-- Facturación --}}
        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-3">
                Facturación
            </h3>
            <div class="space-y-3 text-sm">
                <div>
                    <label class="block text-sm font-medium text-slate-600">Email de facturación</label>
                    <input
                        type="email"
                        name="email_facturacion"
                        value="{{ old('email_facturacion', optional($pedido)->email_facturacion) }}"
                        class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600">Información de facturación</label>
                    <textarea
                        name="info_facturacion"
                        rows="3"
                        class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    >{{ old('info_facturacion', optional($pedido)->info_facturacion) }}</textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        name="facturar_primer_plazo"
                        value="1"
                        {{ old('facturar_primer_plazo', optional($pedido)->facturar_primer_plazo) ? 'checked' : '' }}
                        class="h-4 w-4 rounded border-slate-300 text-[#9d1872] focus:ring-[#9d1872]"
                    >
                    <span>Facturar primer plazo</span>
                </div>
                <div class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        name="facturar_segundo_plazo"
                        value="1"
                        {{ old('facturar_segundo_plazo', optional($pedido)->facturar_segundo_plazo) ? 'checked' : '' }}
                        class="h-4 w-4 rounded border-slate-300 text-[#9d1872] focus:ring-[#9d1872]"
                    >
                    <span>Facturar segundo plazo</span>
                </div>
            </div>
        </div>

        {{-- Información adicional --}}
        <div>
            <label class="block text-sm font-medium text-slate-600">Información adicional</label>
            <textarea
                name="info_adicional"
                rows="3"
                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
            >{{ old('info_adicional', optional($pedido)->info_adicional) }}</textarea>
        </div>
    </div>
</div>
