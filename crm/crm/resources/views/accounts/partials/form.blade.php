@php
    $account = $account ?? null;
    $groupParents = $groupParents ?? [];
    $groupChildren = $groupChildren ?? [];
@endphp

<div class="space-y-6">
    {{-- Datos principales --}}
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
            Datos principales
        </h2>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700">Tipo de cliente</label>
                <select
                    name="lifecycle"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    <option value="">Sin definir</option>
                    <option value="customer" {{ old('lifecycle', $account->lifecycle ?? '') === 'customer' ? 'selected' : '' }}>Cliente</option>
                    <option value="prospect" {{ old('lifecycle', $account->lifecycle ?? '') === 'prospect' ? 'selected' : '' }}>Cliente potencial</option>
                </select>
                @error('lifecycle')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Logo</label>
                <input
                    type="file"
                    name="logo"
                    accept="image/*"
                    class="mt-1 block w-full text-sm text-slate-700 file:mr-4 file:rounded-md file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200"
                >
                @if(! empty($account?->logo_path))
                    <div class="mt-2">
                        <img
                            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($account->logo_path) }}"
                            alt="Logo actual"
                            class="h-12 w-auto rounded border border-slate-200 bg-white object-contain p-1"
                        >
                    </div>
                @endif
                @error('logo')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700">Nombre comercial</label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name', $account->name ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    required
                >
                @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Razón social</label>
                <input
                    type="text"
                    name="legal_name"
                    value="{{ old('legal_name', $account->legal_name ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('legal_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Nombre abreviado</label>
                <input
                    type="text"
                    name="nombre_abreviado"
                    value="{{ old('nombre_abreviado', $account->nombre_abreviado ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('nombre_abreviado')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Tipo entidad</label>
                <select
                    name="tipo_entidad"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    <option value="">Sin definir</option>
                    <option value="empresa_privada" {{ old('tipo_entidad', $account->tipo_entidad ?? '') === 'empresa_privada' ? 'selected' : '' }}>Empresa privada</option>
                    <option value="aapp" {{ old('tipo_entidad', $account->tipo_entidad ?? '') === 'aapp' ? 'selected' : '' }}>AAPP</option>
                    <option value="sin_animo_de_lucro" {{ old('tipo_entidad', $account->tipo_entidad ?? '') === 'sin_animo_de_lucro' ? 'selected' : '' }}>Sin ánimo de lucro</option>
                    <option value="corporacion_derecho_publico" {{ old('tipo_entidad', $account->tipo_entidad ?? '') === 'corporacion_derecho_publico' ? 'selected' : '' }}>Corporación de Derecho público</option>
                    <option value="particular" {{ old('tipo_entidad', $account->tipo_entidad ?? '') === 'particular' ? 'selected' : '' }}>Particular</option>
                </select>
                @error('tipo_entidad')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">NIF / CIF</label>
                <input
                    type="text"
                    name="tax_id"
                    value="{{ old('tax_id', $account->tax_id ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('tax_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Estado</label>
                <select
                    name="estado"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    <option value="">Sin definir</option>
                    <option value="activo" {{ old('estado', $account->estado ?? '') === 'activo' ? 'selected' : '' }}>Activo</option>
                    <option value="inactivo" {{ old('estado', $account->estado ?? '') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                </select>
                @error('estado')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Tipo relación grupo</label>
                <select
                    name="tipo_relacion_grupo"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    <option value="">Independiente</option>
                    <option value="matriz" {{ old('tipo_relacion_grupo', $account->tipo_relacion_grupo ?? '') === 'matriz' ? 'selected' : '' }}>Matriz</option>
                    <option value="filial" {{ old('tipo_relacion_grupo', $account->tipo_relacion_grupo ?? '') === 'filial' ? 'selected' : '' }}>Filial</option>
                </select>
                @error('tipo_relacion_grupo')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Matriz (si es filial)</label>
                <select
                    name="parent_account_id"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    <option value="">Sin matriz</option>
                    @foreach($groupParents as $parent)
                        <option value="{{ $parent->id }}" {{ (string) old('parent_account_id', $account->parent_account_id ?? '') === (string) $parent->id ? 'selected' : '' }}>
                            {{ $parent->nombre_abreviado ?? $parent->name }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">Si esta cuenta pertenece como filial, selecciona aquí su matriz.</p>
                @error('parent_account_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    {{-- Contacto principal empresa --}}
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
            Contacto general de empresa
        </h2>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700">E-mail empresa</label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email', $account->email ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Teléfono</label>
                <input
                    type="text"
                    name="phone"
                    value="{{ old('phone', $account->phone ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('phone')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Fax</label>
                <input
                    type="text"
                    name="fax"
                    value="{{ old('fax', $account->fax ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('fax')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Página web</label>
                <input
                    type="text"
                    name="website"
                    value="{{ old('website', $account->website ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('website')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700">Cargo asociado (empresa)</label>
                <input
                    type="text"
                    name="main_contact_role"
                    value="{{ old('main_contact_role', $account->main_contact_role ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('main_contact_role')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    {{-- Localización --}}
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
            Localización
        </h2>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700">Dirección</label>
                <input
                    type="text"
                    name="address"
                    value="{{ old('address', $account->address ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('address')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Localidad</label>
                <input
                    type="text"
                    name="city"
                    value="{{ old('city', $account->city ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('city')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Provincia</label>
                <input
                    type="text"
                    name="state"
                    value="{{ old('state', $account->state ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('state')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Código postal</label>
                <input
                    type="text"
                    name="postal_code"
                    value="{{ old('postal_code', $account->postal_code ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('postal_code')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">País</label>
                <input
                    type="text"
                    name="country"
                    value="{{ old('country', $account->country ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('country')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    {{-- Tamaño y actividad --}}
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
            Tamaño y actividad
        </h2>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="md:col-span-1">
                <label class="block text-sm font-medium text-slate-700">Sector</label>
                <select
                    name="industry"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    <option value="">Sin definir</option>
                    @php
                        $currentIndustry = old('industry', $account->industry ?? '');
                    @endphp
                    <option value="Agricultura y alimentación" {{ $currentIndustry === 'Agricultura y alimentación' ? 'selected' : '' }}>Agricultura y alimentación</option>
                    <option value="Gobierno y administración pública" {{ $currentIndustry === 'Gobierno y administración pública' ? 'selected' : '' }}>Gobierno y administración pública</option>
                    <option value="Comercio y distribución" {{ $currentIndustry === 'Comercio y distribución' ? 'selected' : '' }}>Comercio y distribución</option>
                    <option value="Tercer sector" {{ $currentIndustry === 'Tercer sector' ? 'selected' : '' }}>Tercer sector</option>
                    <option value="Logística y transporte" {{ $currentIndustry === 'Logística y transporte' ? 'selected' : '' }}>Logística y transporte</option>
                    <option value="Construcción e infraestructuras" {{ $currentIndustry === 'Construcción e infraestructuras' ? 'selected' : '' }}>Construcción e infraestructuras</option>
                    <option value="Medios de comunicación y entretenimiento" {{ $currentIndustry === 'Medios de comunicación y entretenimiento' ? 'selected' : '' }}>Medios de comunicación y entretenimiento</option>
                    <option value="Automotriz e industria" {{ $currentIndustry === 'Automotriz e industria' ? 'selected' : '' }}>Automotriz e industria</option>
                    <option value="Consultoría" {{ $currentIndustry === 'Consultoría' ? 'selected' : '' }}>Consultoría</option>
                    <option value="Salud" {{ $currentIndustry === 'Salud' ? 'selected' : '' }}>Salud</option>
                    <option value="Energético e informático" {{ $currentIndustry === 'Energético e informático' ? 'selected' : '' }}>Energético e informático</option>
                    <option value="Turismo y hostelería" {{ $currentIndustry === 'Turismo y hostelería' ? 'selected' : '' }}>Turismo y hostelería</option>
                    <option value="Financiero y bancario" {{ $currentIndustry === 'Financiero y bancario' ? 'selected' : '' }}>Financiero y bancario</option>
                    <option value="Educación y formación" {{ $currentIndustry === 'Educación y formación' ? 'selected' : '' }}>Educación y formación</option>
                </select>
                @error('industry')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-1">
                <label class="block text-sm font-medium text-slate-700">Empleados</label>
                <input
                    type="number"
                    name="employee_count"
                    value="{{ old('employee_count', $account->employee_count ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('employee_count')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-1">
                <label class="block text-sm font-medium text-slate-700">Año de fundación</label>
                <input
                    type="number"
                    name="founded_year"
                    value="{{ old('founded_year', $account->founded_year ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('founded_year')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-1">
                <label class="block text-sm font-medium text-slate-700">Tamaño empresa (mín)</label>
                <input
                    type="number"
                    name="company_size_min"
                    value="{{ old('company_size_min', $account->company_size_min ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('company_size_min')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-1">
                <label class="block text-sm font-medium text-slate-700">Tamaño empresa (máx)</label>
                <input
                    type="number"
                    name="company_size_max"
                    value="{{ old('company_size_max', $account->company_size_max ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('company_size_max')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-1">
                <label class="block text-sm font-medium text-slate-700">CNAE</label>
                <input
                    type="text"
                    name="cnae"
                    value="{{ old('cnae', $account->cnae ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('cnae')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    {{-- Igualdad, calidad y RSE --}}
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
            Igualdad, calidad y RSE
        </h2>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700">Contratos públicos</label>
                <select
                    name="public_contracts"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    <option value="">Sin definir</option>
                    <option value="0" {{ (string) old('public_contracts', $account->public_contracts ?? '') === '0' ? 'selected' : '' }}>No</option>
                    <option value="1" {{ (string) old('public_contracts', $account->public_contracts ?? '') === '1' ? 'selected' : '' }}>Sí</option>
                </select>
                @error('public_contracts')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Plan de igualdad</label>
                <select
                    name="equality_plan"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    <option value="">Sin definir</option>
                    <option value="0" {{ (string) old('equality_plan', $account->equality_plan ?? '') === '0' ? 'selected' : '' }}>No</option>
                    <option value="1" {{ (string) old('equality_plan', $account->equality_plan ?? '') === '1' ? 'selected' : '' }}>Sí</option>
                </select>
                @error('equality_plan')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Distintivo de igualdad</label>
                <select
                    name="equality_mark"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    <option value="">Sin definir</option>
                    <option value="0" {{ (string) old('equality_mark', $account->equality_mark ?? '') === '0' ? 'selected' : '' }}>No</option>
                    <option value="1" {{ (string) old('equality_mark', $account->equality_mark ?? '') === '1' ? 'selected' : '' }}>Sí</option>
                </select>
                @error('equality_mark')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Calidad</label>
                <select
                    name="quality"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    <option value="">Sin definir</option>
                    <option value="0" {{ (string) old('quality', $account->quality ?? '') === '0' ? 'selected' : '' }}>No</option>
                    <option value="1" {{ (string) old('quality', $account->quality ?? '') === '1' ? 'selected' : '' }}>Sí</option>
                </select>
                @error('quality')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">RSE</label>
                <select
                    name="rse"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    <option value="">Sin definir</option>
                    <option value="0" {{ (string) old('rse', $account->rse ?? '') === '0' ? 'selected' : '' }}>No</option>
                    <option value="1" {{ (string) old('rse', $account->rse ?? '') === '1' ? 'selected' : '' }}>Sí</option>
                </select>
                @error('rse')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700">Otras certificaciones</label>
                <textarea
                    name="other_certifications"
                    rows="3"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >{{ old('other_certifications', $account->other_certifications ?? '') }}</textarea>
                @error('other_certifications')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    {{-- Características --}}
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
            Características
        </h2>

        @php
            $siNoUnknownOptions = [
                ''   => 'Desconocido',
                'si' => 'Sí',
                'no' => 'No',
            ];

            $val = fn($key) => old($key, $account->{$key} ?? '');
            $dateVal = fn($key) => old($key, optional($account->{$key} ?? null)->format('Y-m-d'));
        @endphp

        <div class="grid gap-4 md:grid-cols-2">
            {{-- Plan de igualdad --}}
            <div class="rounded-lg border border-slate-200 p-4">
                <label class="block text-sm font-medium text-slate-700">Plan de igualdad</label>
                <select
                    id="car_plan_igualdad"
                    name="car_plan_igualdad"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    @foreach($siNoUnknownOptions as $k => $label)
                        <option value="{{ $k }}" {{ (string) $val('car_plan_igualdad') === (string) $k ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('car_plan_igualdad')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror

                <div id="wrap_car_plan_igualdad_vigencia" class="mt-3">
                    <label class="block text-sm font-medium text-slate-700">Fecha de vigencia</label>
                    <input
                        type="date"
                        name="car_plan_igualdad_vigencia"
                        value="{{ $dateVal('car_plan_igualdad_vigencia') }}"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    >
                    @error('car_plan_igualdad_vigencia')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Plan LGTBI --}}
            <div class="rounded-lg border border-slate-200 p-4">
                <label class="block text-sm font-medium text-slate-700">Plan LGTBI</label>
                <select
                    id="car_plan_lgtbi"
                    name="car_plan_lgtbi"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    @foreach($siNoUnknownOptions as $k => $label)
                        <option value="{{ $k }}" {{ (string) $val('car_plan_lgtbi') === (string) $k ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('car_plan_lgtbi')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror

                <div id="wrap_car_plan_lgtbi_vigencia" class="mt-3">
                    <label class="block text-sm font-medium text-slate-700">Fecha de vigencia</label>
                    <input
                        type="date"
                        name="car_plan_lgtbi_vigencia"
                        value="{{ $dateVal('car_plan_lgtbi_vigencia') }}"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    >
                    @error('car_plan_lgtbi_vigencia')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Protocolo de acoso sexual y acoso por razón de sexo --}}
            <div class="rounded-lg border border-slate-200 p-4">
                <label class="block text-sm font-medium text-slate-700">Protocolo de acoso sexual y acoso por razón de sexo</label>
                <select
                    id="car_protocolo_acoso_sexual"
                    name="car_protocolo_acoso_sexual"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    @foreach($siNoUnknownOptions as $k => $label)
                        <option value="{{ $k }}" {{ (string) $val('car_protocolo_acoso_sexual') === (string) $k ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('car_protocolo_acoso_sexual')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror

                <div id="wrap_car_protocolo_acoso_sexual_revision" class="mt-3">
                    <label class="block text-sm font-medium text-slate-700">Fecha de última revisión</label>
                    <input
                        type="date"
                        name="car_protocolo_acoso_sexual_revision"
                        value="{{ $dateVal('car_protocolo_acoso_sexual_revision') }}"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    >
                    @error('car_protocolo_acoso_sexual_revision')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Protocolo de acoso laboral --}}
            <div class="rounded-lg border border-slate-200 p-4">
                <label class="block text-sm font-medium text-slate-700">Protocolo de acoso laboral</label>
                <select
                    id="car_protocolo_acoso_laboral"
                    name="car_protocolo_acoso_laboral"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    @foreach($siNoUnknownOptions as $k => $label)
                        <option value="{{ $k }}" {{ (string) $val('car_protocolo_acoso_laboral') === (string) $k ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('car_protocolo_acoso_laboral')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror

                <div id="wrap_car_protocolo_acoso_laboral_revision" class="mt-3">
                    <label class="block text-sm font-medium text-slate-700">Fecha de última revisión</label>
                    <input
                        type="date"
                        name="car_protocolo_acoso_laboral_revision"
                        value="{{ $dateVal('car_protocolo_acoso_laboral_revision') }}"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    >
                    @error('car_protocolo_acoso_laboral_revision')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Protocolo de acoso LGTBI --}}
            <div class="rounded-lg border border-slate-200 p-4">
                <label class="block text-sm font-medium text-slate-700">Protocolo de acoso LGTBI</label>
                <select
                    id="car_protocolo_acoso_lgtbi"
                    name="car_protocolo_acoso_lgtbi"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    @foreach($siNoUnknownOptions as $k => $label)
                        <option value="{{ $k }}" {{ (string) $val('car_protocolo_acoso_lgtbi') === (string) $k ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('car_protocolo_acoso_lgtbi')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror

                <div id="wrap_car_protocolo_acoso_lgtbi_revision" class="mt-3">
                    <label class="block text-sm font-medium text-slate-700">Fecha de última revisión</label>
                    <input
                        type="date"
                        name="car_protocolo_acoso_lgtbi_revision"
                        value="{{ $dateVal('car_protocolo_acoso_lgtbi_revision') }}"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    >
                    @error('car_protocolo_acoso_lgtbi_revision')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- VPT --}}
            <div class="rounded-lg border border-slate-200 p-4">
                <label class="block text-sm font-medium text-slate-700">VPT</label>
                <select
                    id="car_vpt"
                    name="car_vpt"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    @foreach($siNoUnknownOptions as $k => $label)
                        <option value="{{ $k }}" {{ (string) $val('car_vpt') === (string) $k ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('car_vpt')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            {{-- Registro retributivo --}}
            <div class="rounded-lg border border-slate-200 p-4">
                <label class="block text-sm font-medium text-slate-700">Registro retributivo</label>
                <select
                    id="car_registro_retributivo"
                    name="car_registro_retributivo"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    @foreach($siNoUnknownOptions as $k => $label)
                        <option value="{{ $k }}" {{ (string) $val('car_registro_retributivo') === (string) $k ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('car_registro_retributivo')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror

                <div id="wrap_car_registro_retributivo_revision" class="mt-3">
                    <label class="block text-sm font-medium text-slate-700">Fecha de última revisión</label>
                    <input
                        type="date"
                        name="car_registro_retributivo_revision"
                        value="{{ $dateVal('car_registro_retributivo_revision') }}"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    >
                    @error('car_registro_retributivo_revision')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Plan de Igualdad Estratégico --}}
            <div class="rounded-lg border border-slate-200 p-4">
                <label class="block text-sm font-medium text-slate-700">Plan de Igualdad Estratégico</label>
                <select
                    id="car_plan_igualdad_estrategico"
                    name="car_plan_igualdad_estrategico"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    @foreach($siNoUnknownOptions as $k => $label)
                        <option value="{{ $k }}" {{ (string) $val('car_plan_igualdad_estrategico') === (string) $k ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('car_plan_igualdad_estrategico')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror

                <div id="wrap_car_plan_igualdad_estrategico_vigencia" class="mt-3">
                    <label class="block text-sm font-medium text-slate-700">Fecha de vigencia</label>
                    <input
                        type="date"
                        name="car_plan_igualdad_estrategico_vigencia"
                        value="{{ $dateVal('car_plan_igualdad_estrategico_vigencia') }}"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    >
                    @error('car_plan_igualdad_estrategico_vigencia')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Sistema de gestión --}}
            <div class="rounded-lg border border-slate-200 p-4">
                <label class="block text-sm font-medium text-slate-700">Sistema de gestión</label>
                <select
                    id="car_sistema_gestion"
                    name="car_sistema_gestion"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    @foreach($siNoUnknownOptions as $k => $label)
                        <option value="{{ $k }}" {{ (string) $val('car_sistema_gestion') === (string) $k ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('car_sistema_gestion')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const mappings = [
                    { selectId: 'car_plan_igualdad', wrapId: 'wrap_car_plan_igualdad_vigencia', inputName: 'car_plan_igualdad_vigencia' },
                    { selectId: 'car_plan_lgtbi', wrapId: 'wrap_car_plan_lgtbi_vigencia', inputName: 'car_plan_lgtbi_vigencia' },
                    { selectId: 'car_protocolo_acoso_sexual', wrapId: 'wrap_car_protocolo_acoso_sexual_revision', inputName: 'car_protocolo_acoso_sexual_revision' },
                    { selectId: 'car_protocolo_acoso_laboral', wrapId: 'wrap_car_protocolo_acoso_laboral_revision', inputName: 'car_protocolo_acoso_laboral_revision' },
                    { selectId: 'car_protocolo_acoso_lgtbi', wrapId: 'wrap_car_protocolo_acoso_lgtbi_revision', inputName: 'car_protocolo_acoso_lgtbi_revision' },
                    { selectId: 'car_registro_retributivo', wrapId: 'wrap_car_registro_retributivo_revision', inputName: 'car_registro_retributivo_revision' },
                    { selectId: 'car_plan_igualdad_estrategico', wrapId: 'wrap_car_plan_igualdad_estrategico_vigencia', inputName: 'car_plan_igualdad_estrategico_vigencia' },
                ];

                function toggle(selectEl, wrapEl, inputName) {
                    if (!selectEl || !wrapEl) return;

                    const show = (selectEl.value === 'si');
                    wrapEl.classList.toggle('hidden', !show);

                    if (!show) {
                        const input = wrapEl.querySelector(`[name="${inputName}"]`);
                        if (input) input.value = '';
                    }
                }

                mappings.forEach(m => {
                    const selectEl = document.getElementById(m.selectId);
                    const wrapEl   = document.getElementById(m.wrapId);

                    if (wrapEl) wrapEl.classList.add('hidden');
                    toggle(selectEl, wrapEl, m.inputName);

                    if (selectEl) {
                        selectEl.addEventListener('change', () => toggle(selectEl, wrapEl, m.inputName));
                    }
                });
            });
        </script>
    </section>

    {{-- Intereses y gestión interna --}}
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
            Intereses y gestión interna
        </h2>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700">Dpto. Comercial</label>
                <input
                    type="text"
                    name="sales_department"
                    value="{{ old('sales_department', $account->sales_department ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('sales_department')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Fecha de actualización (origen)</label>
                <input
                    type="date"
                    name="legacy_updated_at"
                    value="{{ old('legacy_updated_at', optional($account->legacy_updated_at ?? null)->format('Y-m-d')) }}"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                @error('legacy_updated_at')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-4">
            <label class="block text-sm font-medium text-slate-700">Notas / comentarios</label>
            <textarea
                name="notes"
                rows="4"
                class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
            >{{ old('notes', $account->notes ?? '') }}</textarea>
            @error('notes')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>
    </section>
</div>
