<?php

return [
    'serverKey' => env('MIDTRANS_SERVER_KEYS'),
    'clientKey' => env('MIDTRANS_CLIENT_KEYS'),
    'isProduction' => env('MIDTRANS_IS_PRODUCTION'),
    'isSanitized' => env('MIDTRANS_IS_SANITIZED'),
    'is3ds' => env('MIDTRANS_IS_3DS'),
    'snap_url' => env('MIDTRANS_SNAP_URL'),
];
