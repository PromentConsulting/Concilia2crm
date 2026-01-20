@extends('layouts.app')

@section('title','Dashboard')

@section('content')
    @php
        $recentAccounts = $recentAccounts ?? collect();
        $recentContacts = $recentContacts ?? collect();
        $recentSolicitudes = $recentSolicitudes ?? collect();
        $recentTareas = $recentTareas ?? collect();
        $dashboardLayout = is_array($dashboardLayout ?? null) ? $dashboardLayout : [];
    @endphp
    <div class="grid gap-8">
        <header class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-white px-6 py-6 shadow-sm">
            <div>
                <p class="text-sm font-medium text-slate-500">Bienvenido/a</p>
                <h1 class="text-2xl font-semibold text-slate-900">{{ auth()->user()->name ?? auth()->user()->email }}</h1>
            </div>
            <div class="flex flex-wrap items-center gap-3 text-sm">
                <a href="{{ route('accounts.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 font-medium text-white shadow hover:bg-slate-800">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14M5 12h14" />
                    </svg>
                    Nueva cuenta
                </a>
                <a href="{{ route('contacts.create') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-4 py-2 font-medium text-slate-700 transition-colors hover:bg-slate-100">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                    Nuevo contacto
                </a>
            </div>
        </header>

        <section class="rounded-2xl bg-white p-6 shadow-sm" data-dashboard-controls>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Mi dashboard</h2>
                    <p class="text-sm text-slate-500">Personaliza las filas, columnas y widgets que quieres ver.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <button type="button" data-edit-toggle class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 font-medium text-slate-700 hover:bg-slate-100">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 20h9" />
                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" />
                        </svg>
                        <span data-edit-label>Editar dashboard</span>
                    </button>
                </div>
            </div>
            <div class="mt-6 hidden flex-wrap items-center gap-3 rounded-xl border border-dashed border-slate-200 bg-slate-50 p-4 text-sm" data-edit-panel>
                <div class="flex flex-wrap items-center gap-2">
                    <label class="text-xs font-medium text-slate-500">
                        Columnas de la fila
                        <select data-add-row-columns class="ml-2 rounded-lg border border-slate-200 px-2 py-1 text-sm text-slate-700">
                            @foreach ([1, 2, 3, 4] as $columnsOption)
                                <option value="{{ $columnsOption }}">{{ $columnsOption }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button type="button" data-add-row class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 font-medium text-slate-700 hover:bg-slate-100">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 5v14M5 12h14" />
                        </svg>
                        Añadir fila
                    </button>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <label class="text-xs font-medium text-slate-500">
                        Fila
                        <select data-add-widget-row class="ml-2 rounded-lg border border-slate-200 px-2 py-1 text-sm text-slate-700">
                            @foreach (range(1, max(count($dashboardLayout), 1)) as $rowOption)
                                <option value="{{ $rowOption }}">{{ $rowOption }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-xs font-medium text-slate-500">
                        Widget
                        <select data-add-widget-type class="ml-2 rounded-lg border border-slate-200 px-2 py-1 text-sm text-slate-700">
                            <option value="account_count">Cuentas totales</option>
                            <option value="contact_count">Contactos totales</option>
                            <option value="solicitudes_pendientes">Solicitudes activas</option>
                            <option value="tareas_pendientes">Mis tareas pendientes</option>
                            <option value="quick_links">Acceso rápido</option>
                            <option value="resources">Recursos útiles</option>
                            <option value="recent_accounts">Últimas cuentas</option>
                            <option value="recent_contacts">Últimos contactos</option>
                            <option value="recent_solicitudes">Últimas solicitudes</option>
                            <option value="recent_tareas">Mis tareas recientes</option>
                        </select>
                    </label>
                    <button type="button" data-add-widget class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 font-medium text-slate-700 hover:bg-slate-100">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 12h18" />
                            <path d="M12 3v18" />
                        </svg>
                        Añadir widget
                    </button>
                </div>
                <form method="POST" action="{{ route('dashboard.layout.update') }}" class="ml-auto">
                    @csrf
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="dashboard_layout" value="{{ json_encode($dashboardLayout) }}" data-layout-input>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-3 py-2 font-medium text-white shadow hover:bg-slate-800">
                        Guardar cambios
                    </button>
                </form>
            </div>
        </section>

        @php
            $columnClasses = [
                1 => 'grid-cols-1',
                2 => 'grid-cols-1 md:grid-cols-2',
                3 => 'grid-cols-1 md:grid-cols-3',
                4 => 'grid-cols-1 md:grid-cols-2 xl:grid-cols-4',
            ];
        @endphp

        <div data-dashboard-grid class="grid gap-8">
            @foreach ($dashboardLayout as $rowIndex => $row)
                <section class="space-y-4" data-dashboard-row data-row-index="{{ $rowIndex }}" data-columns="{{ $row['columns'] ?? 1 }}">
                    <div class="flex flex-wrap items-center justify-between gap-3 text-sm text-slate-500">
                        <p>Fila {{ $rowIndex + 1 }}</p>
                        <div class="hidden flex-wrap items-center gap-2" data-row-controls>
                            <label class="text-xs font-medium text-slate-500">
                                Columnas
                                <select data-row-columns class="ml-2 rounded-full border border-slate-200 px-2 py-1 text-xs text-slate-600">
                                    @foreach ([1, 2, 3, 4] as $columnsOption)
                                        <option value="{{ $columnsOption }}" @selected(($row['columns'] ?? 1) === $columnsOption)>{{ $columnsOption }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <button type="button" data-row-delete class="rounded-full border border-red-200 px-3 py-1 text-xs font-medium text-red-600 hover:bg-red-50">Eliminar fila</button>
                        </div>
                    </div>
                    <div class="grid gap-6 {{ $columnClasses[$row['columns']] ?? $columnClasses[2] }}" data-row-grid>
                        @foreach ($row['widgets'] as $widget)
                        @switch($widget)
                            @case('account_count')
                        <div class="rounded-2xl bg-white p-6 shadow-sm relative" data-widget="account_count">
                            <button type="button" data-widget-delete class="absolute right-3 top-3 hidden rounded-full border border-slate-200 bg-white p-1 text-slate-500 hover:text-slate-700" aria-label="Eliminar widget">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18" />
                                    <path d="M8 6V4h8v2" />
                                    <path d="M19 6l-1 14H6L5 6" />
                                </svg>
                            </button>
                            <p class="text-sm font-medium text-slate-500">Cuentas totales</p>
                            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($accountCount) }}</p>
                            <a href="{{ route('accounts.index') }}" class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-slate-900">
                                        Ver todas
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                                @break
                            @case('contact_count')
                        <div class="rounded-2xl bg-white p-6 shadow-sm relative" data-widget="contact_count">
                            <button type="button" data-widget-delete class="absolute right-3 top-3 hidden rounded-full border border-slate-200 bg-white p-1 text-slate-500 hover:text-slate-700" aria-label="Eliminar widget">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18" />
                                    <path d="M8 6V4h8v2" />
                                    <path d="M19 6l-1 14H6L5 6" />
                                </svg>
                            </button>
                            <p class="text-sm font-medium text-slate-500">Contactos totales</p>
                            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($contactCount) }}</p>
                            <a href="{{ route('contacts.index') }}" class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-slate-900">
                                        Ver todos
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                                @break
                            @case('solicitudes_pendientes')
                        <div class="rounded-2xl bg-white p-6 shadow-sm relative" data-widget="solicitudes_pendientes">
                            <button type="button" data-widget-delete class="absolute right-3 top-3 hidden rounded-full border border-slate-200 bg-white p-1 text-slate-500 hover:text-slate-700" aria-label="Eliminar widget">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18" />
                                    <path d="M8 6V4h8v2" />
                                    <path d="M19 6l-1 14H6L5 6" />
                                </svg>
                            </button>
                            <p class="text-sm font-medium text-slate-500">Solicitudes activas</p>
                            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($solicitudesPendientes) }}</p>
                            <p class="mt-3 text-sm text-slate-600">Pendiente · Asignado · En curso · En espera</p>
                                </div>
                                @break
                            @case('tareas_pendientes')
                        <div class="rounded-2xl bg-white p-6 shadow-sm relative" data-widget="tareas_pendientes">
                            <button type="button" data-widget-delete class="absolute right-3 top-3 hidden rounded-full border border-slate-200 bg-white p-1 text-slate-500 hover:text-slate-700" aria-label="Eliminar widget">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18" />
                                    <path d="M8 6V4h8v2" />
                                    <path d="M19 6l-1 14H6L5 6" />
                                </svg>
                            </button>
                            <p class="text-sm font-medium text-slate-500">Mis tareas pendientes</p>
                            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($tareasPendientes) }}</p>
                            <p class="mt-3 text-sm text-slate-600">Asignadas a ti y en estado pendiente.</p>
                                </div>
                                @break
                            @case('quick_links')
                        <div class="rounded-2xl bg-slate-900 p-6 text-white shadow-sm relative" data-widget="quick_links">
                            <button type="button" data-widget-delete class="absolute right-3 top-3 hidden rounded-full border border-slate-700 bg-slate-900 p-1 text-slate-200 hover:text-white" aria-label="Eliminar widget">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18" />
                                    <path d="M8 6V4h8v2" />
                                    <path d="M19 6l-1 14H6L5 6" />
                                </svg>
                            </button>
                            <p class="text-sm font-medium text-slate-200">Acceso rápido</p>
                            <ul class="mt-4 space-y-3 text-sm">
                                <li><a href="{{ route('accounts.index') }}" class="inline-flex items-center gap-2 hover:text-slate-100">Gestión de cuentas</a></li>
                                        <li><a href="{{ route('contacts.index') }}" class="inline-flex items-center gap-2 hover:text-slate-100">Gestión de contactos</a></li>
                                        <li><a href="{{ url('/api/v1/accounts') }}" target="_blank" class="inline-flex items-center gap-2 hover:text-slate-100">API de cuentas</a></li>
                                        <li><a href="{{ url('/api/v1/contacts') }}" target="_blank" class="inline-flex items-center gap-2 hover:text-slate-100">API de contactos</a></li>
                                    </ul>
                                </div>
                                @break
                            @case('resources')
                        <div class="rounded-2xl bg-white p-6 shadow-sm relative" data-widget="resources">
                            <button type="button" data-widget-delete class="absolute right-3 top-3 hidden rounded-full border border-slate-200 bg-white p-1 text-slate-500 hover:text-slate-700" aria-label="Eliminar widget">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18" />
                                    <path d="M8 6V4h8v2" />
                                    <path d="M19 6l-1 14H6L5 6" />
                                </svg>
                            </button>
                            <p class="text-sm font-medium text-slate-500">Recursos útiles</p>
                            <ul class="mt-4 space-y-3 text-sm text-slate-600">
                                <li>Revisa las cuentas recién creadas para validar la información.</li>
                                        <li>Actualiza los estados de contacto para mantener el embudo al día.</li>
                                    </ul>
                                </div>
                                @break
                            @case('recent_accounts')
                        <div class="rounded-2xl bg-white p-6 shadow-sm relative" data-widget="recent_accounts">
                            <button type="button" data-widget-delete class="absolute right-3 top-3 hidden rounded-full border border-slate-200 bg-white p-1 text-slate-500 hover:text-slate-700" aria-label="Eliminar widget">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18" />
                                    <path d="M8 6V4h8v2" />
                                    <path d="M19 6l-1 14H6L5 6" />
                                </svg>
                            </button>
                            <div class="flex items-center justify-between gap-4">
                                <h2 class="text-lg font-semibold text-slate-900">Últimas cuentas</h2>
                                <a href="{{ route('accounts.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Ver todas</a>
                                    </div>
                                    <div class="mt-4 divide-y divide-slate-100">
                                        @forelse ($recentAccounts as $account)
                                            <article class="flex items-center justify-between gap-4 py-4">
                                                <div>
                                                    <h3 class="font-medium text-slate-900">{{ $account->name }}</h3>
                                                    <p class="text-sm text-slate-500">{{ $account->city ?? 'Sin ciudad' }} · {{ $account->country ?? 'Sin país' }}</p>
                                                </div>
                                                <div class="text-right text-sm text-slate-500">
                                                    <p>{{ $account->contacts_count }} contactos</p>
                                                    <time datetime="{{ optional($account->created_at)->toIso8601String() }}">{{ optional($account->created_at)->diffForHumans() }}</time>
                                                </div>
                                            </article>
                                        @empty
                                            <p class="py-6 text-sm text-slate-500">Todavía no hay cuentas creadas.</p>
                                        @endforelse
                                    </div>
                                </div>
                                @break
                            @case('recent_contacts')
                        <div class="rounded-2xl bg-white p-6 shadow-sm relative" data-widget="recent_contacts">
                            <button type="button" data-widget-delete class="absolute right-3 top-3 hidden rounded-full border border-slate-200 bg-white p-1 text-slate-500 hover:text-slate-700" aria-label="Eliminar widget">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18" />
                                    <path d="M8 6V4h8v2" />
                                    <path d="M19 6l-1 14H6L5 6" />
                                </svg>
                            </button>
                            <div class="flex items-center justify-between gap-4">
                                <h2 class="text-lg font-semibold text-slate-900">Últimos contactos</h2>
                                <a href="{{ route('contacts.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Ver todos</a>
                                    </div>
                                    <div class="mt-4 divide-y divide-slate-100">
                                        @forelse ($recentContacts as $contact)
                                            <article class="flex items-center justify-between gap-4 py-4">
                                                <div>
                                                    <h3 class="font-medium text-slate-900">{{ $contact->full_name ?? trim($contact->first_name . ' ' . $contact->last_name) }}</h3>
                                                    <p class="text-sm text-slate-500">
                                                        {{ $contact->email ?? 'Sin email' }}
                                                        @if ($contact->account)
                                                            · {{ $contact->account->name }}
                                                        @endif
                                                    </p>
                                                </div>
                                                <div class="text-right text-sm text-slate-500">
                                                    <p>{{ $contact->phone ?? 'Sin teléfono' }}</p>
                                                    <time datetime="{{ optional($contact->created_at)->toIso8601String() }}">{{ optional($contact->created_at)->diffForHumans() }}</time>
                                                </div>
                                            </article>
                                        @empty
                                            <p class="py-6 text-sm text-slate-500">Todavía no hay contactos creados.</p>
                                        @endforelse
                                    </div>
                                </div>
                                @break
                            @case('recent_solicitudes')
                        <div class="rounded-2xl bg-white p-6 shadow-sm relative" data-widget="recent_solicitudes">
                            <button type="button" data-widget-delete class="absolute right-3 top-3 hidden rounded-full border border-slate-200 bg-white p-1 text-slate-500 hover:text-slate-700" aria-label="Eliminar widget">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18" />
                                    <path d="M8 6V4h8v2" />
                                    <path d="M19 6l-1 14H6L5 6" />
                                </svg>
                            </button>
                            <div class="flex items-center justify-between gap-4">
                                <h2 class="text-lg font-semibold text-slate-900">Últimas solicitudes</h2>
                                <a href="{{ route('solicitudes.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Ver todas</a>
                                    </div>
                                    <div class="mt-4 divide-y divide-slate-100">
                                        @forelse ($recentSolicitudes as $solicitud)
                                            <article class="flex items-center justify-between gap-4 py-4">
                                                <div>
                                                    <h3 class="font-medium text-slate-900">{{ $solicitud->titulo ?? 'Solicitud sin título' }}</h3>
                                                    <p class="text-sm text-slate-500">{{ $solicitud->estado }}</p>
                                                </div>
                                                <div class="text-right text-sm text-slate-500">
                                                    <time datetime="{{ optional($solicitud->created_at)->toIso8601String() }}">{{ optional($solicitud->created_at)->diffForHumans() }}</time>
                                                </div>
                                            </article>
                                        @empty
                                            <p class="py-6 text-sm text-slate-500">Todavía no hay solicitudes creadas.</p>
                                        @endforelse
                                    </div>
                                </div>
                                @break
                            @case('recent_tareas')
                        <div class="rounded-2xl bg-white p-6 shadow-sm relative" data-widget="recent_tareas">
                            <button type="button" data-widget-delete class="absolute right-3 top-3 hidden rounded-full border border-slate-200 bg-white p-1 text-slate-500 hover:text-slate-700" aria-label="Eliminar widget">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18" />
                                    <path d="M8 6V4h8v2" />
                                    <path d="M19 6l-1 14H6L5 6" />
                                </svg>
                            </button>
                            <div class="flex items-center justify-between gap-4">
                                <h2 class="text-lg font-semibold text-slate-900">Mis tareas recientes</h2>
                                <a href="{{ route('tareas.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Ver todas</a>
                                    </div>
                                    <div class="mt-4 divide-y divide-slate-100">
                                        @forelse ($recentTareas as $tarea)
                                            <article class="flex items-center justify-between gap-4 py-4">
                                                <div>
                                                    <h3 class="font-medium text-slate-900">{{ $tarea->titulo ?? 'Tarea sin título' }}</h3>
                                                    <p class="text-sm text-slate-500">{{ $tarea->estado }}</p>
                                                </div>
                                                <div class="text-right text-sm text-slate-500">
                                                    <time datetime="{{ optional($tarea->created_at)->toIso8601String() }}">{{ optional($tarea->created_at)->diffForHumans() }}</time>
                                                </div>
                                            </article>
                                        @empty
                                            <p class="py-6 text-sm text-slate-500">Todavía no hay tareas asignadas.</p>
                                        @endforelse
                                    </div>
                                </div>
                                @break
                            @default
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-6 text-center text-sm text-slate-500 relative" data-widget="{{ $widget }}">
                            <button type="button" data-widget-delete class="absolute right-3 top-3 hidden rounded-full border border-slate-200 bg-white p-1 text-slate-500 hover:text-slate-700" aria-label="Eliminar widget">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18" />
                                    <path d="M8 6V4h8v2" />
                                    <path d="M19 6l-1 14H6L5 6" />
                                </svg>
                            </button>
                            Widget disponible próximamente.
                        </div>
                        @endswitch
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const editToggle = document.querySelector('[data-edit-toggle]');
            const editPanel = document.querySelector('[data-edit-panel]');
            const dashboardGrid = document.querySelector('[data-dashboard-grid]');
            const layoutInput = document.querySelector('[data-layout-input]');
            const addRowButton = document.querySelector('[data-add-row]');
            const addRowColumns = document.querySelector('[data-add-row-columns]');
            const addWidgetButton = document.querySelector('[data-add-widget]');
            const addWidgetRow = document.querySelector('[data-add-widget-row]');
            const addWidgetType = document.querySelector('[data-add-widget-type]');

            const widgetLabels = {
                account_count: 'Cuentas totales',
                contact_count: 'Contactos totales',
                solicitudes_pendientes: 'Solicitudes activas',
                tareas_pendientes: 'Mis tareas pendientes',
                quick_links: 'Acceso rápido',
                resources: 'Recursos útiles',
                recent_accounts: 'Últimas cuentas',
                recent_contacts: 'Últimos contactos',
                recent_solicitudes: 'Últimas solicitudes',
                recent_tareas: 'Mis tareas recientes',
            };

            if (!editToggle || !editPanel || !dashboardGrid || !layoutInput) {
                return;
            }

            const editLabel = document.querySelector('[data-edit-label]');

            const applyEditState = (widget, isEditing) => {
                widget.setAttribute('draggable', isEditing ? 'true' : 'false');
                widget.classList.toggle('ring-2', isEditing);
                widget.classList.toggle('ring-slate-200', isEditing);
                const deleteButton = widget.querySelector('[data-widget-delete]');
                if (deleteButton) {
                    deleteButton.classList.toggle('hidden', !isEditing);
                }
            };

            const toggleEditMode = () => {
                const isEditing = editPanel.classList.toggle('hidden') === false;
                if (editLabel) {
                    editLabel.textContent = isEditing ? 'Salir de edición' : 'Editar dashboard';
                }
                dashboardGrid.querySelectorAll('[data-row-controls]').forEach((controls) => {
                    controls.classList.toggle('hidden', !isEditing);
                });
                dashboardGrid.querySelectorAll('[data-widget]').forEach((widget) => {
                    applyEditState(widget, isEditing);
                });
                updateLayoutInput();
            };

            const updateLayoutInput = () => {
                const rows = Array.from(dashboardGrid.querySelectorAll('[data-dashboard-row]')).map((row) => {
                    const columns = Number(row.dataset.columns || 1);
                    const widgets = Array.from(row.querySelectorAll('[data-widget]')).map((widget) => widget.dataset.widget);
                    return { columns, widgets };
                });
                layoutInput.value = JSON.stringify(rows);
                refreshRowOptions();
            };

            const refreshRowOptions = () => {
                const rows = dashboardGrid.querySelectorAll('[data-dashboard-row]');
                addWidgetRow.innerHTML = '';
                rows.forEach((row, index) => {
                    const option = document.createElement('option');
                    option.value = String(index + 1);
                    option.textContent = String(index + 1);
                    addWidgetRow.append(option);
                });
            };

            const createWidgetPlaceholder = (widgetKey) => {
                const card = document.createElement('div');
                card.className = 'rounded-2xl border border-dashed border-slate-200 bg-white p-6 text-sm text-slate-600 relative';
                card.dataset.widget = widgetKey;
                card.innerHTML = `
                    <button type="button" data-widget-delete class="absolute right-3 top-3 hidden rounded-full border border-slate-200 bg-white p-1 text-slate-500 hover:text-slate-700" aria-label="Eliminar widget">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 6h18" />
                            <path d="M8 6V4h8v2" />
                            <path d="M19 6l-1 14H6L5 6" />
                        </svg>
                    </button>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Widget</p>
                    <p class="mt-2 text-base font-semibold text-slate-900">${widgetLabels[widgetKey] ?? 'Widget'}</p>
                    <p class="mt-2 text-sm text-slate-500">Se mostrará al guardar el dashboard.</p>
                `;
                return card;
            };

            const attachRowHandlers = (row) => {
                const rowGrid = row.querySelector('[data-row-grid]');
                const deleteButton = row.querySelector('[data-row-delete]');
                const columnsSelect = row.querySelector('[data-row-columns]');

                if (deleteButton) {
                    deleteButton.addEventListener('click', () => {
                        row.remove();
                        renumberRows();
                        updateLayoutInput();
                    });
                }

                if (columnsSelect) {
                    columnsSelect.addEventListener('change', (event) => {
                        const columns = Number(event.target.value || 1);
                        row.dataset.columns = String(columns);
                        rowGrid.className = `grid gap-6 ${columnClasses[columns] ?? columnClasses[2]}`;
                        updateLayoutInput();
                    });
                }

                if (rowGrid) {
                    rowGrid.addEventListener('dragover', (event) => {
                        event.preventDefault();
                    });
                    rowGrid.addEventListener('drop', (event) => {
                        event.preventDefault();
                        const widgetKey = event.dataTransfer.getData('text/widget');
                        const widgetId = event.dataTransfer.getData('text/widget-id');
                        if (widgetId) {
                            const dragged = document.getElementById(widgetId);
                            if (dragged) {
                                rowGrid.append(dragged);
                            }
                        } else if (widgetKey) {
                            rowGrid.append(createWidgetPlaceholder(widgetKey));
                        }
                        rowGrid.querySelectorAll('[data-widget]').forEach((widget) => {
                            applyEditState(widget, !editPanel.classList.contains('hidden'));
                        });
                        attachWidgetDeleteHandlers(rowGrid);
                        updateLayoutInput();
                    });
                }
            };

            const attachWidgetDeleteHandlers = (scope) => {
                const buttons = (scope || document).querySelectorAll('[data-widget-delete]');
                buttons.forEach((button) => {
                    button.addEventListener('click', (event) => {
                        event.preventDefault();
                        const widget = button.closest('[data-widget]');
                        if (widget) {
                            widget.remove();
                            updateLayoutInput();
                        }
                    });
                });
            };

            const renumberRows = () => {
                dashboardGrid.querySelectorAll('[data-dashboard-row]').forEach((row, index) => {
                    row.dataset.rowIndex = String(index);
                    const label = row.querySelector('p');
                    if (label) {
                        label.textContent = `Fila ${index + 1}`;
                    }
                });
            };

            const columnClasses = @json($columnClasses);

            editToggle.addEventListener('click', toggleEditMode);

            addRowButton?.addEventListener('click', () => {
                const columns = Number(addRowColumns?.value || 2);
                const rowIndex = dashboardGrid.querySelectorAll('[data-dashboard-row]').length;
                const row = document.createElement('section');
                row.className = 'space-y-4';
                row.dataset.dashboardRow = '';
                row.dataset.rowIndex = String(rowIndex);
                row.dataset.columns = String(columns);
                row.innerHTML = `
                    <div class="flex flex-wrap items-center justify-between gap-3 text-sm text-slate-500">
                        <p>Fila ${rowIndex + 1}</p>
                        <div class="flex flex-wrap items-center gap-2" data-row-controls>
                            <label class="text-xs font-medium text-slate-500">
                                Columnas
                                <select data-row-columns class="ml-2 rounded-full border border-slate-200 px-2 py-1 text-xs text-slate-600">
                                    ${[1, 2, 3, 4].map((value) => `<option value="${value}" ${value === columns ? 'selected' : ''}>${value}</option>`).join('')}
                                </select>
                            </label>
                            <button type="button" data-row-delete class="rounded-full border border-red-200 px-3 py-1 text-xs font-medium text-red-600 hover:bg-red-50">Eliminar fila</button>
                        </div>
                    </div>
                    <div class="grid gap-6 ${columnClasses[columns] ?? columnClasses[2]}" data-row-grid></div>
                `;
                dashboardGrid.append(row);
                attachRowHandlers(row);
                updateLayoutInput();
            });

            addWidgetButton?.addEventListener('click', () => {
                const rowIndex = Number(addWidgetRow?.value || 1) - 1;
                const widgetKey = addWidgetType?.value;
                const rows = dashboardGrid.querySelectorAll('[data-dashboard-row]');
                const targetRow = rows[rowIndex];
                if (!targetRow || !widgetKey) {
                    return;
                }
                const rowGrid = targetRow.querySelector('[data-row-grid]');
                if (rowGrid) {
                    const widget = createWidgetPlaceholder(widgetKey);
                    rowGrid.append(widget);
                    applyEditState(widget, !editPanel.classList.contains('hidden'));
                    attachWidgetDeleteHandlers(rowGrid);
                    updateLayoutInput();
                }
            });

            dashboardGrid.querySelectorAll('[data-dashboard-row]').forEach((row) => {
                attachRowHandlers(row);
            });

            attachWidgetDeleteHandlers(dashboardGrid);
            let widgetCounter = 0;
            dashboardGrid.addEventListener('dragstart', (event) => {
                const target = event.target.closest('[data-widget]');
                if (!target || target.getAttribute('draggable') !== 'true') {
                    return;
                }
                widgetCounter += 1;
                const widgetId = target.id || `widget-${widgetCounter}`;
                target.id = widgetId;
                event.dataTransfer.setData('text/widget-id', widgetId);
                event.dataTransfer.effectAllowed = 'move';
            });

            updateLayoutInput();
        });
    </script>
@endsection