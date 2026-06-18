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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'gogetsms' => [
    'api_key' => env('GOGETSMS_API_KEY'),
],

    'paymentpoint' => [
    'key' => env('PAYMENTPOINT_API_KEY'),
    'secret' => env('PAYMENTPOINT_API_SECRET'),
    'business_id' => env('PAYMENTPOINT_BUSINESS_ID'),
    'bank_code' => env('PAYMENTPOINT_BANK_CODE'),
],
'smsman' => [
    'token' => env('SMSMAN_API_TOKEN'),
    'usd_to_naira_rate' => env('SMSMAN_USD_TO_NAIRA_RATE', 1600),
    'service_gain' => env('SMSMAN_SERVICE_GAIN', 0),
],
'sms_activate' => [
    'api_key' => env('SMS_ACTIVATE_API_KEY'),
],
'pvapins' => [
    'key' => env('PVAPINS_API_KEY'),
    'base_url' => 'http://api.pvapins.com/user/api/',
],

    
    'daisysms' => [
    'key' => env('DAISYSMS_API_KEY'),
],
'smspool' => [
    'key' => env('SMSPOOL_KEY'),
],
'tellabot' => [
    'user' => env('TELLABOT_USER'),
    'key' => env('TELLABOT_KEY'),
],


'paystack' => [
    'key' => env('PAYSTACK_PUBLIC_KEY'),
    'secret' => env('PAYSTACK_SECRET_KEY'),
],
'flutterwave' => [
    'public_key' => env('FLW_PUBLIC_KEY'),
    'secret_key' => env('FLW_SECRET_KEY'),
    'encryption_key' => env('FLW_ENCRYPTION_KEY'),
    'redirect_url' => env('FLW_REDIRECT_URL'),
],

'sprintpay' => [
    'webkey' => env('SPRINTPAY_WEBKEY'),
    'webhook_secret' => env('SPRINTPAY_WEBHOOK_SECRET'),
],

];
