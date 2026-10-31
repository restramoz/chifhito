<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'whatsapp' => [
        // Official number intentionally blank until the owner supplies it.
        // When blank/invalid, CTAs render non-link contact text (no wa.me URL).
        'phone' => env('WHATSAPP_PHONE', ''),
        'default_message' => env('WHATSAPP_DEFAULT_MESSAGE', 'Halo, saya ingin konsultasi mengenai program pelatihan profesional Chifheto.'),
        // Neutral fallback shown in place of a broken/non-working WhatsApp link.
        'fallback_text' => env('WHATSAPP_FALLBACK_TEXT', 'Hubungi admin untuk informasi konsultasi.'),
        // Public business numbers for article callouts (deterministic).
        'callout_contacts' => [
            '08118500177',
            '081399810272',
            '087894580806',
            '087894580649',
        ],
    ],

    // Ollama LLM service configuration for article generation
    'ollama' => [
        // Base URL, e.g. http://localhost:11434/api
        'base_url' => env('OLLAMA_BASE_URL'),
        // Model name, e.g. llama2
        'model' => env('OLLAMA_MODEL'),
        // Optional API key if Ollama is behind auth proxy
        'key' => env('OLLAMA_API_KEY'),
        // Request timeout in seconds
        'timeout' => env('OLLAMA_TIMEOUT', 60),
    ],

];
