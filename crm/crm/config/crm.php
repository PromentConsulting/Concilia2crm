<?php

return [
    'contact_roles' => [
        'decisor',
        'economic_approver',
        'influencer',
        'user',
        'procurement',
        'technical',
        'management',
        'hr',
        'finance',
        'other',
    ],
    'contact_statuses' => [
        'active','inactive','bounced','marketing_opt_out','unreachable'
    ],
    'account_statuses' => [
        'prospect','customer','dormant','inactive','blocked','partner','vendor'
    ],
    'risk_levels' => ['none','low','medium','high'],
    'decision_levels' => ['decisor','economic_approver','influencer'],
    'preferred_channels' => ['email','phone','sms','whatsapp'],
    'legal_basis' => ['consent','legitimate_interest','contractual','legal_obligation','vital_interest'],
    'e_invoice_channels' => ['public','private','peppol'],
];
