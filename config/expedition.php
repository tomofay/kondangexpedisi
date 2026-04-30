<?php

return [
    'roles' => [
        'admin',
        'manager',
        'kasir',
        'courier',
        'customer',
    ],

    'role_hierarchy' => [
        'admin' => [
            'label' => 'Admin Pusat',
            'description' => 'Akses baca laporan, kelola sistem (users, rate card approval, audit logs)',
            'can_modify_shipments' => false,
            'can_modify_payments' => false,
            'scope' => 'global',
        ],
        'manager' => [
            'label' => 'Manager Cabang',
            'description' => 'Full CRUD di cabangnya, request rate card ke admin, approve kasir',
            'can_modify_shipments' => true,
            'can_modify_payments' => true,
            'scope' => 'branch',
        ],
        'kasir' => [
            'label' => 'Kasir Cabang',
            'description' => 'Create shipment/payment, edit butuh approval manager',
            'can_modify_shipments' => true,
            'can_modify_payments' => true,
            'scope' => 'branch',
        ],
    ],

    'approval_flow' => [
        'kasir_edit' => [
            'approved_by' => 'manager',
            'scope' => 'same_branch',
        ],
        'rate_card_change' => [
            'approved_by' => 'admin',
        ],
        'final_status' => [
            'approved_by' => 'manager',
            'scope' => 'same_branch',
        ],
    ],

    'payment_methods' => [
        'midtrans',
        'cash',
        'transfer',
        'e_wallet',
    ],

    'shipment_statuses' => [
        'pending',
        'in_transit',
        'out_for_delivery',
        'delivered',
        'cancelled',
        'returned',
    ],

    'shipment_status_flow' => [
        'transitions' => [
            'pending' => ['in_transit', 'cancelled'],
            'in_transit' => ['out_for_delivery', 'cancelled', 'returned'],
            'out_for_delivery' => ['delivered', 'cancelled', 'returned'],
            'delivered' => [],
            'cancelled' => [],
            'returned' => [],
        ],
        'final_statuses' => ['delivered', 'cancelled', 'returned'],
        'override_roles' => ['admin'],
    ],

    'tracking_number' => [
        'prefix' => 'SXP',
        'date_format' => 'Ymd',
    ],

    'pricing' => [
        'fallback' => [
            'enabled' => true,
            'base_price' => 15000,
            'per_kg_price' => 7000,
            'apply_destination_multiplier' => false,
        ],
    ],
];
