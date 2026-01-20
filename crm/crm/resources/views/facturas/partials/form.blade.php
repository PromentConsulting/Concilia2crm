<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-4 lg:col-span-2">
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-600">Nº Factura</label>
                <input
                    type="text"
                    name="numero"
                    value="{{ old('numero', $factura->numero ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-600">Fecha factura</label>
                <input
                    type="date"
                    name="fecha_factura"
                    value="{{ old('fecha_factura', optional($factura->fecha_factura)->format('Y-m-d')) }}"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-600">Descripción</label>
                <input
                    type="text"
                    name="descripcion"
                    value="{{ old('descripcion', $factura->descripcion ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-600">Cliente</label>
                <select
                    name="account_id"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    <option value="">Sin cliente</option>
                    @foreach ($cuentas as $cuenta)
                        <option value="{{ $cuenta->id }}" @selected(old('account_id', $factura->account_id ?? null) == $cuenta->id)>
                            {{ $cuenta->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-600">Pedido</label>
                <select
                    name="pedido_id"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    <option value="">Sin pedido</option>
                    @foreach ($pedidos as $pedido)
                        <option value="{{ $pedido->id }}" @selected(old('pedido_id', $factura->pedido_id ?? null) == $pedido->id)>
                            {{ $pedido->numero ?: 'Pedido '.$pedido->id }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-600">Fecha vencimiento</label>
                <input
                    type="date"
                    name="fecha_vencimiento"
                    value="{{ old('fecha_vencimiento', optional($factura->fecha_vencimiento)->format('Y-m-d')) }}"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-600">Fecha cobro</label>
                <input
                    type="date"
                    name="fecha_cobro"
                    value="{{ old('fecha_cobro', optional($factura->fecha_cobro)->format('Y-m-d')) }}"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-600">Forma de pago</label>
                <input
                    type="text"
                    name="forma_pago"
                    value="{{ old('forma_pago', $factura->forma_pago ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-600">Instrucción de pago</label>
                <input
                    type="text"
                    name="instruccion_pago"
                    value="{{ old('instruccion_pago', $factura->instruccion_pago ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-600">Dpto. Comercial</label>
                <input
                    type="text"
                    name="dpto_comercial"
                    value="{{ old('dpto_comercial', $factura->dpto_comercial ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-600">Email de facturación</label>
                <input
                    type="email"
                    name="email_facturacion"
                    value="{{ old('email_facturacion', $factura->email_facturacion ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
            </div>
            <div class="flex items-center gap-2">
                <input
                    type="checkbox"
                    name="agrupar_referencias"
                    value="1"
                    @checked(old('agrupar_referencias', $factura->agrupar_referencias ?? false))
                    class="h-4 w-4 rounded border-slate-300 text-[#9d1872] focus:ring-[#9d1872]"
                >
                <span class="text-sm text-slate-700">Agrupar referencias</span>
            </div>
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        name="cobrado"
                        value="1"
                        @checked(old('cobrado', $factura->cobrado ?? false))
                        class="h-4 w-4 rounded border-slate-300 text-[#9d1872] focus:ring-[#9d1872]"
                    >
                    <span class="text-sm text-slate-700">Cobrado</span>
                </label>
                <label class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        name="contabilizado"
                        value="1"
                        @checked(old('contabilizado', $factura->contabilizado ?? false))
                        class="h-4 w-4 rounded border-slate-300 text-[#9d1872] focus:ring-[#9d1872]"
                    >
                    <span class="text-sm text-slate-700">Contabilizado</span>
                </label>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-600">Información adicional</label>
            <textarea
                name="info_adicional"
                rows="3"
                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
            >{{ old('info_adicional', $factura->info_adicional ?? '') }}</textarea>
        </div>
    </div>

    <div class="space-y-4">
        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Totales</h3>
            <div class="space-y-3 text-sm">
                <div>
                    <label class="block text-sm font-medium text-slate-600">Importe</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="importe"
                        value="{{ old('importe', $factura->importe ?? '') }}"
                        class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600">Importe total</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="importe_total"
                        value="{{ old('importe_total', $factura->importe_total ?? '') }}"
                        class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600">Moneda</label>
                    <input
                        type="text"
                        name="moneda"
                        value="{{ old('moneda', $factura->moneda ?? 'EUR') }}"
                        class="mt-1 w-20 rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    >
                </div>
            </div>
        </div>
    </div>
</div>