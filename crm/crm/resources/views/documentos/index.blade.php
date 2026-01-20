@extends('layouts.app')

@section('title', 'Documentos')

@section('content')
<div class="space-y-6">
    <header class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Documentos</h1>
            <p class="mt-1 text-sm text-slate-500">
                Gestión documental vinculada a cuentas, solicitudes, peticiones y pedidos.
            </p>
        </div>
    </header>

    <section class="rounded-2xl bg-white p-5 shadow-sm">
        @if($documentos->isEmpty())
            <p class="text-sm text-slate-500">No hay documentos registrados.</p>
        @else
            <div class="overflow-hidden rounded-xl border border-slate-100">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-2 text-left">Título</th>
                            <th class="px-3 py-2 text-left">Tipo</th>
                            <th class="px-3 py-2 text-left">Vinculado a</th>
                            <th class="px-3 py-2 text-left">Fecha doc.</th>
                            <th class="px-3 py-2 text-left">Usuario</th>
                            <th class="px-3 py-2 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 bg-white">
                        @foreach($documentos as $documento)
                            <tr>
                                <td class="px-3 py-2">
                                    <a href="{{ route('documentos.download', $documento) }}"
                                       class="text-[#9d1872] hover:underline">
                                        {{ $documento->titulo }}
                                    </a>
                                </td>
                                <td class="px-3 py-2">
                                    {{ $documento->tipo ?: '—' }}
                                </td>
                                <td class="px-3 py-2 text-xs text-slate-700">
                                    @if($documento->solicitud)
                                        Solicitud: {{ $documento->solicitud->asunto }}
                                    @elseif($documento->peticion)
                                        Petición: {{ $documento->peticion->titulo }}
                                    @elseif($documento->pedido)
                                        Pedido: {{ $documento->pedido->numero ?: 'Pedido '.$documento->pedido->id }}
                                    @elseif($documento->account)
                                        Cuenta: {{ $documento->account->name }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-xs text-slate-700">
                                    {{ $documento->fecha_documento ? $documento->fecha_documento->format('d/m/Y') : '—' }}
                                </td>
                                <td class="px-3 py-2 text-xs text-slate-700">
                                    {{ optional($documento->owner)->name ?: '—' }}
                                </td>
                                <td class="px-3 py-2 text-right text-xs">
                                    <a
                                        href="{{ route('documentos.download', $documento) }}"
                                        class="inline-flex items-center rounded-lg border border-slate-200 px-2 py-1 text-[11px] text-slate-700 hover:bg-slate-50"
                                    >
                                        Descargar
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $documentos->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
