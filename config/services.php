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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'usda' => [
        'key' => env('USDA_API_SECRET'),
    ],

    // AI nutrition coach. COACH_PROVIDER picks which model API answers questions.
    'coach' => [
        'provider' => env('COACH_PROVIDER', 'gemini'),
        'max_tokens' => (int) env('COACH_MAX_TOKENS', 2048),
        'timeout' => (int) env('COACH_TIMEOUT', 30),
        // Answered questions allowed per user per day (resets at the user's midnight).
        'daily_limit' => (int) env('COACH_DAILY_LIMIT', 20),
    ],

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-3.8-flash'),
        // Gemini 3 Flash thinks at "high" by default, and thinking shares the
        // output token budget. "low" keeps room for the answer. Set it empty
        // to use the model's default.
        'thinking_level' => env('GEMINI_THINKING_LEVEL', 'low'),
    ],

    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-5-5'),
    ],

];
