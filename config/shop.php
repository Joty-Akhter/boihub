<?php

return [
    'name' => env('SHOP_NAME', 'Quire'),
    'tagline' => env('SHOP_TAGLINE', 'Books, bound with care.'),

    // Prices are stored as decimals; this symbol is only used for display.
    'currency' => env('SHOP_CURRENCY', '৳'),

    'shipping' => [
        'flat_rate' => (float) env('SHOP_SHIPPING_FLAT', 60),
        'free_over' => (float) env('SHOP_SHIPPING_FREE_OVER', 1500),
    ],

    'low_stock_threshold' => (int) env('SHOP_LOW_STOCK', 5),
    'max_quantity_per_item' => 10,
];
