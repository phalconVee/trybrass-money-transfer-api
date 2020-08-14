<?php

return [

    'paystack' => [
        'api_url' => 'https://api.paystack.co/',
        'secret_key' => env('PAYSTACK_SECRET_KEY', ''),
        'public_key' => env('PAYSTACK_PUBLIC_KEY', ''),
    ],

];
