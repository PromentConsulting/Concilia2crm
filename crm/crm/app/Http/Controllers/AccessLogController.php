<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccessLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AccessLog::with('user')->orderByDesc('logged_in_at');

        // Filtro opcional por usuario
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Filtro opcional por mes (formato YYYY-MM, ej: 2025-10)
        if ($request->filled('month')) {
            $month = $request->input('month'); // '2025-10'
            [$year, $m] = explode('-', $month);

            $start = "{$year}-{$m}-01 00:00:00";
            // fin = primer día del mes siguiente
            $end = date('Y-m-d H:i:s', strtotime("$start +1 month"));

            $query->whereBetween('logged_in_at', [$start, $end]);
        }

        $logs  = $query->paginate(50)->withQueryString();
        $users = User::orderBy('name')->get();

        return view('logs.index', compact('logs', 'users'));
    }
}
