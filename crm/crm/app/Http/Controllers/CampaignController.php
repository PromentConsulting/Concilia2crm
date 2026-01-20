<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\ContactRole;
use App\Services\CampaignSegmentBuilder;
use App\Services\MauticService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CampaignController extends Controller
{
    public function index(): View
    {
        $campaigns = Campaign::query()->latest()->paginate(20);

        return view('campaigns.index', compact('campaigns'));
    }

    public function create(Request $request, CampaignSegmentBuilder $segmentBuilder, MauticService $mauticService): View
    {
        $filters = $this->extractFilters($request);
        $audienciaQuery = $segmentBuilder->build($filters);
        $selectedMauticCampaign = $request->input('mautic_campaign_id');

        return view('campaigns.create', [
            'filterOptions' => $this->filterOptions($filters),
            'filters' => $filters,
            'audienciaCount' => $audienciaQuery->count(),
            'previewContacts' => $audienciaQuery->limit(5)->get(),
            'mauticCampaigns' => $mauticService->availableCampaigns(),
            'mauticPreview' => $selectedMauticCampaign ? $mauticService->campaignPreview((int) $selectedMauticCampaign) : [],
        ]);
    }

    public function store(Request $request, CampaignSegmentBuilder $segmentBuilder): RedirectResponse
    {
        $data = $request->validate([
            'campaign_number' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['nullable', 'string', 'in:email,fax,telefono,otros'],
            'estado' => ['required', 'string', 'in:borrador,planificada,activa,pausada,finalizada'],
            'planned_start_at' => ['nullable', 'date'],
            'planned_end_at' => ['nullable', 'date', 'after_or_equal:planned_start_at'],
            'email_confirmation_required' => ['sometimes', 'boolean'],
            'company_size' => ['nullable', 'string', 'max:100'],
            'equality_plan_preference' => ['nullable', 'string', 'in:si,no,ambos'],
            'habitantes' => ['nullable', 'integer', 'min:0'],
            'equality_plan_valid_until' => ['nullable', 'date'],
            'equality_mark_preference' => ['nullable', 'string', 'in:si,no,indiferente'],
            'origen' => ['nullable', 'string', 'max:255'],
            'static_snapshot' => ['sometimes', 'boolean'],
            'mautic_campaign_id' => ['nullable', 'integer'],
            'mautic_segment_id' => ['nullable', 'integer'],
            'account_tipo_entidad' => ['sometimes', 'array'],
            'account_tipo_entidad.*' => ['string'],
            'account_estado' => ['sometimes', 'array'],
            'account_estado.*' => ['string'],
            'account_sector' => ['sometimes', 'array'],
            'account_sector.*' => ['string'],
            'account_comunidad' => ['sometimes', 'array'],
            'account_comunidad.*' => ['string', 'in:' . implode(',', $this->communities())],
            'account_provincia' => ['sometimes', 'array'],
            'account_provincia.*' => ['string', 'in:' . implode(',', $this->allProvinces())],
            'account_quality' => ['sometimes', 'boolean'],
            'account_rse' => ['sometimes', 'boolean'],
            'account_intereses' => ['sometimes', 'array'],
            'account_intereses.*' => ['in:interest_local,interest_regional,interest_national'],
            'account_equality_plan' => ['sometimes', 'boolean'],
            'estado_rgpd' => ['nullable', 'string', 'in:consentimiento_otorgado,no_otorgado,revocado'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['string'],
            'niveles_decision' => ['sometimes', 'array'],
            'niveles_decision.*' => ['string', 'in:alto,medio,bajo'],
            'idioma' => ['nullable', 'string', 'max:100'],
            'estado_contacto' => ['sometimes', 'array'],
            'estado_contacto.*' => ['string', 'in:activo,inactivo,rebotado,baja_marketing,no_localizable'],
        ]);

        $data['segment_definition'] = $this->extractFilters($request);
        $data['static_snapshot'] = (bool) ($data['static_snapshot'] ?? false);
        $data['email_confirmation_required'] = (bool) ($data['email_confirmation_required'] ?? false);

        $campaign = Campaign::create($data);

        // precalcular audiencia
        $query = $segmentBuilder->build($data['segment_definition']);
        if ($campaign->static_snapshot) {
            $campaign->contacts()->syncWithoutDetaching(
                $query->pluck('id')->map(fn ($id) => ['contact_id' => $id])
            );
        }

        return redirect()->route('campaigns.show', $campaign);
    }

    public function edit(Request $request, Campaign $campaign, CampaignSegmentBuilder $segmentBuilder, MauticService $mauticService): View
    {
        $filters = $this->extractFilters(new Request(array_merge($campaign->segment_definition ?? [], $request->all())));
        $audienciaQuery = $segmentBuilder->build($filters);
        $selectedMauticCampaign = $request->input('mautic_campaign_id', $campaign->mautic_campaign_id);

        return view('campaigns.edit', [
            'campaign' => $campaign,
            'filterOptions' => $this->filterOptions($filters),
            'filters' => $filters,
            'audienciaCount' => $audienciaQuery->count(),
            'previewContacts' => $audienciaQuery->limit(5)->get(),
            'mauticCampaigns' => $mauticService->availableCampaigns(),
            'mauticPreview' => $selectedMauticCampaign ? $mauticService->campaignPreview((int) $selectedMauticCampaign) : [],
        ]);
    }

    public function update(Request $request, Campaign $campaign, CampaignSegmentBuilder $segmentBuilder): RedirectResponse
    {
        $data = $request->validate([
            'campaign_number' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['nullable', 'string', 'in:email,fax,telefono,otros'],
            'estado' => ['required', 'string', 'in:borrador,planificada,activa,pausada,finalizada'],
            'planned_start_at' => ['nullable', 'date'],
            'planned_end_at' => ['nullable', 'date', 'after_or_equal:planned_start_at'],
            'email_confirmation_required' => ['sometimes', 'boolean'],
            'company_size' => ['nullable', 'string', 'max:100'],
            'equality_plan_preference' => ['nullable', 'string', 'in:si,no,ambos'],
            'habitantes' => ['nullable', 'integer', 'min:0'],
            'equality_plan_valid_until' => ['nullable', 'date'],
            'equality_mark_preference' => ['nullable', 'string', 'in:si,no,indiferente'],
            'origen' => ['nullable', 'string', 'max:255'],
            'static_snapshot' => ['sometimes', 'boolean'],
            'mautic_campaign_id' => ['nullable', 'integer'],
            'mautic_segment_id' => ['nullable', 'integer'],
            'account_tipo_entidad' => ['sometimes', 'array'],
            'account_tipo_entidad.*' => ['string'],
            'account_estado' => ['sometimes', 'array'],
            'account_estado.*' => ['string'],
            'account_sector' => ['sometimes', 'array'],
            'account_sector.*' => ['string'],
            'account_comunidad' => ['sometimes', 'array'],
            'account_comunidad.*' => ['string', 'in:' . implode(',', $this->communities())],
            'account_provincia' => ['sometimes', 'array'],
            'account_provincia.*' => ['string', 'in:' . implode(',', $this->allProvinces())],
            'account_quality' => ['sometimes', 'boolean'],
            'account_rse' => ['sometimes', 'boolean'],
            'account_intereses' => ['sometimes', 'array'],
            'account_intereses.*' => ['in:interest_local,interest_regional,interest_national'],
            'account_equality_plan' => ['sometimes', 'boolean'],
            'estado_rgpd' => ['nullable', 'string', 'in:consentimiento_otorgado,no_otorgado,revocado'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['string'],
            'niveles_decision' => ['sometimes', 'array'],
            'niveles_decision.*' => ['string', 'in:alto,medio,bajo'],
            'idioma' => ['nullable', 'string', 'max:100'],
            'estado_contacto' => ['sometimes', 'array'],
            'estado_contacto.*' => ['string', 'in:activo,inactivo,rebotado,baja_marketing,no_localizable'],
        ]);

        $data['segment_definition'] = $this->extractFilters($request);
        $data['static_snapshot'] = (bool) ($data['static_snapshot'] ?? false);
        $data['email_confirmation_required'] = (bool) ($data['email_confirmation_required'] ?? false);

        $campaign->update($data);

        if ($campaign->static_snapshot) {
            $query = $segmentBuilder->build($data['segment_definition']);
            $campaign->contacts()->syncWithoutDetaching(
                $query->pluck('id')->map(fn ($id) => ['contact_id' => $id])
            );
        }

        return redirect()->route('campaigns.show', $campaign);
    }


    public function show(Campaign $campaign, CampaignSegmentBuilder $segmentBuilder, MauticService $mauticService): View
    {
        $audienciaQuery = $segmentBuilder->build($campaign->segment_definition ?? []);
        $preview = $audienciaQuery->limit(5)->get();

        return view('campaigns.show', [
            'campaign' => $campaign->load(['contacts', 'events']),
            'audienciaCount' => $audienciaQuery->count(),
            'previewContacts' => $preview,
            'filterSummary' => $this->presentFilters($campaign->segment_definition ?? []),
            'mauticMetrics' => $mauticService->campaignMetrics($campaign),
            'mauticPreview' => $campaign->mautic_campaign_id ? $mauticService->campaignPreview((int) $campaign->mautic_campaign_id) : [],
        ]);
    }
    protected function filterOptions(array $filters = []): array
    {
        return [
            'account_tipo_entidad' => [
                'empresa_privada',
                'aapp',
                'sin_animo_de_lucro',
                'corporacion_derecho_publico',
                'particular',
            ],
            'account_estado' => ['activo', 'inactivo'],
            'account_sector' => $this->accountIndustries(),
            'comunidades' => $this->communities(),
            'provincias' => $this->availableProvinces($filters['account_comunidad'] ?? []),
            'account_intereses' => [
                'interest_local' => 'Interés local',
                'interest_regional' => 'Interés regional',
                'interest_national' => 'Interés nacional',
            ],
            'estado_rgpd' => [
                'consentimiento_otorgado' => 'Consentimiento otorgado',
                'no_otorgado' => 'No otorgado',
                'revocado' => 'Revocado',
            ],
            'niveles_decision' => ['alto', 'medio', 'bajo'],
            'estado_contacto' => ['activo', 'inactivo', 'rebotado', 'baja_marketing', 'no_localizable'],
            'idioma' => $this->contactLanguages(),
            'roles' => ContactRole::query()
                ->whereNotNull('role')
                ->where('role', '!=', '')
                ->distinct()
                ->orderBy('role')
                ->pluck('role')
                ->values()
                ->all(),
        ];
    }

    protected function extractFilters(Request $request): array
    {
        return array_filter([
            'account_tipo_entidad' => array_filter((array) $request->input('account_tipo_entidad', [])),
            'account_estado' => array_filter((array) $request->input('account_estado', [])),
            'account_sector' => array_filter((array) $request->input('account_sector', [])),
            'account_comunidad' => array_filter((array) $request->input('account_comunidad', [])),
            'account_provincia' => array_intersect(
                array_filter((array) $request->input('account_provincia', [])),
                $this->availableProvinces((array) $request->input('account_comunidad', []))
            ),
            'account_quality' => $request->boolean('account_quality'),
            'account_rse' => $request->boolean('account_rse'),
            'account_intereses' => array_filter((array) $request->input('account_intereses', [])),
            'account_equality_plan' => $request->boolean('account_equality_plan'),
            'estado_rgpd' => $request->input('estado_rgpd'),
            'roles' => array_filter((array) $request->input('roles', [])),
            'niveles_decision' => array_filter((array) $request->input('niveles_decision', [])),
            'idioma' => $request->input('idioma'),
            'estado_contacto' => array_filter((array) $request->input('estado_contacto', [])),
        ], fn ($value) => $value !== null && $value !== [] && $value !== '');
    }
    protected function presentFilters(array $filters): array
    {
        $options = $this->filterOptions();
        $presented = [];

        if (! empty($filters['account_tipo_entidad'])) {
            $presented['Tipo de entidad'] = collect($filters['account_tipo_entidad'])
                ->map(fn ($tipo) => ucwords(str_replace('_', ' ', $tipo)))
                ->implode(', ');
        }

        if (! empty($filters['account_estado'])) {
            $presented['Estado de la cuenta'] = collect($filters['account_estado'])
                ->map(fn ($estado) => ucfirst($estado))
                ->implode(', ');
        }

        if (! empty($filters['account_sector'])) {
            $presented['Sector'] = collect($filters['account_sector'])->implode(', ');
        }

        if (! empty($filters['account_comunidad'])) {
            $presented['Comunidad'] = collect($filters['account_comunidad'])->implode(', ');
        }

        if (! empty($filters['account_provincia'])) {
            $presented['Provincia'] = collect($filters['account_provincia'])->implode(', ');
        }

        if (! empty($filters['account_quality'])) {
            $presented['Calidad'] = 'Solo cuentas con calidad';
        }

        if (! empty($filters['account_rse'])) {
            $presented['RSE'] = 'Solo cuentas con RSE';
        }

        if (! empty($filters['account_intereses'])) {
            $presented['Intereses'] = collect($filters['account_intereses'])
                ->map(fn ($campo) => $options['account_intereses'][$campo] ?? $campo)
                ->implode(', ');
        }

        if (! empty($filters['account_equality_plan'])) {
            $presented['Plan de igualdad'] = 'Con plan o sello de igualdad';
        }

        if (! empty($filters['estado_rgpd'])) {
            $labels = $this->filterOptions();
            $presented['Estado RGPD'] = $labels['estado_rgpd'][$filters['estado_rgpd']] ?? $filters['estado_rgpd'];
        }

        if (! empty($filters['roles'])) {
            $presented['Roles'] = collect($filters['roles'])
                ->map(fn ($role) => ucwords(str_replace('_', ' ', $role)))
                ->implode(', ');
        }

        if (! empty($filters['niveles_decision'])) {
            $presented['Nivel de decisión'] = collect($filters['niveles_decision'])
                ->map(fn ($nivel) => ucfirst($nivel))
                ->implode(', ');
        }

        if (! empty($filters['idioma'])) {
            $presented['Idioma'] = $filters['idioma'];
        }

        if (! empty($filters['estado_contacto'])) {
            $presented['Estado contacto'] = collect($filters['estado_contacto'])
                ->map(fn ($estado) => ucwords(str_replace('_', ' ', $estado)))
                ->implode(', ');
        }

        return $presented;
    }

    protected function accountIndustries(): array
    {
        return [
            'Agricultura y alimentación',
            'Gobierno y administración pública',
            'Comercio y distribución',
            'Tercer sector',
            'Logística y transporte',
            'Construcción e infraestructuras',
            'Medios de comunicación y entretenimiento',
            'Automotriz e industria',
            'Consultoría',
            'Salud',
            'Energético e informático',
            'Turismo y hostelería',
            'Financiero y bancario',
            'Educación y formación',
        ];
    }

    protected function contactLanguages(): array
    {
        if (! Schema::hasColumn('contacts', 'idioma')) {
            return [];
        }

        return Contact::query()
            ->whereNotNull('idioma')
            ->where('idioma', '!=', '')
            ->distinct()
            ->orderBy('idioma')
            ->pluck('idioma')
            ->values()
            ->all();
    }
    
    protected function communities(): array
    {
        return [
            'Andalucía',
            'Aragón',
            'Asturias',
            'Islas Baleares',
            'Canarias',
            'Cantabria',
            'Castilla-La Mancha',
            'Castilla y León',
            'Cataluña',
            'Comunidad Valenciana',
            'Extremadura',
            'Galicia',
            'La Rioja',
            'Comunidad de Madrid',
            'Región de Murcia',
            'Navarra',
            'País Vasco',
            'Ceuta',
            'Melilla',
        ];
    }

    protected function provinceMap(): array
    {
        return [
            'Andalucía' => ['Almería', 'Cádiz', 'Córdoba', 'Granada', 'Huelva', 'Jaén', 'Málaga', 'Sevilla'],
            'Aragón' => ['Huesca', 'Teruel', 'Zaragoza'],
            'Asturias' => ['Asturias'],
            'Islas Baleares' => ['Islas Baleares'],
            'Canarias' => ['Las Palmas', 'Santa Cruz de Tenerife'],
            'Cantabria' => ['Cantabria'],
            'Castilla-La Mancha' => ['Albacete', 'Ciudad Real', 'Cuenca', 'Guadalajara', 'Toledo'],
            'Castilla y León' => ['Ávila', 'Burgos', 'León', 'Palencia', 'Salamanca', 'Segovia', 'Soria', 'Valladolid', 'Zamora'],
            'Cataluña' => ['Barcelona', 'Girona', 'Lleida', 'Tarragona'],
            'Comunidad Valenciana' => ['Alicante', 'Castellón', 'Valencia'],
            'Extremadura' => ['Badajoz', 'Cáceres'],
            'Galicia' => ['A Coruña', 'Lugo', 'Ourense', 'Pontevedra'],
            'La Rioja' => ['La Rioja'],
            'Comunidad de Madrid' => ['Madrid'],
            'Región de Murcia' => ['Murcia'],
            'Navarra' => ['Navarra'],
            'País Vasco' => ['Álava', 'Bizkaia', 'Gipuzkoa'],
            'Ceuta' => ['Ceuta'],
            'Melilla' => ['Melilla'],
        ];
    }

    protected function allProvinces(): array
    {
        return collect($this->provinceMap())->flatten()->sort()->values()->all();
    }

    protected function availableProvinces(array $communities = []): array
    {
        $communities = array_filter($communities);

        if (empty($communities)) {
            return $this->allProvinces();
        }

        return collect($this->provinceMap())
            ->only($communities)
            ->flatten()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}