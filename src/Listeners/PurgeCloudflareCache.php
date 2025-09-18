<?php

namespace Eminos\StatamicCloudflareCache\Listeners;

use Eminos\StatamicCloudflareCache\Jobs\PurgeCloudflareCacheJob; // Updated job import namespace
use Eminos\StatamicCloudflareCache\Http\Client; // Updated client import namespace
use Statamic\Events\Event;
use Statamic\Events\EntrySaved;
use Statamic\Events\EntryDeleted;
use Statamic\Events\TermSaved;
use Statamic\Events\TermDeleted;
use Statamic\Events\AssetSaved;
use Statamic\Events\AssetDeleted;
use Statamic\Facades\URL;
use Illuminate\Support\Facades\Log; // Already present, but good to confirm

class PurgeCloudflareCache
{
    protected Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function handle(Event $event): void
    {
        if (!config('cloudflare-cache.enabled')) {
            return;
        }

        if (!$this->shouldHandleEvent($event)) {
            return;
        }

        $urls = $this->getUrlsToPurge($event);

        if (config('cloudflare-cache.debug')) {
            Log::debug('Cloudflare Cache: Event triggered', [
                'event' => get_class($event),
                'urls' => $urls,
                'queue_enabled' => config('cloudflare-cache.queue_purge'),
            ]);
        }

        if (config('cloudflare-cache.queue_purge')) {
            $this->dispatchJob($urls);
        } else {
            $this->purgeSynchronously($urls);
        }
    }

    protected function dispatchJob(array $urls): void
    {
        $jobPayload = null;

        if (!empty($urls) && config('cloudflare-cache.purge_urls')) {
            $jobPayload = $urls;
            if (config('cloudflare-cache.debug')) {
                Log::debug('[Cloudflare Cache] Dispatching job to purge URLs: ' . implode(', ', $urls));
            }
        } elseif (config('cloudflare-cache.purge_everything_fallback')) {
            if (config('cloudflare-cache.debug')) {
                Log::debug('[Cloudflare Cache] Dispatching job to purge everything.');
            }
        } else {
            if (config('cloudflare-cache.debug')) {
                Log::debug('[Cloudflare Cache] Skipping job dispatch (no URLs and fallback disabled).');
            }
            return;
        }

        PurgeCloudflareCacheJob::dispatch($jobPayload);
    }

    protected function purgeSynchronously(array $urls): void
    {
        if (config('cloudflare-cache.debug')) {
            Log::debug('[Cloudflare Cache] Performing synchronous purge.');
        }

        if (!empty($urls) && config('cloudflare-cache.purge_urls')) {
            if (config('cloudflare-cache.debug')) {
                Log::debug('[Cloudflare Cache] Synchronously purging URLs: ' . implode(', ', $urls));
            }
            $this->client->purgeUrls($urls);
            return;
        }

        if (config('cloudflare-cache.purge_everything_fallback')) {
            if (config('cloudflare-cache.debug')) {
                Log::debug('[Cloudflare Cache] Synchronously purging everything.');
            }
            $this->client->purgeEverything();
        }
    }

    /**
     * Determine if we should handle this event based on configuration.
     *
     * @param Event $event The Statamic event triggered.
     * @return bool
     */
    protected function shouldHandleEvent(Event $event): bool
    {
        $eventClass = get_class($event);
        $eventMap = [
            'Statamic\Events\EntrySaved' => 'entry_saved',
            'Statamic\Events\EntryDeleted' => 'entry_deleted',
            'Statamic\Events\TermSaved' => 'term_saved',
            'Statamic\Events\TermDeleted' => 'term_deleted',
            'Statamic\Events\AssetSaved' => 'asset_saved',
            'Statamic\Events\AssetDeleted' => 'asset_deleted',
        ];

        $configKey = $eventMap[$eventClass] ?? null;

        return $configKey && config("cloudflare-cache.purge_on.{$configKey}");
    }

    /**
     * Get URLs to purge based on the event's subject (Entry, Term, Asset).
     *
     * @param Event $event The Statamic event triggered.
     * @return array An array of absolute URLs to purge.
     */
    protected function getUrlsToPurge(Event $event): array
    {
        $urls = [];

        if ($event instanceof EntrySaved || $event instanceof EntryDeleted) {
            $entry = $event->entry;
            if ($entry && $entry->url()) {
                $urls[] = URL::makeAbsolute($entry->url());
                if ($entry->collection()) {
                    $urls[] = URL::makeAbsolute($entry->collection()->url());
                }
            }
        }

        if ($event instanceof TermSaved || $event instanceof TermDeleted) {
            $term = $event->term;
            if ($term && $term->url()) {
                $urls[] = URL::makeAbsolute($term->url());
                if ($term->taxonomy()) {
                    $urls[] = URL::makeAbsolute($term->taxonomy()->url());
                }
            }
        }

        if ($event instanceof AssetSaved || $event instanceof AssetDeleted) {
            $asset = $event->asset;
            if ($asset && $asset->url()) {
                $urls[] = URL::makeAbsolute($asset->url());
            }
        }

        $urls = array_filter($urls);

        return array_unique($urls);
    }
}
