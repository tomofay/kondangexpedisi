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
        'picked_up',
        'arrived_at_origin',
        'departed_from_origin',
        'in_transit',
        'arrived_at_destination',
        'out_for_delivery',
        'delivered',
        'failed_delivery',
        'cancelled',
        'returned',
    ],

    'shipment_status_flow' => [
        'transitions' => [
            'pending' => ['picked_up', 'arrived_at_origin', 'cancelled'],
            'picked_up' => ['arrived_at_origin', 'cancelled'],
            'arrived_at_origin' => ['departed_from_origin', 'cancelled'],
            'departed_from_origin' => ['in_transit', 'arrived_at_destination', 'cancelled'],
            'in_transit' => ['arrived_at_destination', 'cancelled', 'returned'],
            'arrived_at_destination' => ['out_for_delivery', 'cancelled', 'returned'],
            'out_for_delivery' => ['delivered', 'failed_delivery', 'cancelled', 'returned'],
            'failed_delivery' => ['out_for_delivery', 'returned', 'cancelled'],
            'delivered' => [],
            'cancelled' => [],
            'returned' => [],
        ],
        'final_statuses' => ['delivered', 'cancelled', 'returned'],
        'override_roles' => ['admin', 'manager'],
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
