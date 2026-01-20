@php
    $campaign = $campaign ?? null;
    $estadoOptions = ['borrador', 'planificada', 'activa', 'pausada', 'finalizada'];
    $tipoOptions = ['email' => 'Email', 'fax' => 'Fax', 'telefono' => 'Teléfono', 'otros' => 'Otros'];
    $yesNoOptions = [1 => 'Sí', 0 => 'No'];
    $equalityPlanOptions = ['si' => 'Sí', 'no' => 'No', 'ambos' => 'Ambos'];
    $equalityMarkOptions = ['si' => 'Sí', 'no' => 'No', 'indiferente' => 'Indiferente'];
    $selectedCommunities = collect(old('account_comunidad', $filters['account_comunidad'] ?? []))->all();
    $selectedProvinces = collect(old('account_provincia', $filters['account_provincia'] ?? []))->all();
    $mauticCampaigns = $mauticCampaigns ?? [];
    $mauticPreview = $mauticPreview ?? [];
    $selectedMauticId = old('mautic_campaign_id', $campaign->mautic_campaign_id ?? request('mautic_campaign_id'));
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($campaign)
        @method('PUT')
    @endif

    <div class="grid gap-6 lg:grid-cols-4">
        <div class="space-y-6 lg:col-span-3">
            <div class="rounded-xl bg-white p-6 shadow-sm space-y-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-700">Datos de la campaña</h2>
                        <p class="text-xs text-slate-500">Define la información básica y la configuración de Mautic.</p>
                    </div>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Número de campaña</label>
                        <input name="campaign_number" value="{{ old('campaign_number', $campaign->campaign_number ?? '') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Nombre</label>
                        <input name="name" value="{{ old('name', $campaign->name ?? '') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Estado</label>
                        <select name="estado" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                            @foreach ($estadoOptions as $estado)
                                <option value="{{ $estado }}" @selected(old('estado', $campaign->estado ?? 'borrador') === $estado)>{{ ucfirst($estado) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Tipo de campaña</label>
                        <select name="tipo" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                            <option value="">Selecciona</option>
                            @foreach ($tipoOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('tipo', $campaign->tipo ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Fecha inicio</label>
                        <input type="date" name="planned_start_at" value="{{ old('planned_start_at', optional($campaign->planned_start_at ?? null)->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Fecha fin</label>
                        <input type="date" name="planned_end_at" value="{{ old('planned_end_at', optional($campaign->planned_end_at ?? null)->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Confirmación de email</label>
                        <select name="email_confirmation_required" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                            @foreach ($yesNoOptions as $value => $label)
                                <option value="{{ $value }}" @selected((int) old('email_confirmation_required', $campaign->email_confirmation_required ?? 0) === (int) $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Tamaño de empresa</label>
                        <input name="company_size" value="{{ old('company_size', $campaign->company_size ?? '') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Plan de igualdad</label>
                        <select name="equality_plan_preference" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                            <option value="">Indiferente</option>
                            @foreach ($equalityPlanOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('equality_plan_preference', $campaign->equality_plan_preference ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Fecha vigencia plan de igualdad</label>
                        <input type="date" name="equality_plan_valid_until" value="{{ old('equality_plan_valid_until', optional($campaign->equality_plan_valid_until ?? null)->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Distintivo de igualdad</label>
                        <select name="equality_mark_preference" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                            <option value="">Indiferente</option>
                            @foreach ($equalityMarkOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('equality_mark_preference', $campaign->equality_mark_preference ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Habitantes</label>
                        <input type="number" name="habitantes" value="{{ old('habitantes', $campaign->habitantes ?? '') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Origen</label>
                        <input name="origen" value="{{ old('origen', $campaign->origen ?? '') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                        <p class="mt-1 text-xs text-slate-500">Vinculado con el módulo de origen (en desarrollo).</p>
                    </div>
                    <div class="md:col-span-2 space-y-2">
                        <label class="block text-sm font-medium text-slate-700">Campaña a enviar en Mautic</label>
                        <select name="mautic_campaign_id" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                            <option value="">Selecciona una campaña de Mautic</option>
                            @foreach ($mauticCampaigns as $remoteCampaign)
                                <option value="{{ $remoteCampaign['id'] }}" @selected((string) $selectedMauticId === (string) $remoteCampaign['id'])>
                                    {{ $remoteCampaign['name'] }}
                                    @if (! empty($remoteCampaign['is_published']))
                                        (publicada)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-500">Elige qué campaña de Mautic se enviará desde este flujo. Se usará el segmento configurado abajo.</p>

                        @if (! empty($mauticPreview))
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                <div class="flex items-center justify-between text-sm">
                                    <div>
                                        <p class="font-semibold text-slate-800">Previsualización de campaña</p>
                                        <p class="text-slate-500">{{ $mauticPreview['name'] ?? 'Campaña seleccionada' }}</p>
                                    </div>
                                    @if (array_key_exists('is_published', $mauticPreview))
                                        <span class="text-xs font-semibold {{ $mauticPreview['is_published'] ? 'text-green-700' : 'text-amber-700' }}">
                                            {{ $mauticPreview['is_published'] ? 'Publicada' : 'Borrador' }}
                                        </span>
                                    @endif
                                </div>

                                @if (! empty($mauticPreview['preview_html']))
                                    <div class="mt-3 overflow-hidden rounded-lg border border-slate-200 bg-white p-3 text-sm text-slate-700">
                                        {!! $mauticPreview['preview_html'] !!}
                                    </div>
                                @elseif (! empty($mauticPreview['description']))
                                    <p class="mt-2 text-sm text-slate-600">{{ $mauticPreview['description'] }}</p>
                                @else
                                    <p class="mt-2 text-sm text-slate-500">No hay vista previa disponible para esta campaña.</p>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Mautic Segment ID</label>
                        <input type="number" name="mautic_segment_id" value="{{ old('mautic_segment_id', $campaign->mautic_segment_id ?? '') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Descripción</label>
                        <textarea name="descripcion" rows="3" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">{{ old('descripcion', $campaign->descripcion ?? '') }}</textarea>
                    </div>
                    <div class="flex items-center gap-2 pt-6">
                        <input type="checkbox" name="static_snapshot" value="1" @checked(old('static_snapshot', $campaign->static_snapshot ?? false)) class="rounded border-slate-300 text-[#9d1872] focus:ring-[#9d1872]">
                        <label class="text-sm text-slate-700">Guardar snapshot estático</label>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm space-y-4">
                <div>
                    <h2 class="text-sm font-semibold text-slate-700">Filtros de audiencia</h2>
                    <p class="text-xs text-slate-500">Segmenta contactos combinando datos de cuentas y contactos.</p>
                </div>

                <div class="space-y-3">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Datos de cuentas</h3>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Tipo de entidad</label>
                            <select multiple name="account_tipo_entidad[]" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                                @foreach ($filterOptions['account_tipo_entidad'] as $tipo)
                                    <option value="{{ $tipo }}" @selected(collect(old('account_tipo_entidad', $filters['account_tipo_entidad'] ?? []))->contains($tipo))>{{ ucwords(str_replace('_', ' ', $tipo)) }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-slate-500">Ctrl/Cmd + clic para elegir varios.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Estado de la cuenta</label>
                            <select multiple name="account_estado[]" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                                @foreach ($filterOptions['account_estado'] as $estado)
                                    <option value="{{ $estado }}" @selected(collect(old('account_estado', $filters['account_estado'] ?? []))->contains($estado))>{{ ucfirst($estado) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Sector</label>
                            <select multiple name="account_sector[]" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                                @foreach ($filterOptions['account_sector'] as $sector)
                                    <option value="{{ $sector }}" @selected(collect(old('account_sector', $filters['account_sector'] ?? []))->contains($sector))>{{ $sector }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Comunidades autónomas</label>
                            <select multiple name="account_comunidad[]" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                                @foreach ($filterOptions['comunidades'] as $comunidad)
                                    <option value="{{ $comunidad }}" @selected(collect($selectedCommunities)->contains($comunidad))>{{ $comunidad }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-slate-500">Selecciona una o varias comunidades; las provincias se filtrarán automáticamente.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Provincia</label>
                            <select multiple name="account_provincia[]" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                                @foreach ($filterOptions['provincias'] as $provincia)
                                    <option value="{{ $provincia }}" @selected(collect($selectedProvinces)->contains($provincia))>{{ $provincia }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center gap-2 pt-6">
                            <input type="checkbox" name="account_quality" value="1" @checked(old('account_quality', $filters['account_quality'] ?? false)) class="rounded border-slate-300 text-[#9d1872] focus:ring-[#9d1872]">
                            <label class="text-sm text-slate-700">Solo cuentas con calidad</label>
                        </div>
                        <div class="flex items-center gap-2 pt-6">
                            <input type="checkbox" name="account_rse" value="1" @checked(old('account_rse', $filters['account_rse'] ?? false)) class="rounded border-slate-300 text-[#9d1872] focus:ring-[#9d1872]">
                            <label class="text-sm text-slate-700">Solo cuentas con RSE</label>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-700">Intereses</p>
                            <div class="mt-2 space-y-2 rounded-lg border border-slate-200 p-3">
                                @foreach ($filterOptions['account_intereses'] as $campo => $label)
                                    <label class="flex items-center gap-2 text-sm text-slate-700">
                                        <input type="checkbox" name="account_intereses[]" value="{{ $campo }}" @checked(collect(old('account_intereses', $filters['account_intereses'] ?? []))->contains($campo)) class="rounded border-slate-300 text-[#9d1872] focus:ring-[#9d1872]">
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="flex items-center gap-2 pt-6">
                            <input type="checkbox" name="account_equality_plan" value="1" @checked(old('account_equality_plan', $filters['account_equality_plan'] ?? false)) class="rounded border-slate-300 text-[#9d1872] focus:ring-[#9d1872]">
                            <label class="text-sm text-slate-700">Solo cuentas con plan o sello de igualdad</label>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Datos de contactos</h3>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Estado RGPD</label>
                            <select name="estado_rgpd" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                                <option value="">Indiferente</option>
                                @foreach ($filterOptions['estado_rgpd'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('estado_rgpd', $filters['estado_rgpd'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Idiomas</label>
                            <select name="idioma" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                                <option value="">Indiferente</option>
                                @foreach ($filterOptions['idioma'] as $idioma)
                                    <option value="{{ $idioma }}" @selected(old('idioma', $filters['idioma'] ?? '') === $idioma)>{{ $idioma }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Estado del contacto</label>
                            <select multiple name="estado_contacto[]" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                                @foreach ($filterOptions['estado_contacto'] as $estado)
                                    <option value="{{ $estado }}" @selected(collect(old('estado_contacto', $filters['estado_contacto'] ?? []))->contains($estado))>{{ ucwords(str_replace('_', ' ', $estado)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Nivel de decisión</label>
                            <select multiple name="niveles_decision[]" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                                @foreach ($filterOptions['niveles_decision'] as $nivel)
                                    <option value="{{ $nivel }}" @selected(collect(old('niveles_decision', $filters['niveles_decision'] ?? []))->contains($nivel))>{{ ucfirst($nivel) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Roles del contacto</label>
                            <select multiple name="roles[]" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                                @foreach ($filterOptions['roles'] as $role)
                                    <option value="{{ $role }}" @selected(collect(old('roles', $filters['roles'] ?? []))->contains($role))>{{ ucwords(str_replace('_', ' ', $role)) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1 lg:self-start lg:sticky lg:top-6">
            <div class="space-y-4 rounded-xl bg-white p-4 shadow-sm lg:max-h-[calc(100vh-2rem)] lg:overflow-y-auto">
                <div class="space-y-2">
                    <h2 class="text-sm font-semibold text-slate-700">Previsualización del email</h2>
                    @if (! empty($mauticPreview['preview_html']))
                        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white p-3 text-xs text-slate-700">
                            {!! $mauticPreview['preview_html'] !!}
                        </div>
                    @elseif (! empty($mauticPreview['description']))
                        <p class="text-xs text-slate-600">{{ $mauticPreview['description'] }}</p>
                    @else
                        <p class="text-xs text-slate-500">Selecciona una campaña de Mautic para ver la vista previa del email.</p>
                    @endif
                </div>
                <div class="space-y-2">
                    <h2 class="text-sm font-semibold text-slate-700">Resultados del filtrado</h2>
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-slate-500">Contactos que cumplen los filtros.</p>
                        <span class="text-2xl font-bold text-[#9d1872]">{{ $audienciaCount }}</span>
                    </div>
                    @if ($previewContacts->isNotEmpty())
                        <ul class="divide-y divide-slate-200 rounded-lg border border-slate-200 bg-white text-xs text-slate-700">
                            @foreach ($previewContacts as $contact)
                                <li class="px-3 py-2">{{ $contact->first_name }} {{ $contact->last_name }} — {{ $contact->email }} ({{ optional($contact->primaryAccount)->name ?? 'Sin cuenta' }})</li>
                            @endforeach
                        </ul>
                    @endif
                    <p class="text-xs text-slate-500">Usa el botón "Previsualizar" para recalcular la audiencia antes de guardar.</p>
                </div>

                <div class="flex flex-col gap-2">
                    <a href="{{ route('campaigns.index') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-center text-sm font-semibold text-slate-600">Cancelar</a>
                    <button type="submit" formmethod="GET" formaction="{{ $campaign ? route('campaigns.edit', $campaign) : route('campaigns.create') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">Previsualizar</button>
                    <button type="submit" class="rounded-lg bg-[#9d1872] px-3 py-2 text-sm font-semibold text-white">Guardar campaña</button>
                </div>
            </div>
        </div>
    </div>
</form>