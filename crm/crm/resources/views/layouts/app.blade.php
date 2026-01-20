<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'CRM')</title>

    {{-- Favicons --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/favicon-16x16.png') }}">
    <link rel="shortcut icon" href="{{ asset('assets/favicon.ico') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('assets/theme.css') }}">

    {{-- Alpine --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full bg-slate-50 text-slate-900">
@php
    $user = Auth::user();

    $isDashboard   = request()->routeIs('dashboard');

    $isAccounts    = request()->routeIs('accounts.*');
    $isContacts    = request()->routeIs('contacts.*');
    $isCampaigns   = request()->routeIs('campaigns.*');
    $isSolicitudes = request()->routeIs('solicitudes.*');
    $isPeticiones  = request()->routeIs('peticiones.*');
    $isPedidos     = request()->routeIs('pedidos.*');
    $isFacturas    = request()->routeIs('facturas.*');
    $isCatalogo    = request()->routeIs('catalogo.*');
    $isTareas      = request()->routeIs('tareas.*');
    $isDocumentos  = request()->routeIs('documentos.*');

    $isInformes    = request()->routeIs('informes.*');
    $isIntegraciones = request()->routeIs('integraciones.*');
    $isAlertas     = request()->routeIs('alertas.*');
    $isAccesos     = request()->routeIs('accesos.*');
    $isConfiguracion = request()->routeIs('configuracion.*');

    $isUsuarios    = request()->routeIs('usuarios.*');
    $isRoles       = request()->routeIs('roles.*');
@endphp

<div x-data="{ sidebarCollapsed: false, alertsOpen: false }" class="min-h-screen flex">

    {{-- SIDEBAR --}}
    <aside
        class="relative bg-white border-r border-slate-200 flex flex-col transition-all duration-200 ease-out"
        :class="sidebarCollapsed ? 'w-16' : 'w-64'"
    >
        {{-- Logo --}}
        <div
            class="flex items-center px-4 py-4 border-b border-slate-200 transition-all duration-200"
            :class="sidebarCollapsed ? 'justify-center px-2' : 'gap-2'"
        >
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-2 w-full"
               :class="sidebarCollapsed ? 'justify-center' : ''">
                <img
                    src="{{ asset('assets/logo-full.webp') }}"
                    alt="Propuestas Suite"
                    class="h-8 max-w-[150px]"
                    x-show="!sidebarCollapsed"
                    x-cloak
                >
                <img
                    src="{{ asset('assets/logo-icon.webp') }}"
                    alt="Propuestas Suite"
                    class="h-8 w-8"
                    x-show="sidebarCollapsed"
                    x-cloak
                >
            </a>
        </div>

        {{-- NAVEGACIÓN --}}
        <nav class="flex-1 overflow-y-auto px-2 py-4 text-sm space-y-4">

            {{-- Sección: Principal --}}
            <div>
                <p class="px-3 mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400"
                   x-show="!sidebarCollapsed"
                   x-cloak>
                    Principal
                </p>
                <a
                    href="{{ route('dashboard') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2 transition-colors
                        {{ $isDashboard ? 'bg-[#9d1872]/10 text-[#9d1872] font-semibold' : 'text-slate-700 hover:bg-slate-100' }}"
                    :class="sidebarCollapsed ? 'justify-center' : ''"
                >
                    <svg class="h-4 w-4 text-[#9d1872]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 12h7V4H3v8zm11 8h7V4h-7v16zM3 20h7v-6H3v6z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-cloak>Inicio</span>
                </a>
            </div>

            <div class="border-t border-slate-200"></div>

            {{-- Sección: CRM --}}
            <div>
                <p class="px-3 mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400"
                   x-show="!sidebarCollapsed"
                   x-cloak>
                    CRM
                </p>

                {{-- Cuentas --}}
                <a
                    href="{{ route('accounts.index') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2 transition-colors
                        {{ $isAccounts ? 'bg-[#9d1872]/10 text-[#9d1872] font-semibold' : 'text-slate-700 hover:bg-slate-100' }}"
                    :class="sidebarCollapsed ? 'justify-center' : ''"
                >
                    <svg class="h-4 w-4 text-[#9d1872]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4 6h16M4 10h16M4 14h10M4 18h7"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-cloak>Cuentas</span>
                </a>

                {{-- Contactos --}}
                <a
                    href="{{ route('contacts.index') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2 transition-colors
                        {{ $isContacts ? 'bg-[#9d1872]/10 text-[#9d1872] font-semibold' : 'text-slate-700 hover:bg-slate-100' }}"
                    :class="sidebarCollapsed ? 'justify-center' : ''"
                >
                    <svg class="h-4 w-4 text-[#9d1872]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M5.5 7a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zm9 8H6.5A3.5 3.5 0 003 18.5V20h14v-1.5A3.5 3.5 0 0014.5 15z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-cloak>Contactos</span>
                </a>

                {{-- Campañas --}}
                <a
                    href="{{ route('campaigns.index') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2 transition-colors
                        {{ $isCampaigns ? 'bg-[#9d1872]/10 text-[#9d1872] font-semibold' : 'text-slate-700 hover:bg-slate-100' }}"
                    :class="sidebarCollapsed ? 'justify-center' : ''"
                >
                    <svg class="h-4 w-4 text-[#9d1872]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4 6h16M7 12h10m-8 6h6"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-cloak>Campañas</span>
                </a>

                {{-- Solicitudes --}}
                <a
                    href="{{ route('solicitudes.index') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2 transition-colors
                        {{ $isSolicitudes ? 'bg-[#9d1872]/10 text-[#9d1872] font-semibold' : 'text-slate-700 hover:bg-slate-100' }}"
                    :class="sidebarCollapsed ? 'justify-center' : ''"
                >
                    <svg class="h-4 w-4 text-[#9d1872]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M5 4h14v12H9l-4 4V4z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-cloak>Solicitudes</span>
                </a>

                {{-- Peticiones --}}
                <a
                    href="{{ route('peticiones.index') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2 transition-colors
                        {{ $isPeticiones ? 'bg-[#9d1872]/10 text-[#9d1872] font-semibold' : 'text-slate-700 hover:bg-slate-100' }}"
                    :class="sidebarCollapsed ? 'justify-center' : ''"
                >
                    <svg class="h-4 w-4 text-[#9d1872]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4 4h16v4H4zm0 6h16v10H4z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-cloak>Peticiones</span>
                </a>

                {{-- Pedidos --}}
                <a
                    href="{{ route('pedidos.index') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2 transition-colors
                        {{ $isPedidos ? 'bg-[#9d1872]/10 text-[#9d1872] font-semibold' : 'text-slate-700 hover:bg-slate-100' }}"
                    :class="sidebarCollapsed ? 'justify-center' : ''"
                >
                    <svg class="h-4 w-4 text-[#9d1872]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M6 4h12l-1 14H7L6 4zm2 4h2m4 0h2"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-cloak>Pedidos</span>
                </a>

                {{-- Facturas --}}
                <a
                    href="{{ route('facturas.index') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2 transition-colors
                        {{ $isFacturas ? 'bg-[#9d1872]/10 text-[#9d1872] font-semibold' : 'text-slate-700 hover:bg-slate-100' }}"
                    :class="sidebarCollapsed ? 'justify-center' : ''"
                >
                    <svg class="h-4 w-4 text-[#9d1872]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M6 2h9l5 5v13a2 2 0 01-2 2H6a2 2 0 01-2-2V4a2 2 0 012-2zm9 0v5h5M8 11h8M8 15h8M8 19h5"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-cloak>Facturas</span>
                </a>
                
                {{-- Catálogo de servicios --}}
                <a
                    href="{{ route('catalogo.servicios.index') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2 transition-colors
                        {{ $isCatalogo ? 'bg-[#9d1872]/10 text-[#9d1872] font-semibold' : 'text-slate-700 hover:bg-slate-100' }}"
                    :class="sidebarCollapsed ? 'justify-center' : ''"
                >
                    <svg class="h-4 w-4 text-[#9d1872]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h10" />
                    </svg>
                    <span x-show="!sidebarCollapsed" x-cloak>Catálogo</span>
                </a>

                {{-- Tareas --}}
                <a
                    href="{{ route('tareas.index') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2 transition-colors
                        {{ $isTareas ? 'bg-[#9d1872]/10 text-[#9d1872] font-semibold' : 'text-slate-700 hover:bg-slate-100' }}"
                    :class="sidebarCollapsed ? 'justify-center' : ''"
                >
                    <svg class="h-4 w-4 text-[#9d1872]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M5 5h14v4H5zM5 11h14v4H5zM5 17h8v2H5z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-cloak>Actividades</span>
                </a>

                {{-- Documentos --}}
                <a
                    href="{{ route('documentos.index') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2 transition-colors
                        {{ $isDocumentos ? 'bg-[#9d1872]/10 text-[#9d1872] font-semibold' : 'text-slate-700 hover:bg-slate-100' }}"
                    :class="sidebarCollapsed ? 'justify-center' : ''"
                >
                    <svg class="h-4 w-4 text-[#9d1872]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M6 3h9l5 5v13H6zM15 3v5h5"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-cloak>Documentos</span>
                </a>
            </div>

            <div class="border-t border-slate-200"></div>

            {{-- Sección: Herramientas --}}
            <div>
                <p class="px-3 mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400"
                   x-show="!sidebarCollapsed"
                   x-cloak>
                    Herramientas
                </p>

                {{-- Informes --}}
                <a
                    href="{{ route('informes.index') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2 transition-colors
                        {{ $isInformes ? 'bg-[#9d1872]/10 text-[#9d1872] font-semibold' : 'text-slate-700 hover:bg-slate-100' }}"
                    :class="sidebarCollapsed ? 'justify-center' : ''"
                >
                    <svg class="h-4 w-4 text-[#9d1872]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4 19h16M5 17V7m5 10V5m5 12V9m5 10V4"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-cloak>Informes</span>
                </a>

                {{-- Integraciones --}}
                <a
                    href="{{ route('integraciones.index') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2 transition-colors
                        {{ $isIntegraciones ? 'bg-[#9d1872]/10 text-[#9d1872] font-semibold' : 'text-slate-700 hover:bg-slate-100' }}"
                    :class="sidebarCollapsed ? 'justify-center' : ''"
                >
                    <svg class="h-4 w-4 text-[#9d1872]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M7 8a4 4 0 118 0 4 4 0 01-8 0zm7 8l3 3m-3-3l-3 3"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-cloak>Integraciones</span>
                </a>

                {{-- Alertas --}}
                <a
                    href="{{ route('alertas.index') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2 transition-colors
                        {{ $isAlertas ? 'bg-[#9d1872]/10 text-[#9d1872] font-semibold' : 'text-slate-700 hover:bg-slate-100' }}"
                    :class="sidebarCollapsed ? 'justify-center' : ''"
                >
                    <svg class="h-4 w-4 text-[#9d1872]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 3l7 13H5l7-13zm0 14v2m0 2h.01"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-cloak>Alertas</span>
                </a>

                {{-- Accesos --}}
                <a
                    href="{{ route('accesos.index') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2 transition-colors
                        {{ $isAccesos ? 'bg-[#9d1872]/10 text-[#9d1872] font-semibold' : 'text-slate-700 hover:bg-slate-100' }}"
                    :class="sidebarCollapsed ? 'justify-center' : ''"
                >
                    <svg class="h-4 w-4 text-[#9d1872]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M5 4h14v4H5zm2 5h10v11H7z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-cloak>Accesos</span>
                </a>
            </div>

            <div class="border-t border-slate-200"></div>

            {{-- Sección: Configuración --}}
            <div>
                <p class="px-3 mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400"
                   x-show="!sidebarCollapsed"
                   x-cloak>
                    Configuración
                </p>

                <a
                    href="{{ route('configuracion.index') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2 transition-colors
                        {{ $isConfiguracion ? 'bg-[#9d1872]/10 text-[#9d1872] font-semibold' : 'text-slate-700 hover:bg-slate-100' }}"
                    :class="sidebarCollapsed ? 'justify-center' : ''"
                >
                    <svg class="h-4 w-4 text-[#9d1872]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2l3 4-3 4-3-4 3-4zm0 12l3 4-3 4-3-4 3-4z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" x-cloak>Configuración</span>
                </a>
            </div>

            <div class="border-t border-slate-200"></div>

            {{-- Sección: Administración --}}
            <div>
                <p class="px-3 mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400"
                   x-show="!sidebarCollapsed"
                   x-cloak>
                    Administración
                </p>

                {{-- Usuarios --}}
                <a
                    href="{{ route('usuarios.index') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2 transition-colors
                        {{ $isUsuarios ? 'bg-[#9d1872]/10 text-[#9d1872] font-semibold' : 'text-slate-700 hover:bg-slate-100' }}"
                    :class="sidebarCollapsed ? 'justify-center' : ''"
                >
                    <svg class="h-4 w-4 text-[#9d1872]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M7 7a4 4 0 118 0 4 4 0 01-8 0zm11 10a5 5 0 00-9.9 0H3a6 6 0 0118 0h-3z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-cloak>Usuarios</span>
                </a>

                {{-- Roles --}}
                <a
                    href="{{ route('roles.index') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2 transition-colors
                        {{ $isRoles ? 'bg-[#9d1872]/10 text-[#9d1872] font-semibold' : 'text-slate-700 hover:bg-slate-100' }}"
                    :class="sidebarCollapsed ? 'justify-center' : ''"
                >
                    <svg class="h-4 w-4 text-[#9d1872]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 2l3 5-3 5-3-5 3-5zm0 10l3 5-3 5-3-5 3-5z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-cloak>Roles</span>
                </a>
            </div>
        </nav>

        {{-- Botón colapsar --}}
        <div class="px-3 pb-3 flex" :class="sidebarCollapsed ? 'justify-center' : 'justify-end'">
            <button
                type="button"
                class="flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 text-slate-500 hover:bg-slate-100"
                @click="sidebarCollapsed = !sidebarCollapsed"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path x-show="!sidebarCollapsed" x-cloak stroke-linecap="round" stroke-linejoin="round" d="M15 6l-6 6 6 6"/>
                    <path x-show="sidebarCollapsed" x-cloak stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6"/>
                </svg>
            </button>
        </div>

        {{-- Logout --}}
        @auth
            <form method="post" action="{{ route('logout') }}" class="border-t border-slate-200 px-3 py-3 mt-auto">
                @csrf
                <button
                    type="submit"
                    class="w-full inline-flex items-center justify-center rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-700 hover:bg-slate-100"
                    :class="sidebarCollapsed ? 'text-[10px] px-2' : ''"
                >
                    Salir
                </button>
            </form>
        @endauth
    </aside>

    {{-- COLUMNA PRINCIPAL --}}
    <div class="flex-1 flex flex-col min-w-0">
        {{-- TOP BAR --}}
        <header class="border-b border-slate-200 bg-white/95 backdrop-blur px-4 py-3 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-4">
                {{-- Buscador --}}
                <form method="GET" action="{{ url()->current() }}" class="flex-1 max-w-xl">
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M21 21l-4.35-4.35M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14z" />
                            </svg>
                        </span>
                        <input
                            type="text"
                            name="global_search"
                            placeholder="Buscar en el CRM…"
                            class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-8 pr-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#9d1872] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                        >
                    </div>
                </form>

                {{-- Alertas + Perfil --}}
                @auth
                    <div class="flex items-center gap-4">
                        {{-- Icono alertas --}}
                        <div class="relative" x-data="{ open: false }">
                            <button
                                type="button"
                                class="relative flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-500 hover:bg-slate-50"
                                @click="open = !open"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 11-6 0m6 0H9"/>
                                </svg>
                            </button>

                            <div
                                x-show="open"
                                x-cloak
                                @click.outside="open = false"
                                class="absolute right-0 mt-2 w-64 rounded-xl border border-slate-200 bg-white py-2 shadow-lg text-sm z-10"
                            >
                                <div class="px-3 pb-2 border-b border-slate-100">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                        Alertas
                                    </p>
                                </div>
                                <div class="px-3 py-3 text-xs text-slate-600">
                                    No tienes alertas nuevas.
                                </div>
                            </div>
                        </div>

                        {{-- Perfil --}}
                        <div class="flex items-center gap-3">
                            <div class="hidden text-right sm:block">
                                <p class="text-sm font-medium text-slate-900">
                                    {{ $user->name ?? 'Usuario' }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ $user->email ?? '' }}
                                </p>
                            </div>
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#9d1872]/10 text-sm font-semibold text-[#9d1872]">
                                {{ strtoupper(mb_substr($user->name ?? 'U', 0, 1)) }}
                            </div>
                        </div>
                    </div>
                @endauth
            </div>
        </header>

        {{-- STATUS --}}
        @if (session('status'))
            <div class="border-b border-emerald-200 bg-emerald-50 px-6 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        
        {{-- AVISOS (duplicados) --}}
        @if (session('duplicate_conflicts'))
            @php($conflicts = session('duplicate_conflicts'))
            <div class="border-b border-amber-200 bg-amber-50 px-6 py-3 text-sm text-amber-800">
                <p class="font-medium">Aviso: posible duplicado</p>
                <p class="mt-1">
                    El email o teléfono coincide con otras cuentas <span class="font-medium">fuera del mismo grupo empresarial</span>.
                    Revísalo por si se trata de la misma empresa.
                </p>

                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @if (!empty($conflicts['email']))
                        <li>
                            <span class="font-medium">Email:</span>
                            @foreach ($conflicts['email'] as $c)
                                <a class="underline" href="{{ route('accounts.show', $c['id']) }}">{{ $c['name'] }}</a>@if(!$loop->last), @endif
                            @endforeach
                        </li>
                    @endif

                    @if (!empty($conflicts['phone']))
                        <li>
                            <span class="font-medium">Teléfono:</span>
                            @foreach ($conflicts['phone'] as $c)
                                <a class="underline" href="{{ route('accounts.show', $c['id']) }}">{{ $c['name'] }}</a>@if(!$loop->last), @endif
                            @endforeach
                        </li>
                    @endif
                </ul>
            </div>
        @endif

@endif

        {{-- CONTENIDO --}}
        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8 overflow-y-auto">
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>