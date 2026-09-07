<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
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

    'n8n' => [
        'webhook_url' => env('N8N_WEBHOOK_URL'),
        'webhook_secret' => env('N8N_WEBHOOK_SECRET'),
        'timeout' => (int) env('N8N_TIMEOUT', 12),
    ],

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'staff_chat_id' => env('TELEGRAM_STAFF_CHAT_ID'),
        'bot_secret' => env('TELEGRAM_BOT_SECRET'),
        'bot_username' => env('TELEGRAM_BOT_USERNAME', 'pro_design_perfect_bot'),
        'staff_bot_token' => env('TELEGRAM_STAFF_BOT_TOKEN'),
        'staff_bot_secret' => env('TELEGRAM_STAFF_BOT_SECRET', 'change-me-staff'),
        'staff_bot_username' => env('TELEGRAM_STAFF_BOT_USERNAME'),
    ],

    'odoo' => [
        'enabled' => (bool) env('ODOO_ENABLED', false),
        'url' => env('ODOO_URL'),
        'db' => env('ODOO_DB'),
        'username' => env('ODOO_USERNAME'),
        'api_key' => env('ODOO_API_KEY'),
        'timeout' => (int) env('ODOO_TIMEOUT', 12),
    ],

    'clickup' => [
        'token' => env('CLICKUP_TOKEN'),
        'list_id' => env('CLICKUP_LIST_ID'),
        'timeout' => (int) env('CLICKUP_TIMEOUT', 12),
    ],

];
