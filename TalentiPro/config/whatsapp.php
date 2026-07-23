<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WhatsApp via Evolution API (Gratuito, Self-Hosted)
    |--------------------------------------------------------------------------
    | Configure a Evolution API no seu servidor e defina as variáveis abaixo
    | no arquivo .env. Quando WHATSAPP_ENABLED=false, nenhuma mensagem é
    | enviada e o sistema continua funcionando normalmente.
    |
    | Docs: https://doc.evolution-api.com
    */

    'enabled'  => env('WHATSAPP_ENABLED', false),
    'base_url' => env('WHATSAPP_BASE_URL', 'http://localhost:8080'),
    'api_key'  => env('WHATSAPP_API_KEY', ''),
    'instance' => env('WHATSAPP_INSTANCE', 'talentipro'),

];
