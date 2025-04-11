<?php

namespace Eminos\StatamicCloudflareCache\Commands;

use Illuminate\Console\Command;
use Eminos\StatamicCloudflareCache\Http\Client;
use Statamic\Console\RunsInPlease;

class PurgeCache extends Command
{
    use RunsInPlease;

    protected $signature = 'cloudflare:purge {--url= : Specific URL to purge}';

    protected $description = 'Purge Cloudflare cache';

    protected Client $client;

    public function __construct(Client $client)
    {
        parent::__construct();
        $this->client = $client;
    }

    public function handle(): int
    {
        if (!config('cloudflare-cache.enabled')) {
            $this->error('Cloudflare Cache is disabled in configuration.');
            return 1;
        }

        $url = $this->option('url');

        if ($url) {
            $this->info("Purging cache for URL: {$url}");
            $result = $this->client->purgeUrls([$url]);
        } else {
            $this->info('Purging all Cloudflare cache...');
            $result = $this->client->purgeEverything();
        }

        if ($result) {
            $this->info('Cache purged successfully!');
            return 0;
        }

        $this->error('Failed to purge cache. Check logs for details.');
        return 1;
    }
}
