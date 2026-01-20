<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AccountImportController extends Controller
{
    public function create(): View
    {
        return view('accounts.import');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimetypes:text/plain,text/csv,text/tsv,application/vnd.ms-excel'],
        ]);

        $file = $request->file('file');

        if (! $file->isValid()) {
            return back()->withErrors(['file' => 'El archivo no es válido.'])->withInput();
        }

        $path = $file->getRealPath();
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return back()->withErrors(['file' => 'No se ha podido leer el archivo.'])->withInput();
        }

        $header   = null;
        $imported = 0;
        $skipped  = 0;

        // Si tu CSV está separado por comas, cambia ';' por ','
        $delimiter = ';';

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if ($header === null) {
                // Primera fila = cabeceras
                // Hay dos columnas "Cargo": la primera es de empresa, la segunda del contacto
                $header     = [];
                $cargoCount = 0;

                foreach ($row as $col) {
                    $col = trim((string) $col);

                    if ($col === 'Cargo') {
                        $cargoCount++;
                        if ($cargoCount === 1) {
                            $col = 'Cargo empresa';
                        } else {
                            $col = 'Cargo contacto';
                        }
                    }

                    $header[] = $col;
                }

                continue;
            }

            // Saltar filas totalmente vacías
            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            // Asociamos cada valor con su cabecera ya “normalizada”
            $raw = [];
            foreach ($header as $index => $colName) {
                $raw[$colName] = $row[$index] ?? null;
            }

            // Mapear a columnas de Account (solo empresa)
            $data = $this->mapRowToAccountData($raw);

            // Al menos necesitamos un nombre
            if (empty($data['name'])) {
                $skipped++;
                continue;
            }

            // Guardamos toda la fila original del Excel en JSON
            $data['import_raw'] = $raw;

            // Buscar cuenta existente por nombre + NIF/CIF
            $account = Account::query()
                ->when(! empty($data['tax_id']), function ($q) use ($data) {
                    $q->where('tax_id', $data['tax_id']);
                })
                ->where('name', $data['name'])
                ->first();

            if ($account) {
                $account->fill($data);
                $account->save();
            } else {
                Account::create($data);
            }

            $imported++;
        }

        fclose($handle);

        return redirect()
            ->route('accounts.index')
            ->with('status', "Importación completada. Importadas {$imported} cuentas, omitidas {$skipped} (sin nombre).");
    }

    /**
     * Mapea la fila del Excel (solo parte de empresa) a las columnas de accounts.
     *
     * @param  array<string,mixed>  $raw
     * @return array<string,mixed>
     */
    private function mapRowToAccountData(array $raw): array
    {
        // Helper para leer un valor por cabecera exacta
        $get = function (string $header) use ($raw) {
            return array_key_exists($header, $raw) && trim((string) $raw[$header]) !== ''
                ? $raw[$header]
                : null;
        };

        $razonSocial     = $get('Razón social');
        $nombreAbreviado = $get('Nombre abreviado');

        return [
            // Nombres básicos
            'tipo_entidad'    => $this->mapTipoEntidad($get('Tipo entidad')),
            'legal_name'      => $razonSocial,
            'nombre_abreviado'=> $nombreAbreviado,

            // Campo principal de la cuenta en el CRM
            'name' => $nombreAbreviado ?: $razonSocial,

            // Identificación y contacto principal de empresa
            'tax_id'  => $get('NIF/CIF'),
            'email'   => $get('E-mail de empresa'),
            'phone'   => $get('Teléfono'),
            'fax'     => $get('Fax'),
            'website' => $get('Página web'),

            // Dirección
            'address'     => $get('Dirección'),
            'city'        => $get('Localidad'),
            'state'       => $get('Provincia'),
            'postal_code' => $get('Código postal'),
            'country'     => null, // en tu Excel no viene país; lo dejamos null

            // Tipo de empresa y actividad
            'company_type'      => $get('Tipo'),
            'products_services' => $get('Productos/Servicios'),
            'company_size_min'  => $get('Tamaño empresa (min)'),
            'company_size_max'  => $get('Tamaño empresa (max)'),
            'employee_count'    => $get('Empleados'),
            'founded_year'      => $get('Año de fundación'),

            // Igualdad / interés
            'public_contracts' => $get('Contratos públicos'),
            'equality_plan'    => $get('Plan de Igualdad'),
            'equality_mark'    => $get('Distintivo de Igualdad'),

            'interest_local'    => $get('Interés local'),
            'interest_regional' => $get('Interés regional'),
            'interest_national' => $get('Interés nacional'),
            'no_interest'       => $get('Sin interés'),

            // Calidad / RSE
            'quality'             => $get('Calidad'),
            'rse'                 => $get('RSE'),
            'otras_certificaciones'=> $get('Otras certificaciones'),

            // Cargo asociado a la empresa (la primera columna "Cargo")
            'main_contact_role' => $get('Cargo empresa'),

            // Notas / estado
            'notes'  => $get('Comentarios'),
            'estado' => $this->mapEstado($get('Estado')),

            // Datos de gestión interna
            'legacy_updated_at' => $this->parseDate($get('Fecha de actualización')),
            'sales_department'  => $get('Dpto. Comercial'),
            'cnae'              => $get('CNAE'),

            // De momento todas entran como prospect
            'lifecycle' => 'prospect',
        ];
    }

    /**
     * Intenta parsear una fecha del Excel a Y-m-d.
     */
    private function parseDate(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        // Formatos típicos de Excel exportado: 31/12/2025, 31-12-2025, 2025-12-31
        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d');
            } catch (\Exception $e) {
                // seguimos probando
            }
        }

        // Si no se puede parsear, lo dejamos null para no romper la migración
        return null;
    }

    private function mapTipoEntidad(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        return match (strtolower(trim($raw))) {
            'empresa privada', 'private', 'empresa_privada' => 'empresa_privada',
            'aapp', 'administracion publica', 'administración pública', 'public' => 'aapp',
            'sin animo de lucro', 'sin ánimo de lucro', 'sin_animo_de_lucro' => 'sin_animo_de_lucro',
            'corporacion de derecho publico', 'corporación de derecho público', 'corporacion_derecho_publico' => 'corporacion_derecho_publico',
            'particular' => 'particular',
            default => null,
        };
    }

    private function mapEstado(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        return match (strtolower(trim($raw))) {
            'inactivo', 'inactive' => 'inactivo',
            'activo', 'active', 'prospect', 'customer' => 'activo',
            default => null,
        };
    }
}