<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Factura {{ $factura->numero ?: $factura->id }}</title>
    <style>
        @page { margin: 28mm 18mm 22mm 18mm; }
        * {
            font-family: DejaVu Sans, Arial, sans-serif;
        }
        body {
            font-size: 11px;
            color: #111827;
            margin: 0;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 14px;
        }
        .logo {
            width: 160px;
        }
        .company-info {
            text-align: right;
            font-size: 10px;
            line-height: 1.4;
        }
        .section-title {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
            margin: 12px 0 6px;
            border-bottom: 1px solid #111827;
            padding-bottom: 4px;
        }
        .grid {
            width: 100%;
            border-collapse: collapse;
        }
        .grid th,
        .grid td {
            border: 1px solid #111827;
            padding: 6px;
            vertical-align: top;
        }
        .grid th {
            font-size: 9px;
            text-transform: uppercase;
            background: #f8fafc;
        }
        .muted {
            color: #6b7280;
            font-size: 9px;
        }
        .totals {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .totals td,
        .totals th {
            border: 1px solid #111827;
            padding: 6px;
            text-align: right;
        }
        .totals th {
            background: #f8fafc;
            font-size: 9px;
            text-transform: uppercase;
        }
        .footer {
            margin-top: 14px;
            font-size: 8px;
            color: #4b5563;
            border-top: 1px solid #111827;
            padding-top: 6px;
        }
        .row {
            display: flex;
            gap: 20px;
        }
        .col {
            flex: 1;
        }
        .label {
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }
        .value {
            margin-top: 2px;
        }
    </style>
</head>
<body>
    @php
        $company = config('company');
        $bank = $company['bank'] ?? [];
    @endphp
    <div class="header">
        <div>
            <img class="logo" src="{{ public_path('images/Concilia2-LOGO.png') }}" alt="Concilia2">
        </div>
        <div class="company-info">
            <div>{{ $company['address'] ?? '—' }}</div>
            <div>Tlf: {{ $company['phone'] ?? '—' }} · Fax: {{ $company['fax'] ?? '—' }}</div>
            <div>{{ $company['website'] ?? '—' }}</div>
            <div>{{ $company['email'] ?? '—' }}</div>
        </div>
    </div>

    <div class="section-title">Factura</div>
    <div class="row">
        <div class="col">
            <div class="label">Nº Factura</div>
            <div class="value">{{ $factura->numero ?: $factura->id }}</div>
        </div>
        <div class="col">
            <div class="label">Fecha factura</div>
            <div class="value">{{ $factura->fecha_factura?->format('Y-m-d') ?? '—' }}</div>
        </div>
        <div class="col">
            <div class="label">Pedido</div>
            <div class="value">{{ $factura->pedido?->numero ?? '—' }}</div>
        </div>
    </div>

    <div class="section-title">Cliente</div>
    <div class="row">
        <div class="col">
            <div class="label">CIF</div>
            <div class="value">{{ $factura->cuenta?->tax_id ?? '—' }}</div>
            <div class="label" style="margin-top: 8px;">Descripción</div>
            <div class="value">{{ $factura->descripcion ?: '—' }}</div>
        </div>
        <div class="col">
            <div class="label">Empresa</div>
            <div class="value">{{ $factura->cuenta?->billing_legal_name ?? $factura->cuenta?->legal_name ?? $factura->cuenta?->name ?? '—' }}</div>
            <div class="label" style="margin-top: 8px;">Dirección</div>
            <div class="value">
                {{ $factura->cuenta?->billing_address ?? $factura->cuenta?->address ?? $factura->cuenta?->direccion ?? '—' }}<br>
                {{ $factura->cuenta?->billing_postal_code ?? $factura->cuenta?->postal_code ?? $factura->cuenta?->codigo_postal ?? '' }}
                {{ $factura->cuenta?->billing_city ?? $factura->cuenta?->city ?? $factura->cuenta?->localidad ?? '' }}
                {{ $factura->cuenta?->billing_country ?? $factura->cuenta?->country ?? $factura->cuenta?->pais ?? '' }}
            </div>
        </div>
    </div>

    <div class="section-title">Detalle</div>
    <table class="grid">
        <thead>
            <tr>
                <th style="width: 36%">Concepto</th>
                <th style="width: 10%">Cantidad</th>
                <th style="width: 12%">Importe</th>
                <th style="width: 10%">Dto</th>
                <th style="width: 10%">IVA</th>
                <th style="width: 12%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($factura->lineas as $linea)
                <tr>
                    <td>{{ $linea->concepto ?: '—' }}</td>
                    <td style="text-align: right;">{{ number_format($linea->cantidad, 2, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($linea->precio, 2, ',', '.') }} €</td>
                    <td style="text-align: right;">{{ number_format($linea->descuento_porcentaje, 2, ',', '.') }} %</td>
                    <td style="text-align: right;">{{ number_format($linea->iva_porcentaje, 2, ',', '.') }} %</td>
                    <td style="text-align: right;">{{ $linea->subtotal !== null ? number_format($linea->subtotal, 2, ',', '.') : '—' }} €</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="muted">Sin líneas</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <thead>
            <tr>
                <th>B.I.</th>
                <th>I.V.A</th>
                <th>Cuota</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $base = $factura->importe ?? $factura->lineas->sum('subtotal');
                $total = $factura->importe_total ?? $factura->lineas->sum('importe');
                $iva = $total - $base;
            @endphp
            <tr>
                <td>{{ number_format($base ?? 0, 2, ',', '.') }} €</td>
                <td>{{ $base ? number_format(($iva / $base) * 100, 2, ',', '.') : '0,00' }} %</td>
                <td>{{ number_format($iva ?? 0, 2, ',', '.') }} €</td>
                <td>{{ number_format($total ?? 0, 2, ',', '.') }} €</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">Instrucciones de pago</div>
    <div class="row">
        <div class="col">
            <div><span class="label">Forma de pago:</span> {{ $factura->forma_pago ?: 'Transferencia bancaria' }}</div>
            <div><span class="label">Fecha vencimiento:</span> {{ $factura->fecha_vencimiento?->format('Y-m-d') ?? '—' }}</div>
            <div><span class="label">Banco:</span> {{ $bank['name'] ?? '—' }}</div>
            <div><span class="label">Dirección:</span> {{ $bank['address'] ?? '—' }}</div>
            <div><span class="label">Sucursal:</span> {{ $bank['branch'] ?? '—' }}</div>
            <div><span class="label">Beneficiario:</span> {{ $bank['beneficiary'] ?? '—' }}</div>
            <div><span class="label">IBAN:</span> {{ $bank['iban'] ?? '—' }}</div>
            <div><span class="label">A/C No:</span> {{ $bank['account_number'] ?? '—' }}</div>
            <div><span class="label">BIC:</span> {{ $bank['bic'] ?? '—' }}</div>
        </div>
        <div class="col">
            <div class="label">Nota</div>
            <div class="value">{{ $factura->instruccion_pago ?: 'IVA no sujeto por reglas de localización. Prestación de servicios fuera territorio IVA.' }}</div>
        </div>
    </div>

    <div class="footer">
        <div><strong>NOTA:</strong> {{ $company['name'] ?? '—' }} se compromete a facilitar información sobre los datos personales tratados para la prestación de servicios. Para más información consulte nuestra política de privacidad en {{ $company['website'] ?? '—' }}.</div>
        <div style="margin-top: 4px;">CIF: {{ $company['tax_id'] ?? '—' }} · Página 1/1</div>
    </div>
</body>
</html>