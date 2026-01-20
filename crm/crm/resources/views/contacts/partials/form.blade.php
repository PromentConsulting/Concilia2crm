@php
    /** @var \App\Models\Contact|null $contact */
    $contact = $contact ?? null;
    $accounts = $accounts ?? collect();
    $defaultAccountId = old('account_id', $contact->account_id ?? ($defaultAccount ?? ''));
@endphp

<div class="space-y-6">
    {{-- CUENTA ASOCIADA --}}
    <section class="rounded-2xl bg-white p-4 border border-slate-200">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
            Cuenta asociada
        </h2>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700">Cuenta</label>
                <select
                    name="account_id"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:ring-1 focus:ring-[#9d1872] focus:outline-none"
                >
                    <option value="">Sin cuenta</option>
                    @foreach ($accounts as $accountOption)
                        <option
                            value="{{ $accountOption->id }}"
                            {{ (int) $defaultAccountId === $accountOption->id ? 'selected' : '' }}
                        >
                            {{ $accountOption->name }}
                        </option>
                    @endforeach
                </select>
                @error('account_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    {{-- DATOS BÁSICOS --}}
    <section class="rounded-2xl bg-white p-4 border border-slate-200">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
            Datos del contacto
        </h2>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700">Nombre</label>
                <input
                    type="text"
                    name="first_name"
                    value="{{ old('first_name', $contact->first_name ?? '') }}"
                    required
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:ring-1 focus:ring-[#9d1872] focus:outline-none"
                >
                @error('first_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Apellidos</label>
                <input
                    type="text"
                    name="last_name"
                    value="{{ old('last_name', $contact->last_name ?? '') }}"
                    required
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:ring-1 focus:ring-[#9d1872] focus:outline-none"
                >
                @error('last_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">E-mail</label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email', $contact->email ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:ring-1 focus:ring-[#9d1872] focus:outline-none"
                >
                @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Teléfono</label>
                <input
                    type="text"
                    name="phone"
                    value="{{ old('phone', $contact->phone ?? ($contact->mobile ?? '')) }}"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:ring-1 focus:ring-[#9d1872] focus:outline-none"
                >
                @error('phone')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    {{-- ROL Y NOTAS --}}
    <section class="rounded-2xl bg-white p-4 border border-slate-200">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
            Rol y notas
        </h2>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700">Cargo / rol</label>
                <input
                    type="text"
                    name="role"
                    value="{{ old('role', $contact->role ?? $contact->job_title ?? '') }}"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:ring-1 focus:ring-[#9d1872] focus:outline-none"
                >
                @error('role')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-2">
                <input
                    id="is_primary"
                    type="checkbox"
                    name="is_primary"
                    value="1"
                    class="h-4 w-4 rounded border-slate-300 text-[#9d1872] focus:ring-[#9d1872]"
                    {{ old('is_primary', $contact->is_primary ?? $contact->primary ?? false) ? 'checked' : '' }}
                >
                <label for="is_primary" class="text-sm font-medium text-slate-700">
                    Contacto principal de la cuenta
                </label>
                @error('is_primary')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700">Notas</label>
                <textarea
                    name="notes"
                    rows="3"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:ring-1 focus:ring-[#9d1872] focus:outline-none"
                >{{ old('notes', $contact->notes ?? '') }}</textarea>
                @error('notes')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>
</div>
