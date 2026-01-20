<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Solicitud;
use App\Models\Tarea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the CRM dashboard.
     */
    public function __invoke(): View
    {
        $user = auth()->user();
        $accountCount = 0;
        $contactCount = 0;
        $solicitudesPendientes = 0;
        $tareasPendientes = 0;
        $recentAccounts = collect();
        $recentContacts = collect();
        $recentSolicitudes = collect();
        $recentTareas = collect();

        if (Schema::hasTable('accounts')) {
            $accountCount = Account::count();
            $recentAccounts = Account::query()
                ->withCount('contacts')
                ->latest()
                ->take(5)
                ->get();
        }

        if (Schema::hasTable('contacts')) {
            $contactCount = Contact::count();
            $recentContacts = Contact::query()
                ->with('account:id,name')
                ->latest()
                ->take(5)
                ->get();
        }

        if (Schema::hasTable('solicitudes')) {
            $solicitudesPendientes = Solicitud::query()
                ->whereIn('estado', ['pendiente_asignacion', 'asignado', 'en_curso', 'en_espera'])
                ->count();
            $recentSolicitudes = Solicitud::query()
                ->latest()
                ->take(5)
                ->get();
        }

        if (Schema::hasTable('tareas')) {
            $tareasPendientes = Tarea::query()
                ->where('estado', 'pendiente')
                ->when($user?->id, fn ($query) => $query->where('owner_user_id', $user->id))
                ->count();
            $recentTareas = Tarea::query()
                ->when($user?->id, fn ($query) => $query->where('owner_user_id', $user->id))
                ->latest()
                ->take(5)
                ->get();
        }

        $dashboardLayout = $user?->dashboard_layout;
        if (! is_array($dashboardLayout) || empty($dashboardLayout)) {
            $dashboardLayout = $this->defaultLayout();
        }

        return view('dashboard', [
            'accountCount' => $accountCount,
            'contactCount' => $contactCount,
            'solicitudesPendientes' => $solicitudesPendientes,
            'tareasPendientes' => $tareasPendientes,
            'recentAccounts' => $recentAccounts,
            'recentContacts' => $recentContacts,
            'recentSolicitudes' => $recentSolicitudes,
            'recentTareas' => $recentTareas,
            'dashboardLayout' => $dashboardLayout,
        ]);
    }

    public function updateLayout(Request $request): RedirectResponse
    {
        $user = $request->user();
        $layout = $user?->dashboard_layout;
        if (! is_array($layout) || empty($layout)) {
            $layout = $this->defaultLayout();
        }

        $action = $request->string('action')->toString();
        $availableWidgets = $this->availableWidgets();

        if ($action === 'add_row') {
            $columns = (int) $request->input('columns', 2);
            $columns = max(1, min($columns, 4));
            $layout[] = [
                'columns' => $columns,
                'widgets' => [],
            ];
        }

        if ($action === 'add_widget') {
            $widget = $request->input('widget');
            if (in_array($widget, $availableWidgets, true)) {
                if (empty($layout)) {
                    $layout = [
                        [
                            'columns' => 2,
                            'widgets' => [],
                        ],
                    ];
                }

                $rowIndex = (int) $request->input('row', count($layout));
                $rowIndex = max(1, min($rowIndex, count($layout)));
                $rowKey = $rowIndex - 1;
                $widgets = $layout[$rowKey]['widgets'] ?? [];
                $columnLimit = (int) ($layout[$rowKey]['columns'] ?? 1);

                if (count($widgets) >= $columnLimit) {
                    $layout[] = [
                        'columns' => $columnLimit,
                        'widgets' => [$widget],
                    ];
                } else {
                    $widgets[] = $widget;
                    $layout[$rowKey]['widgets'] = $widgets;
                }
            }
        }

        if ($action === 'save') {
            $submittedLayout = $request->input('dashboard_layout');
            if (is_string($submittedLayout)) {
                $decoded = json_decode($submittedLayout, true);
                if (is_array($decoded) && ! empty($decoded)) {
                    $layout = $decoded;
                }
            }
        }

        if ($user) {
            $user->dashboard_layout = $layout;
            $user->save();
        }

        return redirect()->route('dashboard');
    }

    private function defaultLayout(): array
    {
        return [
            [
                'columns' => 4,
                'widgets' => [
                    'account_count',
                    'contact_count',
                    'solicitudes_pendientes',
                    'tareas_pendientes',
                ],
            ],
            [
                'columns' => 2,
                'widgets' => [
                    'recent_accounts',
                    'recent_contacts',
                ],
            ],
            [
                'columns' => 2,
                'widgets' => [
                    'recent_solicitudes',
                    'recent_tareas',
                ],
            ],
        ];
    }

    private function availableWidgets(): array
    {
        return [
            'account_count',
            'contact_count',
            'solicitudes_pendientes',
            'tareas_pendientes',
            'quick_links',
            'resources',
            'recent_accounts',
            'recent_contacts',
            'recent_solicitudes',
            'recent_tareas',
        ];
    }
}