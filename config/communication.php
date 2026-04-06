<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Notification Channels
    |--------------------------------------------------------------------------
    |
    | Supported: "email", "sms", "whatsapp"
    |
    */

    'default' => env('NOTIFICATION_DRIVER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | OTP Settings
    |--------------------------------------------------------------------------
    |
    | "otp_bypass_enabled" allows using a fixed OTP (123456) for testing.
    |
    */
    'otp' => [
        'bypass_enabled' => env('OTP_BYPASS_ENABLED', true),
        'bypass_value' => '123456',
        'expiry_minutes' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Logic
    |--------------------------------------------------------------------------
    */
    'max_retries' => 3,

    /*
    |--------------------------------------------------------------------------
    | Channel Configurations
    |--------------------------------------------------------------------------
    */

    'channels' => [
        'email' => [
            'provider' => env('MAIL_MAILER', 'smtp'),
        ],

        'sms' => [
            'provider' => env('SMS_PROVIDER', 'twilio'),
            'twilio' => [
                'sid' => env('TWILIO_SID'),
                'token' => env('TWILIO_TOKEN'),
                'from' => env('TWILIO_FROM'),
            ],
            // Add other providers here
        ],

        'whatsapp' => [
            'provider' => env('WHATSAPP_PROVIDER', 'meta'),
            'meta' => [
                'token' => env('META_WHATSAPP_TOKEN'),
                'phone_number_id' => env('META_WHATSAPP_PHONE_ID'),
            ],
        ],
    ],
];
