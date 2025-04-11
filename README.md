# Statamic Cloudflare Cache

Automatically purge your Cloudflare cache when content changes in Statamic.

## Features

- Automatically purges Cloudflare cache when Statamic content changes.
- Configurable events that trigger cache purging.
- Optional queuing of purge jobs for background processing.
- CLI command for manual cache purging.
- Simple configuration.

## Installation

You can install the package via composer:

```bash
composer require eminos/statamic-cloudflare-cache
```

Publish the config file:

```bash
php artisan vendor:publish --tag="cloudflare-cache-config"
```

## Configuration

Add the following environment variables to your `.env` file:

```
CLOUDFLARE_API_TOKEN=your-api-token
CLOUDFLARE_ZONE_ID=your-zone-id
CLOUDFLARE_CACHE_ENABLED=true
CLOUDFLARE_CACHE_QUEUE_PURGE=false # Optional: Set to true to dispatch purges to the queue
CLOUDFLARE_CACHE_DEBUG=false # Optional: Set to true to log API calls
```

You can get your API token and Zone ID from the Cloudflare dashboard:

1. **API Token**: Log in to your Cloudflare dashboard, go to "My Profile" > "API Tokens" and create a token with the "Zone.Cache Purge" permission.

2. **Zone ID**: Go to your domain's overview page in Cloudflare. The Zone ID is displayed in the right sidebar under "API" section.

## Usage

### Automatic Cache Purging

Once configured, the addon will automatically purge the Cloudflare cache when content changes in Statamic. By default, it listens for the following events:

- Entry saved/deleted
- Term saved/deleted
- Asset saved/deleted

You can configure which events trigger cache purging in the config file.

#### Queued Purging

If you prefer to handle cache purging in the background to avoid potential delays during web requests, you can enable queued purging. Set the `CLOUDFLARE_CACHE_QUEUE_PURGE` environment variable to `true` or set `'queue_purge' => true` in the configuration file.

When enabled, purge operations triggered by events will be dispatched as jobs to your application's queue. **Note:** This requires you to have a queue worker running (`php artisan queue:work`).

### Manual Cache Purging

You can manually purge the cache using the following command:

```bash
# Purge all cache
php please cloudflare:purge

# Purge specific URL
php please cloudflare:purge --url=https://example.com/specific-page
```

## Advanced Configuration

The published configuration file (`config/cloudflare-cache.php`) allows you to customize the behavior of the addon:

```php
return [
    // Enable or disable the addon
    'enabled' => env('CLOUDFLARE_CACHE_ENABLED', true),
    
    // Cloudflare API credentials
    'api_token' => env('CLOUDFLARE_API_TOKEN', ''),
    'zone_id' => env('CLOUDFLARE_ZONE_ID', ''),
    
    // Configure which events trigger cache purging
    'purge_on' => [
        'entry_saved' => true,
        'entry_deleted' => true,
        'term_saved' => true,
        'term_deleted' => true,
        'asset_saved' => true,
        'asset_deleted' => true,
    ],

    // Dispatch purge jobs to the queue instead of running synchronously
    'queue_purge' => env('CLOUDFLARE_CACHE_QUEUE_PURGE', false),
    
    // Advanced settings
    'purge_urls' => true, // Purge specific URLs if possible
    'purge_everything_fallback' => true, // Fallback to purging everything if specific URLs can't be determined
    'debug' => env('CLOUDFLARE_CACHE_DEBUG', false), // Log API call attempts when purging
];
```

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
