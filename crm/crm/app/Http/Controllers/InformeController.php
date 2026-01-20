<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use App\Models\Peticion;
use App\Models\Pedido;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InformeController extends Controller
{
    public function index(Request $request): View
    {
        $today = now();

        // Fechas (por defecto: desde inicio de año hasta hoy)
        $from = $request->query('from')
            ? Carbon::createFromFormat('Y-m-d', $request->query('from'))->startOfDay()
            : $today->copy()->startOfYear();

        $to = $request->query('to')
            ? Carbon::createFromFormat('Y-m-d', $request->query('to'))->endOfDay()
            : $today->copy()->endOfDay();

        $ownerId = $request->query('owner_id');
        $origen  = $request->query('origen');

        // Usuarios (para el filtro)
        $users = User::orderBy('name')->get(['id', 'name']);

        // Orígenes disponibles de solicitudes
        $origins = Solicitud::query()
            ->select('origen')
            ->distinct()
            ->orderBy('origen')
            ->pluck('origen')
            ->filter()
            ->values();

        // --------- QUERIES BASE ----------

        // Solicitudes (leads)
        $solicitudesBase = Solicitud::query()
            ->whereBetween('created_at', [$from, $to]);

        if ($ownerId) {
            $solicitudesBase->where('owner_user_id', $ownerId);
        }

        if ($origen) {
            $solicitudesBase->where('origen', $origen);
        }

        // Peticiones (oportunidades / presupuestos)
        $peticionesBase = Peticion::query()
            ->whereBetween('created_at', [$from, $to]);

        // Pedidos (ventas)
        $pedidosBase = Pedido::query()
            ->whereBetween('created_at', [$from, $to]);

        // Tareas comerciales
        $tareasBase = Tarea::query()
            ->whereBetween('created_at', [$from, $to]);

        if ($ownerId) {
            $tareasBase->where('owner_user_id', $ownerId);
        }

        // --------- KPIs PRINCIPALES ----------

        $totalSolicitudes = (clone $solicitudesBase)->count();
        $totalPeticiones  = (clone $peticionesBase)->count();
        $totalPedidos     = (clone $pedidosBase)->count();

        $importePeticiones = (clone $peticionesBase)->sum('importe_total');
        $importePedidos    = (clone $pedidosBase)->sum('importe_total');

        $totalTareas       = (clone $tareasBase)->count();
        $tareasCompletadas = (clone $tareasBase)->where('estado', 'completada')->count();

        $kpis = [
            'total_solicitudes'  => $totalSolicitudes,
            'total_peticiones'   => $totalPeticiones,
            'total_pedidos'      => $totalPedidos,
            'importe_peticiones' => $importePeticiones,
            'importe_pedidos'    => $importePedidos,
            'total_tareas'       => $totalTareas,
            'tareas_completadas' => $tareasCompletadas,
        ];

        // --------- PIPELINE ----------

        $pipeline = [
            'leads'                    => $totalSolicitudes,
            'peticiones'               => $totalPeticiones,
            'pedidos'                  => $totalPedidos,
            'rate_leads_to_peticiones' => $totalSolicitudes > 0
                ? round(($totalPeticiones / $totalSolicitudes) * 100, 1)
                : null,
            'rate_peticiones_to_pedidos' => $totalPeticiones > 0
                ? round(($totalPedidos / $totalPeticiones) * 100, 1)
                : null,
            'rate_leads_to_pedidos' => $totalSolicitudes > 0
                ? round(($totalPedidos / $totalSolicitudes) * 100, 1)
                : null,
        ];

        // --------- LEADS POR CANAL (ORIGEN) ----------

        $channelStats = (clone $solicitudesBase)
            ->select('origen', DB::raw('count(*) as total'))
            ->groupBy('origen')
            ->orderByDesc('total')
            ->get();

        // --------- INDICADORES POR COMERCIAL (SOLO SOLICITUDES) ----------

        $leadsPorUsuario = Solicitud::query()
            ->select('owner_user_id', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$from, $to])
            ->when($origen, fn ($q) => $q->where('origen', $origen))
            ->whereNotNull('owner_user_id')
            ->groupBy('owner_user_id')
            ->get()
            ->keyBy('owner_user_id');

        $cerradasPorUsuario = Solicitud::query()
            ->select('owner_user_id', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$from, $to])
            ->when($origen, fn ($q) => $q->where('origen', $origen))
            ->whereNotNull('owner_user_id')
            ->where('estado', '<>', 'abierta')
            ->groupBy('owner_user_id')
            ->get()
            ->keyBy('owner_user_id');

        $usersIndex = $users->keyBy('id');

        $userStats = [];
        foreach ($leadsPorUsuario as $userId => $row) {
            $totalLeadsUser = (int) $row->total;
            $totalCerradas  = (int) ($cerradasPorUsuario[$userId]->total ?? 0);

            $ratio = $totalLeadsUser > 0
                ? round(($totalCerradas / $totalLeadsUser) * 100, 1)
                : null;

            $userStats[] = [
                'user_name' => $usersIndex[$userId]->name ?? ('Usuario #' . $userId),
                'leads'     => $totalLeadsUser,
                'cerradas'  => $totalCerradas,
                'ratio'     => $ratio,
            ];
        }

        // --------- EVOLUCIÓN MENSUAL (LEADS, PETICIONES, PEDIDOS) ----------

        $monthlyLeads = Solicitud::query()
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as total')
            ->whereBetween('created_at', [$from, $to])
            ->when($ownerId, fn ($q) => $q->where('owner_user_id', $ownerId))
            ->when($origen, fn ($q) => $q->where('origen', $origen))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $monthlyPeticiones = Peticion::query()
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as total')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $monthlyPedidos = Pedido::query()
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as total')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $months = [];

        foreach ($monthlyLeads as $row) {
            $key = sprintf('%04d-%02d', $row->year, $row->month);
            $months[$key]['label'] = sprintf('%02d/%04d', $row->month, $row->year);
            $months[$key]['leads'] = (int) $row->total;
        }

        foreach ($monthlyPeticiones as $row) {
            $key = sprintf('%04d-%02d', $row->year, $row->month);
            $months[$key]['label'] = sprintf('%02d/%04d', $row->month, $row->year);
            $months[$key]['peticiones'] = (int) $row->total;
        }

        foreach ($monthlyPedidos as $row) {
            $key = sprintf('%04d-%02d', $row->year, $row->month);
            $months[$key]['label'] = sprintf('%02d/%04d', $row->month, $row->year);
            $months[$key]['pedidos'] = (int) $row->total;
        }

        ksort($months);

        // --------- STATS DE TAREAS (PLANIFICACIÓN / SEGUIMIENTO) ----------

        $tasksStats = [
            'total'        => $totalTareas,
            'completadas'  => $tareasCompletadas,
            'pendientes'   => max($totalTareas - $tareasCompletadas, 0),
            'completion_rate' => $totalTareas > 0
                ? round(($tareasCompletadas / $totalTareas) * 100, 1)
                : null,
        ];

        return view('informes.index', [
            'filters' => [
                'from'     => $from->format('Y-m-d'),
                'to'       => $to->format('Y-m-d'),
                'owner_id' => $ownerId,
                'origen'   => $origen,
            ],
            'users'         => $users,
            'origins'       => $origins,
            'kpis'          => $kpis,
            'pipeline'      => $pipeline,
            'channelStats'  => $channelStats,
            'userStats'     => $userStats,
            'months'        => $months,
            'tasksStats'    => $tasksStats,
        ]);
    }
}
