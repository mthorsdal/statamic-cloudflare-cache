<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cloudflare API Settings
    |--------------------------------------------------------------------------
    */
    'enabled' => env('CLOUDFLARE_CACHE_ENABLED', true),
    
    'api_token' => env('CLOUDFLARE_API_TOKEN', ''),
    
    // Single zone configuration (backward compatibility)
    'zone_id' => env('CLOUDFLARE_ZONE_ID', ''),
    
    /*
    |--------------------------------------------------------------------------
    | Multi-Zone Configuration
    |--------------------------------------------------------------------------
    |
    | Map domains or Statamic site handles to Cloudflare zone IDs.
    | The package will automatically match URLs to the correct zone.
    |
    | Example:
    | 'zones' => [
    |     'example.com' => 'zone_id_123',
    |     'example.fr' => 'zone_id_456',
    |     'default' => 'zone_id_123',  // Statamic site handle
    |     'french' => 'zone_id_456',   // Statamic site handle
    | ],
    */
    'zones' => [
        // Domain or site handle => Zone ID mapping
    ],
    
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
