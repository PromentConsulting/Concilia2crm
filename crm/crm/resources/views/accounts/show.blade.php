@extends('layouts.app')

@section('title', $account->name)

@section('content')
@php
    $hasLifecycle = $hasLifecycle ?? false;
    $activeTab = $tab ?? request('tab', 'resumen');

    $ynuLabel = function ($value) {
        return match((string) $value) {
            'si' => 'Sí',
            'no' => 'No',
            default => 'Desconocido',
        };
    };

    $formatDate = function ($value) {
        if (empty($value)) return '—';
        try {
            return \Carbon\Carbon::parse($value)->format('d/m/Y');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    };
@endphp

<div class="space-y-6">


@if(session('duplicate_conflicts'))
    @php
        $conf = session('duplicate_conflicts');
    @endphp
    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
        <div class="font-semibold">Aviso de posible duplicado</div>
        <div class="mt-1">
            Esta cuenta tiene datos que ya existen en otra cuenta <strong>fuera de su grupo empresarial</strong>.
            Revísalo antes de continuar:
        </div>

        <ul class="mt-2 list-disc pl-5 space-y-1">
            @if(!empty($conf['email']))
                <li>
                    <span class="font-medium">E-mail</span> coincide con:
                    @foreach($conf['email'] as $c)
                        <a class="underline" href="{{ route('accounts.show', $c['id']) }}">{{ $c['name'] }}</a>@if(!$loop->last), @endif
                    @endforeach
                </li>
            @endif

            @if(!empty($conf['phone']))
                <li>
                    <span class="font-medium">Teléfono</span> coincide con:
                    @foreach($conf['phone'] as $c)
                        <a class="underline" href="{{ route('accounts.show', $c['id']) }}">{{ $c['name'] }}</a>@if(!$loop->last), @endif
                    @endforeach
                </li>
            @endif
        </ul>
    </div>
@endif



    {{-- CABECERA --}}
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div class="space-y-2">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-semibold text-slate-900">
                    {{ $account->name }}
                </h1>

                @if($account->estado)
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                        Estado: {{ ucfirst($account->estado) }}
                    </span>
                @endif

                @if($hasLifecycle && $account->lifecycle)
                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                        {{ $account->lifecycle === 'customer' ? 'Cliente' : 'Prospecto' }}
                    </span>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-4 text-sm text-slate-600">
                @if($account->tax_id)
                    <span><span class="font-medium">NIF/CIF:</span> {{ $account->tax_id }}</span>
                @endif
                @if($account->sales_department)
                    <span><span class="font-medium">Dpto. Comercial:</span> {{ $account->sales_department }}</span>
                @endif
                @if($account->cnae)
                    <span><span class="font-medium">CNAE:</span> {{ $account->cnae }}</span>
                @endif
                @if($account->legacy_updated_at)
                    <span class="text-xs text-slate-500">
                        Última actualización origen: {{ $account->legacy_updated_at->format('d/m/Y') }}
                    </span>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a
                href="{{ route('tareas.create', ['account_id' => $account->id]) }}"
                class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"
            >
                Nueva tarea
            </a>
            <a
                href="{{ route('contacts.create', ['account_id' => $account->id]) }}"
                class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"
            >
                Nuevo contacto
            </a>
            <a
                href="{{ route('accounts.edit', $account) }}"
                class="inline-flex items-center rounded-lg bg-[#9d1872] px-3 py-2 text-sm font-medium text-white shadow hover:bg-[#7b1459]"
            >
                Editar
            </a>
        </div>
    </header>

    {{-- PESTAÑAS --}}
    <nav class="mt-2 border-b border-slate-200">
        @php
            $tabs = [
                'resumen'      => 'Resumen',
                'contactos'    => 'Contactos',
                'actividad'    => 'Actividad',
                'operaciones'  => 'Operaciones',
                'facturas'     => 'Facturas',
                'documentos'   => 'Documentos',
                'facturacion'  => 'Facturación',
                'sistema'      => 'Información del sistema',
            ];

            if (! empty($showGroupTab)) {
                $tabs = array_slice($tabs, 0, 1, true)
                    + ['grupo' => 'Grupo empresarial']
                    + array_slice($tabs, 1, null, true);
            }


            // Añadir contadores a las pestañas (ej. "Facturas (4)")
            if (isset($tabCounts)) {
                $countKeys = ['contactos', 'actividad', 'operaciones', 'facturas', 'documentos'];
                foreach ($countKeys as $k) {
                    if (isset($tabs[$k])) {
                        $tabs[$k] = $tabs[$k].' ('.($tabCounts[$k] ?? 0).')';
                    }
                }
            }
        @endphp

        <div class="-mb-px flex flex-wrap gap-4 text-sm">
            @foreach($tabs as $key => $label)
                <a
                    href="{{ route('accounts.show', $account) }}?tab={{ $key }}"
                    class="border-b-2 px-3 py-2
                        {{ $activeTab === $key
                            ? 'border-[#9d1872] text-[#9d1872] font-medium'
                            : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-200' }}"
                >
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </nav>

    {{-- CONTENIDO PESTAÑA: RESUMEN --}}
    @if($activeTab === 'resumen')
        <div class="grid gap-6 lg:grid-cols-3">
            {{-- COLUMNA IZQUIERDA (2/3): ficha de empresa --}}
            <div class="space-y-6 lg:col-span-2">

                {{-- Datos principales --}}
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
                        Datos principales
                    </h2>

                    <dl class="grid gap-4 md:grid-cols-2 text-sm">
                        <div>
                            <dt class="text-slate-500">Nombre comercial</dt>
                            <dd class="mt-1 font-medium text-slate-900">{{ $account->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Razón social</dt>
                            <dd class="mt-1 font-medium text-slate-900">
                                <div class="flex items-center justify-between gap-3">
                                    <span>{{ $account->legal_name ?? '—' }}</span>
                                    @if($account->logo_path)
                                        <img
                                            src="{{ \Illuminate\Support\Facades\Storage::url($account->logo_path) }}"
                                            alt="Logo {{ $account->name }}"
                                            class="h-10 w-auto rounded border border-slate-200 bg-white object-contain p-1"
                                        >
                                    @endif
                                </div>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Nombre abreviado</dt>
                            <dd class="mt-1 text-slate-900">{{ $account->nombre_abreviado ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Tipo entidad</dt>
                            <dd class="mt-1 text-slate-900">
                                @if($account->tipo_entidad)
                                    {{ match($account->tipo_entidad) {
                                        'empresa_privada' => 'Empresa privada',
                                        'aapp' => 'AAPP',
                                        'sin_animo_de_lucro' => 'Sin ánimo de lucro',
                                        'corporacion_derecho_publico' => 'Corporación de Derecho público',
                                        'particular' => 'Particular',
                                        default => $account->tipo_entidad,
                                    } }}
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">NIF / CIF</dt>
                            <dd class="mt-1 text-slate-900">{{ $account->billingValue('tax_id') ?? '—' }}
                            @if($account->billingIsInherited('tax_id'))
                                <span class="ml-2 inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-600">Heredado de matriz</span>
                            @endif</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Estado</dt>
                            <dd class="mt-1 text-slate-900">{{ ucfirst($account->estado ?? '—') }}</dd>
                        </div>
                    </dl>
                </section>

                {{-- Contacto general --}}
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
                        Contacto general de empresa
                    </h2>

                    <dl class="grid gap-4 md:grid-cols-2 text-sm">
                        <div>
                            <dt class="text-slate-500">E-mail empresa</dt>
                            <dd class="mt-1">
                                @if($account->email)
                                    <a href="mailto:{{ $account->email }}" class="text-[#9d1872] hover:underline">
                                        {{ $account->email }}
                                    </a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Teléfono</dt>
                            <dd class="mt-1">
                                @if($account->phone)
                                    <a href="tel:{{ $account->phone }}" class="text-slate-900 hover:underline">
                                        {{ $account->phone }}
                                    </a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Fax</dt>
                            <dd class="mt-1 text-slate-900">{{ $account->fax ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Página web</dt>
                            <dd class="mt-1">
                                @if($account->website)
                                    <a href="{{ \Illuminate\Support\Str::startsWith($account->website, ['http://','https://']) ? $account->website : 'https://' . $account->website }}"
                                       target="_blank"
                                       rel="noopener"
                                       class="text-[#9d1872] hover:underline">
                                        {{ $account->website }}
                                    </a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-slate-500">Cargo asociado (empresa)</dt>
                            <dd class="mt-1 text-slate-900">{{ $account->main_contact_role ?? '—' }}</dd>
                        </div>
                    </dl>
                </section>

                @if(! empty($account->notes))
                    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">Notas internas</h2>
                        <div class="whitespace-pre-line text-sm text-slate-900">{{ $account->notes }}</div>
                    </section>
                @endif

                @if(! empty($showGroupTab))
                    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
                            Grupo empresarial
                        </h2>

@php
                            // Hermanas: otras filiales de la misma matriz (solo si esta cuenta es filial)
                            $sisters = collect();
                            if ($account->parent) {
                                $sisters = \App\Models\Account::query()
                                    ->with('ownerUser:id,name')
                                    ->where('parent_account_id', $account->parent->id)
                                    ->where('id', '!=', $account->id)
                                    ->orderBy('name')
                                    ->get(['id','name','nombre_abreviado','country','estado','owner_user_id']);
                            }
                

            // Añadir contadores a las pestañas (ej. "Facturas (4)")
            if (isset($tabCounts)) {
                $countKeys = ['contactos', 'actividad', 'operaciones', 'facturas', 'documentos'];
                foreach ($countKeys as $k) {
                    if (isset($tabs[$k])) {
                        $tabs[$k] = $tabs[$k].' ('.($tabCounts[$k] ?? 0).')';
                    }
                }
            }
        @endphp

                        <div class="space-y-3 text-sm text-slate-700">
                            <div class="flex items-center gap-2">
                                <span class="font-medium">Rol:</span>
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700">
                                    {{ $account->tipo_relacion_grupo ? ucfirst($account->tipo_relacion_grupo) : 'Independiente' }}
                                </span>
                            </div>

                            @if($account->parent)
                                <div>
                                    <p class="text-slate-500">Matriz</p>
                                    <a href="{{ route('accounts.show', $account->parent) }}" class="font-medium text-[#9d1872] hover:underline">
                                        {{ $account->parent->nombre_abreviado ?? $account->parent->name }}
                                    </a>
                                    @if($account->parent->country)
                                        <span class="text-slate-500">({{ $account->parent->country }})</span>
                                    @endif
                                </div>
                            @endif

                            
                            @if($account->parent && $sisters->isNotEmpty())
                                <div>
                                    <p class="text-slate-500">Hermanas ({{ $sisters->count() }})</p>
                                    <ul class="mt-2 space-y-2">
                                        @foreach($sisters as $sister)
                                            <li class="flex flex-col rounded-lg border border-slate-200 p-3 hover:border-[#9d1872]/40">
                                                <div class="flex flex-wrap items-center justify-between gap-2">
                                                    <div class="flex flex-col">
                                                        <a href="{{ route('accounts.show', $sister) }}" class="font-medium text-[#9d1872] hover:underline">
                                                            {{ $sister->nombre_abreviado ?? $sister->name }}
                                                        </a>
                                                        <span class="text-xs text-slate-500">{{ $sister->country ?? '—' }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-2 text-xs text-slate-600">
                                                        @if($sister->estado)
                                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-1">{{ ucfirst($sister->estado) }}</span>
                                                        @endif
                                                        @if($sister->ownerUser)
                                                            <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2 py-1">
                                                                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.343 17.657a8 8 0 0 1 11.314 0M4.928 16.243A10 10 0 0 1 12 13a10 10 0 0 1 7.071 3.243"/></svg>
                                                                {{ $sister->ownerUser->name }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

@if($account->children->isNotEmpty())
                                <div>
                                    <p class="text-slate-500">Filiales ({{ $account->children->count() }})</p>
                                    <ul class="mt-2 space-y-2">
                                        @foreach($account->children as $child)
                                            <li class="flex flex-col rounded-lg border border-slate-200 p-3 hover:border-[#9d1872]/40">
                                                <div class="flex flex-wrap items-center justify-between gap-2">
                                                    <div class="flex flex-col">
                                                        <a href="{{ route('accounts.show', $child) }}" class="font-medium text-[#9d1872] hover:underline">
                                                            {{ $child->nombre_abreviado ?? $child->name }}
                                                        </a>
                                                        <span class="text-xs text-slate-500">{{ $child->country ?? '—' }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-2 text-xs text-slate-600">
                                                        @if($child->estado)
                                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-1">{{ ucfirst($child->estado) }}</span>
                                                        @endif
                                                        @if($child->ownerUser)
                                                            <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2 py-1">
                                                                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.343 17.657a8 8 0 0 1 11.314 0M4.928 16.243A10 10 0 0 1 12 13a10 10 0 0 1 7.071 3.243"/></svg>
                                                                {{ $child->ownerUser->name }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </section>
                @endif

                {{-- Localización --}}
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
                        Localización
                    </h2>

                    <dl class="grid gap-4 md:grid-cols-2 text-sm">
                        <div class="md:col-span-2">
                            <dt class="text-slate-500">Dirección</dt>
                            <dd class="mt-1 text-slate-900">{{ $account->address ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Localidad</dt>
                            <dd class="mt-1 text-slate-900">{{ $account->city ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Provincia</dt>
                            <dd class="mt-1 text-slate-900">{{ $account->state ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Código postal</dt>
                            <dd class="mt-1 text-slate-900">{{ $account->postal_code ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">País</dt>
                            <dd class="mt-1 text-slate-900">{{ $account->country ?? '—' }}</dd>
                        </div>
                    </dl>
                </section>

                {{-- Tamaño y actividad --}}
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
                        Tamaño y actividad
                    </h2>

                    <dl class="grid gap-4 md:grid-cols-3 text-sm">
                        <div>
                            <dt class="text-slate-500">Sector</dt>
                            <dd class="mt-1 text-slate-900">{{ $account->industry ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Empleados</dt>
                            <dd class="mt-1 text-slate-900">{{ $account->employee_count ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Año de fundación</dt>
                            <dd class="mt-1 text-slate-900">{{ $account->founded_year ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Tamaño empresa (mín)</dt>
                            <dd class="mt-1 text-slate-900">{{ $account->company_size_min ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Tamaño empresa (máx)</dt>
                            <dd class="mt-1 text-slate-900">{{ $account->company_size_max ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">CNAE</dt>
                            <dd class="mt-1 text-slate-900">{{ $account->cnae ?? '—' }}</dd>
                        </div>
                    </dl>
                </section>

                {{-- Igualdad, calidad y RSE --}}
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
                        Igualdad, calidad y RSE
                    </h2>

                    <dl class="grid gap-4 md:grid-cols-2 text-sm">
                        <div>
                            <dt class="text-slate-500">Contratos públicos</dt>
                            <dd class="mt-1 text-slate-900">{{ isset($account->public_contracts) ? ($account->public_contracts ? 'Sí' : 'No') : '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Plan de igualdad</dt>
                            <dd class="mt-1 text-slate-900">{{ isset($account->equality_plan) ? ($account->equality_plan ? 'Sí' : 'No') : '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Distintivo de igualdad</dt>
                            <dd class="mt-1 text-slate-900">{{ isset($account->equality_mark) ? ($account->equality_mark ? 'Sí' : 'No') : '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Calidad</dt>
                            <dd class="mt-1 text-slate-900">{{ isset($account->quality) ? ($account->quality ? 'Sí' : 'No') : '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">RSE</dt>
                            <dd class="mt-1 text-slate-900">{{ isset($account->rse) ? ($account->rse ? 'Sí' : 'No') : '—' }}</dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-slate-500">Otras certificaciones</dt>
                            <dd class="mt-1 text-slate-900 whitespace-pre-line">
                                {{ $account->other_certifications ?? '—' }}
                            </dd>
                        </div>
                    </dl>
                </section>

                {{-- Características (NUEVO) --}}
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
                        Características
                    </h2>

                    <dl class="grid gap-4 md:grid-cols-2 text-sm">

                        <div>
                            <dt class="text-slate-500">Plan de igualdad</dt>
                            <dd class="mt-1 text-slate-900">
                                {{ $ynuLabel($account->car_plan_igualdad ?? null) }}
                                @if(($account->car_plan_igualdad ?? null) === 'si')
                                    <div class="mt-1 text-xs text-slate-500">
                                        Fecha de vigencia: {{ $formatDate($account->car_plan_igualdad_vigencia ?? null) }}
                                    </div>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-slate-500">Plan LGTBI</dt>
                            <dd class="mt-1 text-slate-900">
                                {{ $ynuLabel($account->car_plan_lgtbi ?? null) }}
                                @if(($account->car_plan_lgtbi ?? null) === 'si')
                                    <div class="mt-1 text-xs text-slate-500">
                                        Fecha de vigencia: {{ $formatDate($account->car_plan_lgtbi_vigencia ?? null) }}
                                    </div>
                                @endif
                            </dd>
                        </div>

                        <div class="md:col-span-2">
                            <dt class="text-slate-500">Protocolo de acoso sexual y acoso por razón de sexo</dt>
                            <dd class="mt-1 text-slate-900">
                                {{ $ynuLabel($account->car_protocolo_acoso_sexual ?? null) }}
                                @if(($account->car_protocolo_acoso_sexual ?? null) === 'si')
                                    <div class="mt-1 text-xs text-slate-500">
                                        Fecha de última revisión: {{ $formatDate($account->car_protocolo_acoso_sexual_revision ?? null) }}
                                    </div>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-slate-500">Protocolo de acoso laboral</dt>
                            <dd class="mt-1 text-slate-900">
                                {{ $ynuLabel($account->car_protocolo_acoso_laboral ?? null) }}
                                @if(($account->car_protocolo_acoso_laboral ?? null) === 'si')
                                    <div class="mt-1 text-xs text-slate-500">
                                        Fecha de última revisión: {{ $formatDate($account->car_protocolo_acoso_laboral_revision ?? null) }}
                                    </div>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-slate-500">Protocolo de acoso LGTBI</dt>
                            <dd class="mt-1 text-slate-900">
                                {{ $ynuLabel($account->car_protocolo_acoso_lgtbi ?? null) }}
                                @if(($account->car_protocolo_acoso_lgtbi ?? null) === 'si')
                                    <div class="mt-1 text-xs text-slate-500">
                                        Fecha de última revisión: {{ $formatDate($account->car_protocolo_acoso_lgtbi_revision ?? null) }}
                                    </div>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-slate-500">VPT</dt>
                            <dd class="mt-1 text-slate-900">
                                {{ $ynuLabel($account->car_vpt ?? null) }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-slate-500">Registro retributivo</dt>
                            <dd class="mt-1 text-slate-900">
                                {{ $ynuLabel($account->car_registro_retributivo ?? null) }}
                                @if(($account->car_registro_retributivo ?? null) === 'si')
                                    <div class="mt-1 text-xs text-slate-500">
                                        Fecha de última revisión: {{ $formatDate($account->car_registro_retributivo_revision ?? null) }}
                                    </div>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-slate-500">Plan de Igualdad Estratégico</dt>
                            <dd class="mt-1 text-slate-900">
                                {{ $ynuLabel($account->car_plan_igualdad_estrategico ?? null) }}
                                @if(($account->car_plan_igualdad_estrategico ?? null) === 'si')
                                    <div class="mt-1 text-xs text-slate-500">
                                        Fecha de vigencia: {{ $formatDate($account->car_plan_igualdad_estrategico_vigencia ?? null) }}
                                    </div>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-slate-500">Sistema de gestión</dt>
                            <dd class="mt-1 text-slate-900">
                                {{ $ynuLabel($account->car_sistema_gestion ?? null) }}
                            </dd>
                        </div>

                    </dl>
                </section>

                {{-- Intereses y gestión interna --}}
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
                        Intereses y gestión interna
                    </h2>

                    <dl class="grid gap-4 md:grid-cols-2 text-sm">
                        <div>
                            <dt class="text-slate-500">Dpto. Comercial</dt>
                            <dd class="mt-1 text-slate-900">{{ $account->sales_department ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Lifecycle</dt>
                            <dd class="mt-1 text-slate-900">
                                @if($account->lifecycle === 'customer')
                                    Cliente
                                @elseif($account->lifecycle === 'prospect')
                                    Prospecto
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                    </dl>
                </section>
            </div>

            {{-- COLUMNA DERECHA: resumen rápido --}}
            <div class="space-y-6">
                {{-- Resumen rápido --}}
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
                        Resumen
                    </h2>
                    <dl class="space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500">Contactos</dt>
                            <dd class="font-medium text-slate-900">
                                {{ $account->contacts_count ?? $account->contacts->count() }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500">Empleados</dt>
                            <dd class="font-medium text-slate-900">
                                {{ $account->employee_count ?? '—' }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500">Sector</dt>
                            <dd class="text-slate-900">
                                {{ $account->industry ?? '—' }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500">CNAE</dt>
                            <dd class="text-slate-900">
                                {{ $account->cnae ?? '—' }}
                            </dd>
                        </div>
                    </dl>
                </section>
            </div>
        </div>
    @endif

    {{-- PESTAÑA: GRUPO EMPRESARIAL --}}
    @if($activeTab === 'grupo')
        <div class="mt-6 space-y-6">
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">Estructura del grupo</h2>

                <div class="space-y-4 text-sm text-slate-700">
                    <div class="flex items-center gap-2">
                        <span class="font-medium">Rol de la cuenta:</span>
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700">
                            {{ $account->tipo_relacion_grupo ? ucfirst($account->tipo_relacion_grupo) : 'Independiente' }}
                        </span>
                    </div>

                    @php
                        $groupRole = $account->tipo_relacion_grupo ?: 'independiente';
                        $groupSisters = collect();
                        if ($account->parent) {
                            $groupSisters = \App\Models\Account::query()
                                ->with('ownerUser:id,name')
                                ->where('parent_account_id', $account->parent->id)
                                ->where('id', '!=', $account->id)
                                ->orderBy('name')
                                ->get(['id','name','nombre_abreviado','country','estado','owner_user_id']);
                        }
            

            // Añadir contadores a las pestañas (ej. "Facturas (4)")
            if (isset($tabCounts)) {
                $countKeys = ['contactos', 'actividad', 'operaciones', 'facturas', 'documentos'];
                foreach ($countKeys as $k) {
                    if (isset($tabs[$k])) {
                        $tabs[$k] = $tabs[$k].' ('.($tabCounts[$k] ?? 0).')';
                    }
                }
            }
        @endphp

                    @if($account->parent)
                        <div class="rounded-lg border border-slate-200 p-3">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Matriz</p>
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="space-y-1">
                                    <a href="{{ route('accounts.show', $account->parent) }}" class="font-medium text-[#9d1872] hover:underline">
                                        {{ $account->parent->nombre_abreviado ?? $account->parent->name }}
                                    </a>
                                    <div class="text-xs text-slate-500">{{ $account->parent->country ?? '—' }}</div>
                                </div>
                                @if($account->parent->estado)
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-1 text-xs">{{ ucfirst($account->parent->estado) }}</span>
                                @endif
                            </div>
                        </div>
                    @endif

                    
                    @if($groupRole === 'matriz')
                        @if($account->children->isNotEmpty())
                            <div class="rounded-lg border border-slate-200 p-3">
                                <div class="mb-2 flex items-center justify-between">
                                    <p class="text-xs uppercase tracking-wide text-slate-500">Filiales</p>
                                    <span class="text-xs text-slate-500">{{ $account->children->count() }} registros</span>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-left text-xs text-slate-700">
                                        <thead class="text-[11px] uppercase text-slate-500">
                                            <tr>
                                                <th class="px-2 py-2">Nombre</th>
                                                <th class="px-2 py-2">País</th>
                                                <th class="px-2 py-2">Estado</th>
                                                <th class="px-2 py-2">Propietario</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($account->children as $child)
                                                <tr class="border-t border-slate-100">
                                                    <td class="px-2 py-2 font-medium text-[#9d1872]">
                                                        <a href="{{ route('accounts.show', $child) }}" class="hover:underline">
                                                            {{ $child->nombre_abreviado ?? $child->name }}
                                                        </a>
                                                    </td>
                                                    <td class="px-2 py-2">{{ $child->country ?? '—' }}</td>
                                                    <td class="px-2 py-2">{{ ucfirst($child->estado ?? '—') }}</td>
                                                    <td class="px-2 py-2">{{ optional($child->ownerUser)->name ?? '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-slate-500">No hay filiales asociadas.</p>
                        @endif
                    @elseif($account->parent)
                        <div class="rounded-lg border border-slate-200 p-3">
                            <div class="mb-2 flex items-center justify-between">
                                <p class="text-xs uppercase tracking-wide text-slate-500">Hermanas</p>
                                <span class="text-xs text-slate-500">{{ $groupSisters->count() }} registros</span>
                            </div>

                            @if($groupSisters->isEmpty())
                                <p class="text-sm text-slate-500">No hay otras filiales asociadas a este grupo.</p>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-left text-xs text-slate-700">
                                        <thead class="text-[11px] uppercase text-slate-500">
                                            <tr>
                                                <th class="px-2 py-2">Nombre</th>
                                                <th class="px-2 py-2">País</th>
                                                <th class="px-2 py-2">Estado</th>
                                                <th class="px-2 py-2">Propietario</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($groupSisters as $sister)
                                                <tr class="border-t border-slate-100">
                                                    <td class="px-2 py-2 font-medium text-[#9d1872]">
                                                        <a href="{{ route('accounts.show', $sister) }}" class="hover:underline">
                                                            {{ $sister->nombre_abreviado ?? $sister->name }}
                                                        </a>
                                                    </td>
                                                    <td class="px-2 py-2">{{ $sister->country ?? '—' }}</td>
                                                    <td class="px-2 py-2">{{ ucfirst($sister->estado ?? '—') }}</td>
                                                    <td class="px-2 py-2">{{ optional($sister->ownerUser)->name ?? '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-slate-500">Esta cuenta no está asociada a un grupo empresarial.</p>
                    @endif
                </div>
            </section>
        </div>
    @endif

    {{-- PESTAÑA: CONTACTOS --}}
    @if($activeTab === 'contactos')
        <div class="mt-6 space-y-6">
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">
                        Contactos de la cuenta
                    </h2>
                    <a
                        href="{{ route('contacts.create', ['account_id' => $account->id]) }}"
                        class="text-xs font-medium text-[#9d1872] hover:underline"
                    >
                        Añadir contacto
                    </a>
                </div>

                @if($account->contacts->isEmpty())
                    <p class="text-sm text-slate-500">
                        Esta cuenta todavía no tiene contactos asociados.
                    </p>
                @else
                    <ul class="space-y-3 text-sm">
                        @foreach($account->contacts as $contact)
                            <li class="flex items-start justify-between gap-3">
                                <div>
                                    <a
                                        href="{{ route('contacts.show', $contact) }}"
                                        class="font-medium text-slate-900 hover:text-[#9d1872]"
                                    >
                                        {{ $contact->name ?? trim(($contact->first_name ?? '') . ' ' . ($contact->last_name ?? '')) }}
                                    </a>
                                    @if($contact->job_title)
                                        <p class="text-xs text-slate-500">
                                            {{ $contact->job_title }}
                                        </p>
                                    @endif
                                    @if($contact->email)
                                        <p class="text-xs text-slate-500">
                                            <a href="mailto:{{ $contact->email }}" class="hover:underline">
                                                {{ $contact->email }}
                                            </a>
                                        </p>
                                    @endif
                                </div>
                                @if($contact->phone || $contact->mobile)
                                    <div class="text-right text-xs text-slate-500">
                                        @if($contact->phone)
                                            <div>{{ $contact->phone }}</div>
                                        @endif
                                        @if($contact->mobile)
                                            <div>{{ $contact->mobile }}</div>
                                        @endif
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    @endif

    {{-- PESTAÑA: ACTIVIDAD --}}
    @if($activeTab === 'actividad')
        <div class="mt-6 space-y-6">
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">
                        Tareas relacionadas
                    </h2>
                    <a
                        href="{{ route('tareas.create', ['account_id' => $account->id]) }}"
                        class="text-xs font-medium text-[#9d1872] hover:underline"
                    >
                        Nueva tarea
                    </a>
                </div>

                @if($account->tareas->isEmpty())
                    <p class="text-sm text-slate-500">
                        Esta cuenta todavía no tiene tareas asociadas.
                    </p>
                @else
                    <ul class="divide-y divide-slate-100 text-sm">
                        @foreach($account->tareas as $tarea)
                            <li class="py-2 flex items-start justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs uppercase tracking-wide text-slate-400">
                                            {{ ucfirst($tarea->tipo) }}
                                        </span>
                                        @if($tarea->estado === 'completada')
                                            <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] text-emerald-700">
                                                Completada
                                            </span>
                                        @elseif($tarea->estado === 'en_proceso')
                                            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] text-amber-700">
                                                En proceso
                                            </span>
                                        @else
                                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-700">
                                                Pendiente
                                            </span>
                                        @endif
                                    </div>

                                    <p class="mt-1 text-sm font-medium text-slate-900">
                                        <a href="{{ route('tareas.edit', $tarea) }}" class="hover:text-[#9d1872]">
                                            {{ $tarea->titulo }}
                                        </a>
                                    </p>

                                    @if($tarea->descripcion)
                                        <p class="mt-0.5 text-xs text-slate-500">
                                            {{ \Illuminate\Support\Str::limit($tarea->descripcion, 120) }}
                                        </p>
                                    @endif
                                </div>

                                <div class="text-right text-xs text-slate-500 space-y-1">
                                    @if($tarea->fecha_vencimiento)
                                        <div>
                                            Vence:
                                            <span class="font-medium text-slate-700">
                                                {{ $tarea->fecha_vencimiento->format('d/m/Y H:i') }}
                                            </span>
                                        </div>
                                    @endif

                                    @if($tarea->owner)
                                        <div>Propietario: {{ $tarea->owner->name }}</div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    @endif

    {{-- PESTAÑA: OPERACIONES --}}
    @if($activeTab === 'operaciones')
        <div class="mt-6 space-y-6">
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">
                        Operaciones
                    </h2>

                    <form method="GET" action="{{ route('accounts.show', $account) }}" class="flex flex-wrap items-end gap-2 text-xs">
                        <input type="hidden" name="tab" value="operaciones">

                        @php
                            $f = $operationFilters ?? ['scope' => 'self', 'company_id' => null, 'tipo' => 'all'];
                

            // Añadir contadores a las pestañas (ej. "Facturas (4)")
            if (isset($tabCounts)) {
                $countKeys = ['contactos', 'actividad', 'operaciones', 'facturas', 'documentos'];
                foreach ($countKeys as $k) {
                    if (isset($tabs[$k])) {
                        $tabs[$k] = $tabs[$k].' ('.($tabCounts[$k] ?? 0).')';
                    }
                }
            }
        @endphp

                        <div>
                            <label class="block text-[11px] text-slate-500">Ámbito</label>
                            <select name="scope" class="mt-1 rounded-lg border border-slate-300 px-2 py-1">
                                <option value="self"  {{ ($f['scope'] ?? 'self') === 'self' ? 'selected' : '' }}>Solo esta cuenta</option>
                                <option value="group" {{ ($f['scope'] ?? 'self') === 'group' ? 'selected' : '' }}>Grupo (matriz + filiales)</option>
                            </select>
                        </div>

                        @if(isset($groupCompanies) && $groupCompanies->count() > 1)
                            <div>
                                <label class="block text-[11px] text-slate-500">Empresa del grupo</label>
                                <select name="company_id" class="mt-1 rounded-lg border border-slate-300 px-2 py-1">
                                    <option value="">Todas</option>
                                    @foreach($groupCompanies as $gc)
                                        <option value="{{ $gc->id }}" {{ (string)($f['company_id'] ?? '') === (string)$gc->id ? 'selected' : '' }}>
                                            {{ $gc->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div>
                            <label class="block text-[11px] text-slate-500">Tipo</label>
                            <select name="tipo" class="mt-1 rounded-lg border border-slate-300 px-2 py-1">
                                <option value="all"        {{ ($f['tipo'] ?? 'all') === 'all' ? 'selected' : '' }}>Todos</option>
                                <option value="solicitudes" {{ ($f['tipo'] ?? 'all') === 'solicitudes' ? 'selected' : '' }}>Solicitudes</option>
                                <option value="peticiones"  {{ ($f['tipo'] ?? 'all') === 'peticiones' ? 'selected' : '' }}>Peticiones</option>
                                <option value="pedidos"     {{ ($f['tipo'] ?? 'all') === 'pedidos' ? 'selected' : '' }}>Pedidos</option>
                            </select>
                        </div>

                        <button type="submit" class="rounded-lg bg-slate-900 px-3 py-1.5 text-white hover:bg-slate-800">
                            Filtrar
                        </button>
                    </form>
                </div>

                @if(($operations ?? collect())->isEmpty())
                    <p class="text-sm text-slate-500">No hay operaciones para los filtros seleccionados.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500">
                                    <th class="py-2 pr-4">Tipo</th>
                                    <th class="py-2 pr-4">Título</th>
                                    <th class="py-2 pr-4">Estado</th>
                                    <th class="py-2 pr-4">Fecha</th>
                                    <th class="py-2 pr-4 text-right">Importe</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($operations as $op)
                                    <tr>
                                        <td class="py-2 pr-4">
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-700">
                                                {{ $op['tipo'] }}
                                            </span>
                                        </td>
                                        <td class="py-2 pr-4">
                                            <a href="{{ $op['route'] }}" class="font-medium text-slate-900 hover:text-[#9d1872]">
                                                {{ $op['titulo'] }}
                                            </a>
                                        </td>
                                        <td class="py-2 pr-4 text-slate-700">
                                            {{ $op['estado'] ?? '—' }}
                                        </td>
                                        <td class="py-2 pr-4 text-slate-600">
                                            @if(!empty($op['fecha']))
                                                {{ \Carbon\Carbon::parse($op['fecha'])->format('d/m/Y') }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="py-2 pr-4 text-right text-slate-700">
                                            @if(isset($op['importe']) && $op['importe'] !== null)
                                                {{ number_format((float)$op['importe'], 2, ',', '.') }} {{ $op['moneda'] ?? 'EUR' }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>
    @endif

    {{-- PESTAÑA: FACTURAS --}}
    @if($activeTab === 'facturas')
        <div class="mt-6 space-y-6">
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">
                        Facturas
                    </h2>

                    <form method="GET" action="{{ route('accounts.show', $account) }}" class="flex flex-wrap items-end gap-2 text-xs">
                        <input type="hidden" name="tab" value="facturas">

                        @php
                            $f = $operationFilters ?? ['scope' => 'self', 'company_id' => null];
                

            // Añadir contadores a las pestañas (ej. "Facturas (4)")
            if (isset($tabCounts)) {
                $countKeys = ['contactos', 'actividad', 'operaciones', 'facturas', 'documentos'];
                foreach ($countKeys as $k) {
                    if (isset($tabs[$k])) {
                        $tabs[$k] = $tabs[$k].' ('.($tabCounts[$k] ?? 0).')';
                    }
                }
            }
        @endphp

                        <div>
                            <label class="block text-[11px] text-slate-500">Ámbito</label>
                            <select name="scope" class="mt-1 rounded-lg border border-slate-300 px-2 py-1">
                                <option value="self"  {{ ($f['scope'] ?? 'self') === 'self' ? 'selected' : '' }}>Solo esta cuenta</option>
                                <option value="group" {{ ($f['scope'] ?? 'self') === 'group' ? 'selected' : '' }}>Grupo (matriz + filiales)</option>
                            </select>
                        </div>

                        @if(isset($groupCompanies) && $groupCompanies->count() > 1)
                            <div>
                                <label class="block text-[11px] text-slate-500">Empresa del grupo</label>
                                <select name="company_id" class="mt-1 rounded-lg border border-slate-300 px-2 py-1">
                                    <option value="">Todas</option>
                                    @foreach($groupCompanies as $gc)
                                        <option value="{{ $gc->id }}" {{ (string)($f['company_id'] ?? '') === (string)$gc->id ? 'selected' : '' }}>
                                            {{ $gc->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <button type="submit" class="rounded-lg bg-slate-900 px-3 py-1.5 text-white hover:bg-slate-800">
                            Filtrar
                        </button>
                    </form>
                </div>

                @if(($facturas ?? collect())->isEmpty())
                    <p class="text-sm text-slate-500">No hay facturas para los filtros seleccionados.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    <th class="py-2 pr-4">Nº Factura</th>
                                    <th class="py-2 pr-4">Empresa</th>
                                    <th class="py-2 pr-4">Pedido</th>
                                    <th class="py-2 pr-4">Estado</th>
                                    <th class="py-2 pr-4">Fecha</th>
                                    <th class="py-2 pr-0 text-right">Importe</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($facturas as $factura)
                                    <tr class="hover:bg-slate-50">
                                        <td class="py-3 pr-4 font-medium text-slate-900">
                                            <a href="{{ route('facturas.show', $factura) }}" class="hover:text-[#9d1872]">
                                                {{ $factura->numero ?: ($factura->numero_serie ?: ('#' . $factura->id)) }}
                                            </a>
                                        </td>
                                        <td class="py-3 pr-4 text-slate-700">
                                            {{ $factura->cuenta->name ?? '—' }}
                                        </td>
                                        <td class="py-3 pr-4 text-slate-700">
                                            @if($factura->pedido)
                                                <a class="hover:underline" href="{{ route('pedidos.show', $factura->pedido) }}">
                                                    {{ $factura->pedido->numero ?: ('#'.$factura->pedido->id) }}
                                                </a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="py-3 pr-4 text-slate-700">{{ $factura->estado ?? '—' }}</td>
                                        <td class="py-3 pr-4 text-slate-700">
                                            @if($factura->fecha_factura)
                                                {{ \Illuminate\Support\Carbon::parse($factura->fecha_factura)->format('d/m/Y') }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="py-3 pr-0 text-right text-slate-900">
                                            {{ number_format((float)($factura->importe_total ?? $factura->importe ?? 0), 2, ',', '.') }} {{ $factura->moneda ?? 'EUR' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>
    @endif

    {{-- PESTAÑA: DOCUMENTOS --}}
    @if($activeTab === 'documentos')
        @php
            $docsGenerales = $docsGenerales ?? collect();
            $docsVinculados = $docsVinculados ?? collect();


            // Añadir contadores a las pestañas (ej. "Facturas (4)")
            if (isset($tabCounts)) {
                $countKeys = ['contactos', 'actividad', 'operaciones', 'facturas', 'documentos'];
                foreach ($countKeys as $k) {
                    if (isset($tabs[$k])) {
                        $tabs[$k] = $tabs[$k].' ('.($tabCounts[$k] ?? 0).')';
                    }
                }
            }
        @endphp

        <div class="mt-6 space-y-6">
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">
                        Documentos generales de la cuenta
                    </h2>
                    <a
                        href="{{ route('documentos.create', ['account_id' => $account->id]) }}"
                        class="text-xs font-medium text-[#9d1872] hover:underline"
                    >
                        Añadir documento
                    </a>
                </div>

                @if($docsGenerales->isEmpty())
                    <p class="text-sm text-slate-500">No hay documentos generales asociados.</p>
                @else
                    <ul class="space-y-2 text-sm">
                        @foreach($docsGenerales as $documento)
                            <li class="flex items-start justify-between gap-3">
                                <div>
                                    <a href="{{ route('documentos.download', $documento) }}" class="font-medium text-slate-900 hover:text-[#9d1872]">
                                        {{ $documento->titulo }}
                                    </a>
                                    <p class="text-xs text-slate-500">
                                        {{ $documento->tipo ?: 'Documento' }}
                                        @if($documento->fecha_documento) · {{ $documento->fecha_documento->format('d/m/Y') }} @endif
                                    </p>
                                </div>
                                <form method="POST" action="{{ route('documentos.destroy', $documento) }}" onsubmit="return confirm('¿Eliminar este documento?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-[11px] text-rose-600 hover:underline">Eliminar</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">
                        Documentos vinculados a operaciones
                    </h2>
                    <span class="text-xs text-slate-500">(Pedido / Petición / Solicitud)</span>
                </div>

                @if($docsVinculados->isEmpty())
                    <p class="text-sm text-slate-500">No hay documentos vinculados a operaciones.</p>
                @else
                    <ul class="space-y-2 text-sm">
                        @foreach($docsVinculados as $documento)
                            <li class="flex items-start justify-between gap-3">
                                <div>
                                    <a href="{{ route('documentos.download', $documento) }}" class="font-medium text-slate-900 hover:text-[#9d1872]">
                                        {{ $documento->titulo }}
                                    </a>
                                    <p class="text-xs text-slate-500">
                                        {{ $documento->tipo ?: 'Documento' }}
                                        @if($documento->fecha_documento) · {{ $documento->fecha_documento->format('d/m/Y') }} @endif

                                        @if($documento->pedido)
                                            · Pedido: <a class="underline" href="{{ route('pedidos.show', $documento->pedido) }}">{{ $documento->pedido->numero ?: ('#'.$documento->pedido->id) }}</a>
                                        @elseif($documento->peticion)
                                            · Petición: <a class="underline" href="{{ route('peticiones.show', $documento->peticion) }}">{{ $documento->peticion->titulo }}</a>
                                        @elseif($documento->solicitud)
                                            · Solicitud: <a class="underline" href="{{ route('solicitudes.show', $documento->solicitud) }}">{{ $documento->solicitud->titulo }}</a>
                                        @endif
                                    </p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <a
                                        href="{{ route('documentos.create', ['account_id' => $account->id, 'pedido_id' => $documento->pedido_id, 'peticion_id' => $documento->peticion_id, 'solicitud_id' => $documento->solicitud_id]) }}"
                                        class="text-[11px] text-slate-600 hover:underline"
                                    >
                                        Añadir otro
                                    </a>
                                    <form method="POST" action="{{ route('documentos.destroy', $documento) }}" onsubmit="return confirm('¿Eliminar este documento?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-[11px] text-rose-600 hover:underline">Eliminar</button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    @endif

    {{-- PESTAÑA: FACTURACIÓN --}}
    @if($activeTab === 'facturacion')
        <div class="mt-6 max-w-4xl space-y-6">
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
                    Datos de facturación
                </h2>

                <dl class="grid gap-4 md:grid-cols-2 text-sm">
                    <div class="md:col-span-2">
                        <dt class="text-slate-500">Razón social de facturación</dt>
                        <dd class="mt-1 text-slate-900">
                            {{ $account->billingValue('billing_legal_name', 'legal_name') ?? '—' }}
                            @if($account->billingIsInherited('billing_legal_name'))
                                <span class="ml-2 inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-600">Heredado de matriz</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-slate-500">Código cliente / Cuenta</dt>
                        <dd class="mt-1 text-slate-900">
                            {{ $account->billingValue('customer_code') ?? '—' }}
                            @if($account->billingIsInherited('customer_code'))
                                <span class="ml-2 inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-600">Heredado de matriz</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-slate-500">NIF / CIF</dt>
                        <dd class="mt-1 text-slate-900">
                            {{ $account->billingValue('tax_id') ?? '—' }}
                            @if($account->billingIsInherited('tax_id'))
                                <span class="ml-2 inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-600">Heredado de matriz</span>
                            @endif
                        </dd>
                    </div>

                    <div class="md:col-span-2">
                        <dt class="text-slate-500">Dirección fiscal</dt>
                        <dd class="mt-1 text-slate-900">
                            {{ $account->billingValue('billing_address') ?? '—' }}
                            @if($account->billingIsInherited('billing_address'))
                                <span class="ml-2 inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-600">Heredado de matriz</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-slate-500">Localidad fiscal</dt>
                        <dd class="mt-1 text-slate-900">
                            {{ $account->billingValue('billing_city') ?? '—' }}
                            @if($account->billingIsInherited('billing_city'))
                                <span class="ml-2 inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-600">Heredado de matriz</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-slate-500">Provincia fiscal</dt>
                        <dd class="mt-1 text-slate-900">
                            {{ $account->billingValue('billing_state') ?? '—' }}
                            @if($account->billingIsInherited('billing_state'))
                                <span class="ml-2 inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-600">Heredado de matriz</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-slate-500">CP fiscal</dt>
                        <dd class="mt-1 text-slate-900">
                            {{ $account->billingValue('billing_postal_code') ?? '—' }}
                            @if($account->billingIsInherited('billing_postal_code'))
                                <span class="ml-2 inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-600">Heredado de matriz</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-slate-500">País fiscal</dt>
                        <dd class="mt-1 text-slate-900">
                            {{ $account->billingValue('billing_country') ?? '—' }}
                            @if($account->billingIsInherited('billing_country'))
                                <span class="ml-2 inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-600">Heredado de matriz</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-slate-500">Email de facturación</dt>
                        <dd class="mt-1">
                            @if($account->billingValue('billing_email'))
                                <a href="mailto:{{ $account->billingValue('billing_email') }}" class="text-[#9d1872] hover:underline">
                                    {{ $account->billingValue('billing_email') }}
                                </a>
                                @if($account->billingIsInherited('billing_email'))
                                    <span class="ml-2 inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-600">Heredado de matriz</span>
                                @endif
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-slate-500">Cliente facturable</dt>
                        <dd class="mt-1">
                            @if($account->is_billable)
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700">
                                    Sí
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">
                                    No
                                </span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-slate-500">Problemas en cobro</dt>
                        <dd class="mt-1">
                            @if($account->billing_has_payment_issues)
                                <span class="inline-flex items-center rounded-full bg-rose-50 px-2 py-0.5 text-[11px] font-medium text-rose-700">
                                    Sí, revisar antes de facturar
                                </span>
                            @else
                                <span class="text-slate-900">No</span>
                            @endif
                        </dd>
                    </div>

                    <div class="md:col-span-2">
                        <dt class="text-slate-500">Comentarios de facturación</dt>
                        <dd class="mt-1 text-slate-900 whitespace-pre-line">
                            {{ $account->billing_notes ?? '—' }}
                        </dd>
                    </div>

                    <div class="md:col-span-2">
                        <dt class="text-slate-500">Contacto de facturación</dt>
                        <dd class="mt-1 text-slate-900">
                            @php
                                $billingContact = $account->billingContact();
                    

            // Añadir contadores a las pestañas (ej. "Facturas (4)")
            if (isset($tabCounts)) {
                $countKeys = ['contactos', 'actividad', 'operaciones', 'facturas', 'documentos'];
                foreach ($countKeys as $k) {
                    if (isset($tabs[$k])) {
                        $tabs[$k] = $tabs[$k].' ('.($tabCounts[$k] ?? 0).')';
                    }
                }
            }
        @endphp

                            @if($billingContact)
                                <a href="{{ route('contacts.show', $billingContact) }}"
                                   class="text-[#9d1872] hover:underline">
                                    {{ $billingContact->name }}
                                </a>
                                @if($account->billingIsInherited('billing_email'))
                                    <span class="ml-2 inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-600">Heredado de matriz</span>
                                @endif
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </section>
        </div>
    @endif

    {{-- PESTAÑA: INFORMACIÓN DEL SISTEMA --}}
    @if($activeTab === 'sistema')
        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            {{-- Columna izquierda (2/3): historial de auditoría --}}
            <div class="space-y-6 lg:col-span-2">
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
                        Historial de cambios
                    </h2>

                    @if($account->audits->isNotEmpty())
                        <ul class="divide-y divide-slate-100 text-xs">
                            @foreach($account->audits->sortByDesc('created_at')->take(20) as $audit)
                                <li class="py-2 flex items-start justify-between gap-3">
                                    <div>
                                        <div class="font-medium text-slate-900">
                                            {{ $audit->field }}
                                        </div>
                                        <div class="mt-0.5 text-slate-500">
                                            {{ $audit->old_value ?? '—' }} → {{ $audit->new_value ?? '—' }}
                                        </div>
                                    </div>
                                    <div class="text-right text-slate-400">
                                        <div>
                                            {{ $audit->created_at->format('d/m/Y H:i') }}
                                        </div>
                                        @if($audit->user)
                                            <div>por {{ $audit->user->name }}</div>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-xs text-slate-500">
                            Todavía no hay cambios registrados en esta cuenta.
                        </p>
                    @endif
                </section>
            </div>

            {{-- Columna derecha (1/3): info básica de sistema --}}
            <div class="space-y-6">
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
                        Información del sistema
                    </h2>

                    <dl class="space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500">Fecha de alta</dt>
                            <dd class="text-slate-900">
                                {{ optional($account->created_at)->format('d/m/Y H:i') ?? '—' }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500">Última actualización</dt>
                            <dd class="text-slate-900">
                                {{ optional($account->updated_at)->format('d/m/Y H:i') ?? '—' }}
                            </dd>
                        </div>

                        {{-- Aquí más adelante:
                            - Fecha de inactivación
                            - Usuario creador
                            - Usuario última modificación
                            cuando tengáis esos datos --}}
                    </dl>
                </section>
            </div>
        </div>
    @endif
</div>
@endsection
