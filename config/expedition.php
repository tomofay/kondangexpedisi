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

    'tracking_number' => [
        'prefix' => 'SXP',
        'date_format' => 'Ymd',
    ],
];
