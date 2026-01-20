@php
    $init = old('lineas');

    if (!$init && isset($lineasPreset) && !empty($lineasPreset)) {
        $init = $lineasPreset;
    }

    if (!$init && isset($factura) && $factura) {
        $init = $factura->lineas->map(function ($l) {
            return [
                'service_id' => $l->service_id,
                'referencia' => $l->referencia,
                'concepto' => $l->concepto,
                'cantidad' => (float) $l->cantidad,
                'precio' => (float) $l->precio,
                'descuento_porcentaje' => (float) $l->descuento_porcentaje,
                'iva_porcentaje' => (float) $l->iva_porcentaje,
            ];
        })->values()->toArray();
    }

    if (!$init) {
        $init = [[
            'service_id' => null,
            'referencia' => '',
            'concepto' => '',
            'cantidad' => 1,
            'precio' => 0,
            'descuento_porcentaje' => 0,
            'iva_porcentaje' => 21,
        ]];
    }

    $servicesList = ($services ?? collect());
    $categoriesList = ($categories ?? collect());
    $categoriesByParent = $categoriesList->groupBy('parent_id');

    $renderCategoryTree = function ($parentId) use (&$renderCategoryTree, $categoriesByParent) {
        $children = $categoriesByParent->get($parentId, collect());
        if ($children->isEmpty()) {
            return '';
        }

        $html = '<ul class="space-y-2">';
        foreach ($children as $categoria) {
            $tieneHijos = $categoriesByParent->get($categoria->id, collect())->isNotEmpty();
            $html .= '<li class="space-y-2">';
            $html .= '<div class="flex items-center gap-2">';
            if ($tieneHijos) {
                $html .= '<button type="button" class="flex h-6 w-6 items-center justify-center rounded border border-slate-200 text-xs text-slate-500 hover:border-[#9d1872] hover:text-[#9d1872]" @click="toggleCategoria(' . $categoria->id . ')">';
                $html .= '<span x-text="categoriaAbierta(' . $categoria->id . ') ? \'−\' : \'+\'"></span>';
                $html .= '</button>';
            } else {
                $html .= '<span class="flex h-6 w-6 items-center justify-center text-xs text-slate-300">•</span>';
            }
            $html .= '<button type="button" class="text-left text-sm font-semibold text-slate-700 hover:text-[#9d1872]" :class="categoriaSeleccionada === ' . $categoria->id . ' ? \'text-[#9d1872]\' : \'\'" @click="selectCategoria(' . $categoria->id . ')">';
            $html .= e($categoria->nombre);
            $html .= '</button>';
            $html .= '</div>';
            if ($tieneHijos) {
                $html .= '<div class="pl-6" x-show="categoriaAbierta(' . $categoria->id . ')" x-cloak>';
                $html .= $renderCategoryTree($categoria->id);
                $html .= '</div>';
            }
            $html .= '</li>';
        }
        $html .= '</ul>';

        return $html;
    };
@endphp

