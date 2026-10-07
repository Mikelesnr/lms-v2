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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'refresh_token' => env('GOOGLE_REFRESH_TOKEN'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
    ],

    'admin' => [
        'name' => env('APP_ADMIN_NAME', 'Default Admin'),
        'email' => env('APP_ADMIN_EMAIL'),
        'password' => env('APP_ADMIN_PASSWORD'),
    ],

    'user' => [
        'password' => env('APP_USER_PASSWORD'),
    ],

    'system_user' => [
        'password' => env('APP_SYSTEM_USER_PASSWORD'),
    ],

];
