<?php

return [

    'api_token' => env('INVENTORY_API_TOKEN'),

    'pos_user_email' => env('INVENTORY_POS_USER_EMAIL', 'pos-integration@system.local'),

    'pos_url' => rtrim(env('POS_SERVICE_URL', 'http://127.0.0.1:8000'), '/'),

];
