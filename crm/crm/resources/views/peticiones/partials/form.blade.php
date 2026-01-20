<div class="grid gap-6 md:grid-cols-2">
    <div class="grid gap-3 md:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-slate-600">Código</label>
            <input
                type="text"
                name="codigo"
                value="{{ old('codigo', optional($peticion)->codigo) }}"
                placeholder="{{ now()->year }}00001"
                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
            >
            @error('codigo')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-600">Año</label>
            <input
                type="number"
                name="anio"
                value="{{ old('anio', optional($peticion)->anio ?? now()->year) }}"
                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
            >
            @error('anio')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="grid gap-3 md:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-slate-600">Fecha de alta</label>
            <input
                type="date"
                name="fecha_alta"
                value="{{ old('fecha_alta', optional(optional($peticion)->fecha_alta)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
            >
            @error('fecha_alta')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-600">Fecha límite de oferta</label>
            <input
                type="date"
                name="fecha_limite_oferta"
                value="{{ old('fecha_limite_oferta', optional(optional($peticion)->fecha_limite_oferta)->format('Y-m-d')) }}"
                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
            >
            @error('fecha_limite_oferta')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-600">Cuenta</label>
        <select
            name="account_id"
            class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
        >
            <option value="">Sin cuenta</option>
            @foreach ($cuentas as $cuenta)
                <option value="{{ $cuenta->id }}"
                    {{ (int) old('account_id', optional($peticion)->account_id ?? optional($solicitud)->account_id) === $cuenta->id ? 'selected' : '' }}>
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
                @php
                    $nombreContacto = trim(($contacto->first_name ?? '') . ' ' . ($contacto->last_name ?? ''));
                    if ($nombreContacto === '') {
                        $nombreContacto = $contacto->email ?? 'Sin nombre';
                    }
                @endphp
                <option value="{{ $contacto->id }}"
                    {{ (int) old('contact_id', optional($peticion)->contact_id ?? optional($solicitud)->contact_id) === $contacto->id ? 'selected' : '' }}>
                    {{ $nombreContacto }}
                </option>
            @endforeach
        </select>
        @error('contact_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-600">Depto. comercial</label>
        <select
            name="owner_user_id"
            class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
        >
            <option value="">Sin asignar</option>
            @foreach($usuarios as $usuario)
                <option value="{{ $usuario->id }}" {{ (int) old('owner_user_id', optional($peticion)->owner_user_id) === $usuario->id ? 'selected' : '' }}>
                    {{ $usuario->name }}
                </option>
            @endforeach
        </select>
        @error('owner_user_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-600">Creado por</label>
        <select
            name="created_by_user_id"
            class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
        >
            <option value="">No especificado</option>
            @foreach($usuarios as $usuario)
                <option value="{{ $usuario->id }}" {{ (int) old('created_by_user_id', optional($peticion)->created_by_user_id) === $usuario->id ? 'selected' : '' }}>
                    {{ $usuario->name }}
                </option>
            @endforeach
        </select>
        @error('created_by_user_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-600">Título de la petición</label>
        <input
            type="text"
            name="titulo"
            value="{{ old('titulo', optional($peticion)->titulo ?? optional($solicitud)->asunto) }}"
            class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
            required
        >
        @error('titulo')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-600">Descripción</label>
        <textarea
            name="descripcion"
            rows="3"
            class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
        >{{ old('descripcion', optional($peticion)->descripcion ?? optional($solicitud)->descripcion) }}</textarea>
        @error('descripcion')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="grid gap-3 md:grid-cols-3 md:col-span-2">
        <div>
            <label class="block text-sm font-medium text-slate-600">Subvención</label>
            <select
                name="subvencion_id"
                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
            >
                <option value="">Selecciona una opción</option>
                @foreach($subvenciones as $id => $nombre)
                    <option value="{{ $id }}" {{ (string) old('subvencion_id', optional($peticion)->subvencion_id) === (string) $id ? 'selected' : '' }}>
                        {{ $nombre }}
                    </option>
                @endforeach
            </select>
            @error('subvencion_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-600">Tipo de proyecto</label>
            <select
                name="tipo_proyecto"
                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
            >
                <option value="">Sin definir</option>
                @foreach($tiposProyecto as $clave => $label)
                    <option value="{{ $clave }}" {{ old('tipo_proyecto', optional($peticion)->tipo_proyecto) === $clave ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('tipo_proyecto')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-600">Gasto subcontratado</label>
            <input
                type="text"
                name="gasto_subcontratado"
                value="{{ old('gasto_subcontratado', optional($peticion)->gasto_subcontratado) }}"
                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
            >
            @error('gasto_subcontratado')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="flex items-center gap-3">
        <label class="flex items-center gap-2 text-sm text-slate-700">
            <input type="hidden" name="memoria" value="0">
            <input type="checkbox" name="memoria" value="1" class="rounded border-slate-300 text-[#9d1872] focus:ring-[#9d1872]" {{ old('memoria', optional($peticion)->memoria) ? 'checked' : '' }}>
            Memoria presentada
        </label>
        @error('memoria')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="md:col-span-2 grid gap-4 md:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-slate-600">Información del cliente</label>
            <textarea
                name="info_cliente"
                rows="3"
                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
            >{{ old('info_cliente', optional($peticion)->info_cliente) }}</textarea>
            @error('info_cliente')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-600">Información de facturación</label>
            <textarea
                name="info_facturacion"
                rows="3"
                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
            >{{ old('info_facturacion', optional($peticion)->info_facturacion) }}</textarea>
            @error('info_facturacion')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="md:col-span-2 grid gap-4 md:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-slate-600">Información adicional</label>
            <textarea
                name="info_adicional"
                rows="3"
                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
            >{{ old('info_adicional', optional($peticion)->info_adicional) }}</textarea>
            @error('info_adicional')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-600">Comentarios</label>
            <textarea
                name="comentarios"
                rows="3"
                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
            >{{ old('comentarios', optional($peticion)->comentarios) }}</textarea>
            @error('comentarios')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="grid gap-3 md:grid-cols-3 md:col-span-2">
        <div>
            <label class="block text-sm font-medium text-slate-600">Importe total</label>
            <input
                type="number"
                step="0.01"
                min="0"
                name="importe_total"
                value="{{ old('importe_total', optional($peticion)->importe_total) }}"
                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
            >
            @error('importe_total')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-600">Moneda</label>
            <input
                type="text"
                name="moneda"
                value="{{ old('moneda', optional($peticion)->moneda ?? 'EUR') }}"
                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
            >
            @error('moneda')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-600">Estado</label>
            @php
                $estadoActual = old('estado', optional($peticion)->estado ?? 'borrador');
            @endphp
            <select
                name="estado"
                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                required
            >
                <option value="borrador"   {{ $estadoActual === 'borrador' ? 'selected' : '' }}>Borrador</option>
                <option value="enviada"    {{ $estadoActual === 'enviada' ? 'selected' : '' }}>Enviada</option>
                <option value="aceptada"   {{ $estadoActual === 'aceptada' ? 'selected' : '' }}>Aceptada</option>
                <option value="rechazada"  {{ $estadoActual === 'rechazada' ? 'selected' : '' }}>Rechazada</option>
                <option value="cancelada"  {{ $estadoActual === 'cancelada' ? 'selected' : '' }}>Cancelada</option>
            </select>
            @error('estado')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="grid gap-3 md:grid-cols-2 md:col-span-2">
        <div>
            <label class="block text-sm font-medium text-slate-600">Fecha envío</label>
            <input
                type="datetime-local"
                name="fecha_envio"
                value="{{ old('fecha_envio', optional(optional($peticion)->fecha_envio)->format('Y-m-d\TH:i')) }}"
                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
            >
            @error('fecha_envio')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-600">Fecha respuesta</label>
            <input
                type="datetime-local"
                name="fecha_respuesta"
                value="{{ old('fecha_respuesta', optional(optional($peticion)->fecha_respuesta)->format('Y-m-d\TH:i')) }}"
                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
            >
            @error('fecha_respuesta')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>