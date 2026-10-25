<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Contact Number
    |--------------------------------------------------------------------------
    | Formatted as international number without '+' (e.g. 6281234567890).
    */
    'wa_number' => env('WA_NUMBER', '6281234567890'),

    /*
    |--------------------------------------------------------------------------
    | Fresh Bake Schedule Configurations
    |--------------------------------------------------------------------------
    */
    'schedule' => [
        'default_lead_time_days' => 1,      // Minimal H-1
        'custom_cake_lead_time_days' => 3, // Minimal H-3
        'cutoff_hour' => 18,               // Pukul 18:00 WITA
        'default_slot_capacity' => 5,      // 5 cake per slot
        'time_slots' => [
            '10:00' => '10.00 – 12.00',
            '13:00' => '13.00 – 15.00',
            '16:00' => '16.00 – 18.00',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Midtrans Payment Gateway
    |--------------------------------------------------------------------------
    */
    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        'is_sanitized' => true,
        'is_3ds' => true,
    ],
];
