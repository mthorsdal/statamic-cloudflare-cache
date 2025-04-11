<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cloudflare API Settings
    |--------------------------------------------------------------------------
    */
    'enabled' => env('CLOUDFLARE_CACHE_ENABLED', true),
    
    'api_token' => env('CLOUDFLARE_API_TOKEN', ''),
    
    'zone_id' => env('CLOUDFLARE_ZONE_ID', ''),
    
    /*
    |--------------------------------------------------------------------------
    | Cache Purging Settings
    |--------------------------------------------------------------------------
    */
    'purge_on' => [
        'entry_saved' => true,
        'entry_deleted' => true,
        'term_saved' => true,
        'term_deleted' => true,
        'asset_saved' => true,
        'asset_deleted' => true,
    ],

    'queue_purge' => env('CLOUDFLARE_CACHE_QUEUE_PURGE', false), // Dispatch purge jobs to the queue
    
    /*
    |--------------------------------------------------------------------------
    | Advanced Settings
    |--------------------------------------------------------------------------
    */
    'purge_urls' => true, // Purge specific URLs if possible
    'purge_everything_fallback' => true, // Fallback to purging everything if specific URLs can't be determined

    'debug' => env('CLOUDFLARE_CACHE_DEBUG', false), // Log API call attempts when purging
];