<section
    x-data="{
        lineas: @js($init),
        serviciosCatalogo: @js($servicesList->map(fn($s) => [
            'id' => $s->id,
            'referencia' => $s->referencia,
            'descripcion' => $s->descripcion,
            'precio' => (float) $s->precio,
            'categoria_id' => $s->service_category_id,
            'categoria' => optional($s->category)->nombre,
        ])->values()),
        categoriasCatalogo: @js($categoriesList->map(fn($c) => [
            'id' => $c->id,
            'nombre' => $c->nombre,
            'parent_id' => $c->parent_id,
        ])->values()),
        modalServiciosAbierto: false,
        busquedaServicios: '',
        serviciosSeleccionados: [],
        categoriaSeleccionada: null,
        categoriasAbiertas: [],
        categoriaDescendientesCache: {},
        add() {
            this.lineas.push({ service_id:null, referencia:'', concepto:'', cantidad:1, precio:0, descuento_porcentaje:0, iva_porcentaje:21 })
        },
        openServiciosModal() {
            this.modalServiciosAbierto = true;
            this.busquedaServicios = '';
            this.serviciosSeleccionados = [];
        },
        closeServiciosModal() {
            this.modalServiciosAbierto = false;
            this.busquedaServicios = '';
            this.serviciosSeleccionados = [];
            this.categoriaSeleccionada = null;
        },
        toggleCategoria(id) {
            if (this.categoriasAbiertas.includes(id)) {
                this.categoriasAbiertas = this.categoriasAbiertas.filter(catId => catId !== id);
                return;
            }
            this.categoriasAbiertas.push(id);
        },
        categoriaAbierta(id) {
            return this.categoriasAbiertas.includes(id);
        },
        selectCategoria(id) {
            this.categoriaSeleccionada = id;
            this.categoriasAbiertas = [...new Set([...this.categoriasAbiertas, id])];
        },
        clearCategoria() {
            this.categoriaSeleccionada = null;
        },
        getDescendientesCategoria(id) {
            if (this.categoriaDescendientesCache[id]) {
                return this.categoriaDescendientesCache[id];
            }
            const hijos = this.categoriasCatalogo.filter(cat => Number(cat.parent_id) === Number(id));
            const descendientes = hijos.flatMap(hijo => [
                hijo.id,
                ...this.getDescendientesCategoria(hijo.id),
            ]);
            const resultado = [Number(id), ...descendientes];
            this.categoriaDescendientesCache[id] = resultado;
            return resultado;
        },
        serviciosFiltrados() {
            const term = this.busquedaServicios.trim().toLowerCase();
            const categoriaIds = this.categoriaSeleccionada
                ? this.getDescendientesCategoria(this.categoriaSeleccionada)
                : null;
            return this.serviciosCatalogo.filter(servicio => {
                if (categoriaIds && !categoriaIds.includes(Number(servicio.categoria_id))) {
                    return false;
                }
                if (!term) return true;
                const texto = `${servicio.referencia || ''} ${servicio.descripcion || ''} ${servicio.categoria || ''}`.toLowerCase();
                return texto.includes(term);
            });
        },
        gruposServicios() {
            const grupos = {};
            this.serviciosFiltrados().forEach(servicio => {
                const nombre = servicio.categoria || 'Sin categoría';
                if (!grupos[nombre]) {
                    grupos[nombre] = [];
                }
                grupos[nombre].push(servicio);
            });
            return Object.entries(grupos).map(([nombre, servicios]) => ({
                nombre,
                servicios,
            }));
        },
        addSeleccionadosDesdeCatalogo() {
            const servicios = this.serviciosCatalogo.filter(servicio =>
                this.serviciosSeleccionados.includes(String(servicio.id))
            );
            if (!servicios.length) {
                this.closeServiciosModal();
                return;
            }
            servicios.forEach(servicio => {
                this.lineas.push({
                    service_id: servicio.id,
                    referencia: servicio.referencia || '',
                    concepto: servicio.descripcion || '',
                    cantidad: 1,
                    precio: Number(servicio.precio) || 0,
                    descuento_porcentaje: 0,
                    iva_porcentaje: 21,
                });
            });
            this.closeServiciosModal();
        },
        remove(i) { this.lineas.splice(i,1) },
        subTotal(l) {
            const c = Number(l.cantidad||0), p = Number(l.precio||0), d = Number(l.descuento_porcentaje||0);
            return c * p * (1 - (d/100));
        },
        totalLinea(l) {
            const st = this.subTotal(l), iva = Number(l.iva_porcentaje||0);
            return st * (1 + (iva/100));
        },
        totalFactura() {
            return this.lineas.reduce((acc, l) => acc + this.totalLinea(l), 0);
        },
        totalSubtotal() {
            return this.lineas.reduce((acc, l) => acc + this.subTotal(l), 0);
        },
    }"
    class="mt-6"
