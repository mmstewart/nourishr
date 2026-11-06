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
        // Seconds for one model call, and for all calls in one question combined.
        'timeout' => (int) env('COACH_TIMEOUT', 30),
        'total_timeout' => (int) env('COACH_TOTAL_TIMEOUT', 50),
        // Rounds of tool calls before the model must answer with what it has.
        'max_tool_rounds' => (int) env('COACH_MAX_TOOL_ROUNDS', 4),
        // Answered questions allowed per user per day (resets at the user's midnight).
        'daily_limit' => (int) env('COACH_DAILY_LIMIT', 20),
    ],

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        // Flash-Lite is fast enough for tool questions to finish well within
        // the time budget; gemini-3.8-flash timed out on them in production.
        'model' => env('GEMINI_MODEL', 'gemini-3.5-flash-lite'),
        // Gemini 3 Flash thinks at "high" by default, and thinking shares the
        // output token budget. "low" keeps room for the answer. Set it empty
        // to use the model's default.
        'thinking_level' => env('GEMINI_THINKING_LEVEL', 'low'),
        // Tried once when the main model is overloaded (HTTP 500/503). Leave
        // empty to turn the fallback off.
        'fallback_model' => env('GEMINI_FALLBACK_MODEL', 'gemini-3.8-flash'),
    ],

    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-5-5'),
    ],

];
