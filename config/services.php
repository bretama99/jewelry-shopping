<?php
// config/services.php - Add metal price API configuration

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

    'stripe' => [
        'model' => App\Models\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    'paypal' => [
        'mode' => env('PAYPAL_MODE', 'sandbox'), // sandbox or live
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'app_id' => env('PAYPAL_APP_ID'),
        'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Metal Price API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the metal price API service that provides live
    | precious metal prices for gold, silver, palladium, and platinum.
    |
    */

    'metal_price_api' => [
        'key' => env('METAL_PRICE_API_KEY', 'd68f51781cca05150ab380fbea59224c'),
        'base_url' => env('METAL_PRICE_API_URL', 'https://api.metalpriceapi.com/v1'),
        'cache_minutes' => env('METAL_PRICE_CACHE_MINUTES', 5),
        'timeout' => env('METAL_PRICE_API_TIMEOUT', 10),
        'fallback_prices' => [
            'XAU' => 3300.00, // Gold ~$3300/oz
            'XAG' => 33.00,   // Silver ~$33/oz
            'XPD' => 980.00,  // Palladium ~$980/oz
            'XPT' => 1075.00, // Platinum ~$1075/oz
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency Exchange API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for currency exchange rate API to convert USD to AUD.
    |
    */

    'exchange_rate_api' => [
        'base_url' => env('EXCHANGE_RATE_API_URL', 'https://api.exchangerate-api.com/v4/latest'),
        'cache_hours' => env('EXCHANGE_RATE_CACHE_HOURS', 1),
        'timeout' => env('EXCHANGE_RATE_API_TIMEOUT', 10),
        'fallback_aud_rate' => env('FALLBACK_AUD_RATE', 1.45),
    ],

    /*
    |--------------------------------------------------------------------------
    | Social Authentication Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for social authentication providers like Google, Facebook.
    |
    */

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI', '/auth/facebook/callback'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Marketing Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for email marketing services like Mailchimp, ConvertKit.
    |
    */

    'mailchimp' => [
        'api_key' => env('MAILCHIMP_API_KEY'),
        'list_id' => env('MAILCHIMP_LIST_ID'),
        'data_center' => env('MAILCHIMP_DATA_CENTER'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for SMS services like Twilio for notifications.
    |
    */

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from' => env('TWILIO_FROM'),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for cloud storage services like AWS S3, Cloudinary.
    |
    */

    'cloudinary' => [
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
        'api_key' => env('CLOUDINARY_API_KEY'),
        'api_secret' => env('CLOUDINARY_API_SECRET'),
        'secure' => env('CLOUDINARY_SECURE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for analytics services like Google Analytics.
    |
    */

    'google_analytics' => [
        'tracking_id' => env('GOOGLE_ANALYTICS_TRACKING_ID'),
        'gtag_id' => env('GOOGLE_GTAG_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for search services like Algolia, Elasticsearch.
    |
    */

    'algolia' => [
        'app_id' => env('ALGOLIA_APP_ID'),
        'secret' => env('ALGOLIA_SECRET'),
    ],

];

// .env.example - Add these environment variables

/*
# Metal Price API Configuration
METAL_PRICE_API_KEY=30dd28a124d5b751efddcc5d499154ef
METAL_PRICE_API_URL=https://api.metalpriceapi.com/v1
METAL_PRICE_CACHE_MINUTES=5
METAL_PRICE_API_TIMEOUT=10

# Currency Exchange Configuration
EXCHANGE_RATE_API_URL=https://api.exchangerate-api.com/v4/latest
EXCHANGE_RATE_CACHE_HOURS=1
EXCHANGE_RATE_API_TIMEOUT=10
FALLBACK_AUD_RATE=1.45

# Payment Gateways
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

PAYPAL_MODE=sandbox
PAYPAL_CLIENT_ID=
PAYPAL_CLIENT_SECRET=
PAYPAL_WEBHOOK_ID=

# Social Authentication
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"

FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=
FACEBOOK_REDIRECT_URI="${APP_URL}/auth/facebook/callback"

# Email Marketing
MAILCHIMP_API_KEY=
MAILCHIMP_LIST_ID=
MAILCHIMP_DATA_CENTER=

# SMS Notifications
TWILIO_SID=
TWILIO_TOKEN=
TWILIO_FROM=

# Cloud Storage
CLOUDINARY_CLOUD_NAME=
CLOUDINARY_API_KEY=
CLOUDINARY_API_SECRET=
CLOUDINARY_SECURE=true

# Analytics
GOOGLE_ANALYTICS_TRACKING_ID=
GOOGLE_GTAG_ID=

# Search
ALGOLIA_APP_ID=
ALGOLIA_SECRET=
*/