>
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-sm font-semibold text-slate-800">Detalle de productos/servicios</h3>
        <div class="flex flex-wrap items-center gap-2">
            <div x-show="serviciosCatalogo.length" class="flex items-center gap-2" x-cloak>
                <button type="button" @click="openServiciosModal()" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                    Añadir servicio
                </button>
            </div>
            <button type="button" @click="add()" class="inline-flex items-center rounded-lg bg-[#9d1872] px-3 py-1.5 text-xs font-semibold text-white hover:bg-[#86145f]">
                + Añadir línea
            </button>
        </div>
    </div>

    <div
        x-show="modalServiciosAbierto"
        x-cloak
        class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/40 px-4 py-6"
        @click.self="closeServiciosModal()"
        @keydown.escape.window="closeServiciosModal()"
    >
        <div class="w-full max-w-4xl rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <h4 class="text-base font-semibold text-slate-900">Añadir servicios del catálogo</h4>
                    <p class="text-sm text-slate-500">Selecciona uno o varios servicios para incorporarlos a la factura.</p>
                </div>
                <button type="button" class="rounded-full p-2 text-slate-500 hover:bg-slate-100" @click="closeServiciosModal()">
                    <span class="sr-only">Cerrar</span>
                    ✕
                </button>
            </div>
            <div class="px-6 py-4">
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Buscar servicios</label>
                <input
                    type="text"
                    x-model="busquedaServicios"
                    placeholder="Buscar por referencia, descripción o categoría"
                    class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
            </div>
            <div class="grid gap-6 px-6 pb-6 lg:grid-cols-[260px_1fr]">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h5 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Categorías</h5>
                        <button type="button" class="text-xs font-semibold text-[#9d1872] hover:underline" @click="clearCategoria()">
                            Ver todo
                        </button>
                    </div>
                    <div class="max-h-[420px] overflow-y-auto rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm">
                        <button
                            type="button"
                            class="mb-3 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-left text-sm font-semibold text-slate-700 hover:border-[#9d1872] hover:text-[#9d1872]"
                            :class="categoriaSeleccionada === null ? 'border-[#9d1872] text-[#9d1872]' : ''"
                            @click="clearCategoria()"
                        >
                            Todas las categorías
                        </button>
                        {!! $renderCategoryTree(null) !!}
                    </div>
                </div>
                <div class="max-h-[420px] space-y-6 overflow-y-auto">
                    <template x-if="!serviciosFiltrados().length">
                        <p class="rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-500">No se encontraron servicios con esa búsqueda.</p>
                    </template>
                    <template x-for="grupo in gruposServicios()" :key="grupo.nombre">
                        <div class="space-y-3">
                            <h5 class="text-xs font-semibold uppercase tracking-wide text-slate-500" x-text="grupo.nombre"></h5>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <template x-for="servicio in grupo.servicios" :key="servicio.id">
                                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-3 transition hover:border-[#9d1872] hover:bg-slate-50">
                                        <input
                                            type="checkbox"
                                            class="mt-1 h-4 w-4 rounded border-slate-300 text-[#9d1872] focus:ring-[#9d1872]"
                                            :value="String(servicio.id)"
                                            x-model="serviciosSeleccionados"
                                        >
                                        <span class="space-y-1">
                                            <span class="block text-sm font-semibold text-slate-900" x-text="servicio.referencia"></span>
                                            <span class="block text-xs text-slate-500" x-text="servicio.descripcion"></span>
                                            <span class="block text-xs font-semibold text-slate-700" x-text="`${Number(servicio.precio || 0).toFixed(2)} €`"></span>
                                        </span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-6 py-4">
                <p class="text-xs text-slate-500">
                    <span class="font-semibold text-slate-700" x-text="serviciosSeleccionados.length"></span>
                    servicios seleccionados
                </p>
                <div class="flex items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50" @click="closeServiciosModal()">
                        Cancelar
                    </button>
                    <button type="button" class="rounded-lg bg-[#9d1872] px-4 py-2 text-xs font-semibold text-white hover:bg-[#86145f]" @click="addSeleccionadosDesdeCatalogo()">
                        Añadir seleccionados
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2 text-left">Referencia</th>
                    <th class="px-3 py-2 text-left">Concepto</th>
                    <th class="px-3 py-2 text-right">Cantidad</th>
                    <th class="px-3 py-2 text-right">Precio</th>
                    <th class="px-3 py-2 text-right">Dto %</th>
                    <th class="px-3 py-2 text-right">IVA %</th>
                    <th class="px-3 py-2 text-right">Subtotal</th>
                    <th class="px-3 py-2 text-right">Total c/IVA</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <template x-for="(l, i) in lineas" :key="i">
                    <tr class="bg-white">
                        <td class="px-3 py-2">
                            <input type="hidden" :name="`lineas[${i}][service_id]`" x-model="l.service_id">
                            <input type="text" class="w-32 rounded border border-slate-200 px-2 py-1 text-sm"
                                   x-model="l.referencia" :name="`lineas[${i}][referencia]`">
                            <p class="mt-1 text-[11px] text-slate-500" x-show="l.service_id" x-cloak>Servicio de catálogo</p>
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" class="w-full rounded border border-slate-200 px-2 py-1 text-sm"
                                   x-model="l.concepto" :name="`lineas[${i}][concepto]`">
                        </td>
                        <td class="px-3 py-2 text-right">
                            <input type="number" step="0.01" min="0" class="w-24 rounded border border-slate-200 px-2 py-1 text-right text-sm"
                                   x-model.number="l.cantidad" :name="`lineas[${i}][cantidad]`">
                        </td>
                        <td class="px-3 py-2 text-right">
                            <input type="number" step="0.01" min="0" class="w-28 rounded border border-slate-200 px-2 py-1 text-right text-sm"
                                   x-model.number="l.precio" :name="`lineas[${i}][precio]`">
                        </td>
                        <td class="px-3 py-2 text-right">
                            <input type="number" step="0.01" min="0" max="100" class="w-20 rounded border border-slate-200 px-2 py-1 text-right text-sm"
                                   x-model.number="l.descuento_porcentaje" :name="`lineas[${i}][descuento_porcentaje]`">
                        </td>
                        <td class="px-3 py-2 text-right">
                            <input type="number" step="0.01" min="0" class="w-20 rounded border border-slate-200 px-2 py-1 text-right text-sm"
                                   x-model.number="l.iva_porcentaje" :name="`lineas[${i}][iva_porcentaje]`">
                        </td>
                        <td class="px-3 py-2 text-right tabular-nums" x-text="subTotal(l).toFixed(2)"></td>
                        <td class="px-3 py-2 text-right tabular-nums" x-text="totalLinea(l).toFixed(2)"></td>
                        <td class="px-3 py-2 text-right">
                            <button type="button" @click="remove(i)" class="rounded border border-slate-200 px-2 py-1 text-xs text-slate-600 hover:bg-slate-50">
                                Quitar
                            </button>
                        </td>
                    </tr>
                </template>

                <tr class="bg-slate-50 font-medium">
                    <td colspan="6" class="px-3 py-3 text-right">Totales</td>
                    <td class="px-3 py-3 text-right tabular-nums" x-text="totalSubtotal().toFixed(2)"></td>
                    <td class="px-3 py-3 text-right tabular-nums" x-text="totalFactura().toFixed(2)"></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>

    <input type="hidden" name="importe" :value="totalSubtotal().toFixed(2)">
    <input type="hidden" name="importe_total" :value="totalFactura().toFixed(2)">
</section>