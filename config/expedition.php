<?php

return [
    'roles' => [
        'admin',
        'kasir',
        'courier',
        'manager',
        'customer',
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
            'apply_destination_multiplier' => true,
        ],
    ],
];
