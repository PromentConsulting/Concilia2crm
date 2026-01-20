@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">Importar cuentas</h1>
            <p class="text-gray-600 mt-1">Sube un archivo CSV para crear cuentas en bloque. Si hay duplicados por email/teléfono, podrás decidir qué hacer antes de confirmar.</p>
        </div>
        <a href="{{ route('accounts.index') }}" class="text-sm text-gray-600 hover:underline">Volver al listado</a>
    </div>

    @if (session('status'))
        <div class="mt-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">
            <div class="font-semibold">Revisa estos errores:</div>
            <ul class="list-disc pl-5 mt-1 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $rowsList = $rows ?? data_get($preview ?? [], 'rows', []);
        $importTokenVal = $importToken ?? data_get($preview ?? [], 'token', '');
    @endphp

    <div class="mt-6 grid gap-6">
        {{-- Plantilla + guía rápida --}}
        <div class="bg-white rounded-2xl shadow p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold">Plantilla de importación</h2>
                    <p class="text-gray-600 text-sm mt-1">Descarga una plantilla con las columnas correctas. Rellénala y vuelve a subirla.</p>
                </div>
                <a href="{{ route('accounts.import.template') }}" class="px-4 py-2 rounded-lg bg-[#9d1872] text-white hover:bg-[#7d135a] text-sm">
                    Descargar plantilla CSV
                </a>
            </div>

            <div class="mt-4 rounded-xl bg-slate-50 p-4">
                <div class="font-semibold text-sm">Guía rápida</div>
                <ul class="mt-2 list-disc pl-5 text-sm text-slate-700 space-y-1">
                    <li><strong>1 línea = 1 empresa (cuenta).</strong></li>
                    <li>Formato: <strong>CSV</strong> (recomendado en <strong>UTF-8</strong>).</li>
                    <li>Separador recomendado: <strong>;</strong> (punto y coma).</li>
                    <li>Si el email/teléfono ya existe <strong>fuera del mismo grupo empresarial</strong>, aparecerá como conflicto para que elijas qué hacer.</li>
                </ul>
            </div>
        </div>

        {{-- Formulario de subida o previsualización --}}
        @if (!isset($preview))
            <div class="bg-white rounded-2xl shadow p-6">
                <h2 class="text-lg font-semibold">Archivo CSV de cuentas</h2>
                <p class="text-gray-600 text-sm mt-1">Selecciona el archivo CSV y pulsa “Subir y previsualizar”.</p>

                <form action="{{ route('accounts.import.store') }}" method="POST" enctype="multipart/form-data" class="mt-4">
                    @csrf

                    <label class="block text-sm font-medium mb-2">Selecciona el archivo</label>
                    <input type="file" name="file" accept=".csv,text/csv" class="w-full border rounded-lg p-2" required>

                    <div class="mt-4 flex items-center gap-3">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-[#9d1872] text-white hover:bg-[#7d135a]">
                            Subir y previsualizar
                        </button>
                        <a href="{{ route('accounts.index') }}" class="text-sm text-gray-600 hover:underline">Cancelar</a>
                    </div>
                </form>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold">Previsualización</h2>
                        <p class="text-gray-600 text-sm mt-1">Revisa las filas detectadas. Si hay conflictos, decide qué hacer en cada una. Hasta que no selecciones una opción para todos los conflictos, no podrás confirmar.</p>
                    </div>
                    <a href="{{ route('accounts.import.create') }}" class="text-sm text-gray-600 hover:underline">Subir otro archivo</a>
                </div>

                <form id="confirmForm" action="{{ route('accounts.import.store') }}" method="POST" class="mt-4">
                    @csrf
                    <input type="hidden" name="confirm" value="1">
                    <input type="hidden" name="import_token" value="{{ $importTokenVal }}">

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="p-2 text-left border">#</th>
                                    <th class="p-2 text-left border">Nombre</th>
                                    <th class="p-2 text-left border">Email</th>
                                    <th class="p-2 text-left border">Teléfono</th>
                                    <th class="p-2 text-left border">Estado</th>
                                    <th class="p-2 text-left border">Decisión</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rowsList as $i => $row)
                                    @php
                                        $data = $row['data'] ?? [];
                                        $hasConflict = (bool)($row['has_conflict'] ?? false);
                                        $needsTarget = (bool)($row['needs_target'] ?? false);
                                        $conflicts = $row['conflicts'] ?? [];
                                        $defaultTarget = data_get($conflicts, '0.id');
                                    @endphp
                                    <tr class="{{ $hasConflict ? 'bg-amber-50' : '' }}">
                                        <td class="p-2 border">{{ $i + 1 }}</td>
                                        <td class="p-2 border">{{ $data['name'] ?? ($data['nombre_abreviado'] ?? '') }}</td>
                                        <td class="p-2 border">{{ $data['email_raw'] ?? ($data['email'] ?? '') }}</td>
                                        <td class="p-2 border">{{ $data['phone_raw'] ?? ($data['phone'] ?? '') }}</td>
                                        <td class="p-2 border">
                                            @if ($hasConflict)
                                                <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-800">Conflicto</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-800">Nueva</span>
                                            @endif
                                        </td>
                                        <td class="p-2 border">
                                            @if (!$hasConflict)
                                                —
                                            @else
                                                <div class="space-y-2" data-row="{{ $i }}">
                                                    <div class="space-y-1">
                                                        <label class="flex items-center gap-2">
                                                            <input type="radio" name="decisions[{{ $i }}]" value="keep" class="decision-radio" data-row="{{ $i }}">
                                                            <span>Mantener registro existente</span>
                                                        </label>
                                                        <label class="flex items-center gap-2">
                                                            <input type="radio" name="decisions[{{ $i }}]" value="update" class="decision-radio" data-row="{{ $i }}">
                                                            <span>Actualizar registro existente con los datos del CSV</span>
                                                        </label>
                                                        <label class="flex items-center gap-2">
                                                            <input type="radio" name="decisions[{{ $i }}]" value="create_new" class="decision-radio" data-row="{{ $i }}">
                                                            <span>Crear nuevo (duplicado)</span>
                                                        </label>
                                                    </div>

                                                    {{-- Selector del registro existente (si hay varios posibles) --}}
                                                    @if ($needsTarget)
                                                        <select name="targets[{{ $i }}]" class="target-select w-full border rounded-lg p-2 text-sm hidden" data-row="{{ $i }}">
                                                            <option value="">Selecciona el registro con el que choca…</option>
                                                            @foreach ($conflicts as $c)
                                                                <option value="{{ $c['id'] ?? '' }}">{{ $c['label'] ?? ('#' . ($c['id'] ?? '')) }}</option>
                                                            @endforeach
                                                        </select>
                                                    @elseif ($defaultTarget)
                                                        <input type="hidden" name="targets[{{ $i }}]" value="{{ $defaultTarget }}">
                                                    @endif

                                                    {{-- Confirmación extra para crear duplicado --}}
                                                    <label class="confirm-dup flex items-center gap-2 text-xs text-gray-600 hidden" data-row="{{ $i }}">
                                                        <input type="checkbox" name="confirm_duplicates[{{ $i }}]" value="1" class="confirm-dup-checkbox" data-row="{{ $i }}">
                                                        Confirmo que quiero crear un nuevo duplicado
                                                    </label>

                                                    {{-- Mostrar contra qué choca --}}
                                                    <div class="text-xs text-gray-600">
                                                        <div class="font-semibold">Coincidencias detectadas:</div>
                                                        <ul class="list-disc pl-5 mt-1 space-y-0.5">
                                                            @foreach ($conflicts as $c)
                                                                <li>{{ $c['label'] ?? ('#' . ($c['id'] ?? '')) }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            @endif

                                            @if (!empty($row['warnings']))
                                                <div class="mt-2 text-xs text-red-700">
                                                    <div class="font-semibold">Avisos de validación:</div>
                                                    <ul class="list-disc pl-5 mt-1 space-y-0.5">
                                                        @foreach ($row['warnings'] as $w)
                                                            <li>{{ $w }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-4">
                        <div id="readyHint" class="text-sm text-gray-600"></div>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('accounts.index') }}" class="text-sm text-gray-600 hover:underline">Cancelar</a>
                            <button id="confirmBtn" type="submit" class="px-4 py-2 rounded-lg bg-[#9d1872] text-white hover:bg-[#7d135a] disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                Confirmar importación
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <script>
                (function () {
                    const confirmBtn = document.getElementById('confirmBtn');
                    const readyHint = document.getElementById('readyHint');

                    function toggleRowUI(rowIndex) {
                        const selected = document.querySelector(`input.decision-radio[data-row="${rowIndex}"]:checked`);
                        const targetSelect = document.querySelector(`select.target-select[data-row="${rowIndex}"]`);
                        const confirmDup = document.querySelector(`label.confirm-dup[data-row="${rowIndex}"]`);

                        // Mostrar selector de target solo si (hay selector) y la decisión es keep/update
                        if (targetSelect) {
                            const showTarget = selected && (selected.value === 'keep' || selected.value === 'update');
                            targetSelect.classList.toggle('hidden', !showTarget);
                        }

                        // Mostrar confirmación de duplicado solo si la decisión es create_new
                        if (confirmDup) {
                            const showDup = selected && selected.value === 'create_new';
                            confirmDup.classList.toggle('hidden', !showDup);
                        }
                    }

                    function isRowResolved(rowIndex) {
                        const selected = document.querySelector(`input.decision-radio[data-row="${rowIndex}"]:checked`);
                        if (!selected) return false;

                        // Si create_new, debe estar marcado confirm_duplicates
                        if (selected.value === 'create_new') {
                            const chk = document.querySelector(`input.confirm-dup-checkbox[data-row="${rowIndex}"]`);
                            return !!(chk && chk.checked);
                        }

                        // Si keep/update y hay selector de target, debe tener valor
                        const targetSelect = document.querySelector(`select.target-select[data-row="${rowIndex}"]`);
                        if (targetSelect && !targetSelect.classList.contains('hidden')) {
                            return !!targetSelect.value;
                        }

                        return true;
                    }

                    function refreshState() {
                        const conflictBlocks = document.querySelectorAll('[data-row] input.decision-radio');
                        const rows = new Set();
                        conflictBlocks.forEach(r => rows.add(r.getAttribute('data-row')));

                        let unresolved = 0;
                        rows.forEach(idx => {
                            if (!isRowResolved(idx)) unresolved++;
                        });

                        if (unresolved === 0) {
                            confirmBtn.disabled = false;
                            readyHint.textContent = '✅ Todo listo. Puedes confirmar la importación.';
                        } else {
                            confirmBtn.disabled = true;
                            readyHint.textContent = `⚠️ Faltan ${unresolved} conflicto(s) por decidir.`;
                        }
                    }

                    // Bind events
                    document.querySelectorAll('input.decision-radio').forEach(radio => {
                        radio.addEventListener('change', () => {
                            toggleRowUI(radio.getAttribute('data-row'));
                            refreshState();
                        });
                    });

                    document.querySelectorAll('select.target-select').forEach(sel => {
                        sel.addEventListener('change', refreshState);
                    });

                    document.querySelectorAll('input.confirm-dup-checkbox').forEach(chk => {
                        chk.addEventListener('change', refreshState);
                    });

                    // Init
                    const allRowIndexes = new Set();
                    document.querySelectorAll('input.decision-radio').forEach(r => allRowIndexes.add(r.getAttribute('data-row')));
                    allRowIndexes.forEach(toggleRowUI);
                    refreshState();
                })();
            </script>
        @endif
    </div>
</div>
@endsection
