<?php

return [
    'enabled' => env('WHATSAPP_ENABLED', false),
    'base_url' => env('WHATSAPP_BASE_URL', 'http://localhost:8080'),
    'api_key' => env('WHATSAPP_API_KEY', ''),
    'instance' => env('WHATSAPP_INSTANCE', 'talentipro'),
];
