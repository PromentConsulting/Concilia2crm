<?php

return [
    'name' => env('COMPANY_NAME', 'Concilia2 Soluciones, S.L.'),
    'tax_id' => env('COMPANY_TAX_ID', 'B02592756'),
    'address' => env('COMPANY_ADDRESS', 'C/ Marzo 9, 02002 Albacete, España'),
    'phone' => env('COMPANY_PHONE', '(+34) 967 240 056'),
    'fax' => env('COMPANY_FAX', '(+34) 967 223 369'),
    'website' => env('COMPANY_WEBSITE', 'www.concilia2.es'),
    'email' => env('COMPANY_EMAIL', 'administracion@concilia2.es'),
    'bank' => [
        'name' => env('COMPANY_BANK_NAME', 'Banco de Sabadell, S.A.'),
        'address' => env('COMPANY_BANK_ADDRESS', 'Central 25, P.I. Campollano, 02007 Albacete, España'),
        'branch' => env('COMPANY_BANK_BRANCH', 'Oficina 5387'),
        'beneficiary' => env('COMPANY_BANK_BENEFICIARY', 'Concilia2 Soluciones, S.L.'),
        'iban' => env('COMPANY_BANK_IBAN', 'ES13 0081 5387 5700 0115 8319'),
        'account_number' => env('COMPANY_BANK_ACCOUNT_NUMBER', '0081 5387 57 0001 158319'),
        'bic' => env('COMPANY_BANK_BIC', 'BSABESBBXXX'),
    ],
];