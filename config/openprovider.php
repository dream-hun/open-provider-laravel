<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenProvider API Configuration
    |--------------------------------------------------------------------------
    */

    'url' => env('OPENPROVIDER_URL', 'https://api.openprovider.eu'),

    'username' => env('OPENPROVIDER_USERNAME'),

    'password' => env('OPENPROVIDER_PASSWORD'),

    'hash' => env('OPENPROVIDER_HASH'),

    'timeout' => env('OPENPROVIDER_TIMEOUT', 1000),
];