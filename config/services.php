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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => 'b108a55df643c2050b249a4fa3ddaa6a7e71f4bb',
    ],
    
    'sparkpost_from' => [
        'name' => 'LEAP',
        'email' => 'tpis@email-1.knrint.in',
    ],

    'resend' => [
        'key' => 're_bsMCmgWq_29Lzzu7MHBodjy5XqsBM3jGW',
        'from' => [
            'address' => 'noreply@KNR Office.com',
            'name' => 'HRMS System'
        ]
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID', 'placeholder-google-client-id'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET', 'placeholder-google-secret'),
        'redirect' => env('GOOGLE_REDIRECT_URI', '/comms/auth/google/callback'),
    ],

    'outlook' => [
        'client_id' => env('OUTLOOK_CLIENT_ID', 'placeholder-outlook-client-id'),
        'client_secret' => env('OUTLOOK_CLIENT_SECRET', 'placeholder-outlook-secret'),
        'redirect' => env('OUTLOOK_REDIRECT_URI', '/comms/auth/outlook/callback'),
        'tenant' => env('OUTLOOK_TENANT', 'common'),
    ],

    'zoom' => [
        'base_url' => env('ZOOM_API_URL', 'https://api.zoom.us/v2'),
        'client_id' => env('ZOOM_CLIENT_ID'),
        'client_secret' => env('ZOOM_CLIENT_SECRET'),
    ],

    'ms_graph' => [
        'base_url' => env('MS_GRAPH_API_URL', 'https://graph.microsoft.com/v1.0'),
    ],
];
