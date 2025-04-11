<?php

use Eminos\StatamicCloudflareCache\Http\Client;
use Illuminate\Support\Facades\Http;
beforeEach(function () {
    Http::fake([
        'https://api.cloudflare.com/client/v4/zones/*/purge_cache' => Http::response([
            'success' => true,
        ], 200),
    ]);
});

test('command can purge all cache', function () {
    $this->mock(Client::class, function ($mock) {
        $mock->shouldReceive('purgeEverything')
             ->once()
             ->andReturn(true);
    });

    $this->artisan('cloudflare:purge')
         ->expectsOutput('Purging all Cloudflare cache...')
         ->expectsOutput('Cache purged successfully!')
         ->assertExitCode(0);
});

test('command can purge specific URL', function () {
    $this->mock(Client::class, function ($mock) {
        $mock->shouldReceive('purgeUrls')
             ->once()
             ->with(['https://example.com/test'])
             ->andReturn(true);
    });

    $this->artisan('cloudflare:purge', ['--url' => 'https://example.com/test'])
         ->expectsOutput('Purging cache for URL: https://example.com/test')
         ->expectsOutput('Cache purged successfully!')
         ->assertExitCode(0);
});

test('command handles failure', function () {
    $this->mock(Client::class, function ($mock) {
        $mock->shouldReceive('purgeEverything')
             ->once()
             ->andReturn(false); // Simulate failure
    });

    $this->artisan('cloudflare:purge')
         ->expectsOutput('Purging all Cloudflare cache...')
         ->expectsOutput('Failed to purge cache. Check logs for details.')
         ->assertExitCode(1); // Expect exit code 1 on failure
});

test('command respects disabled setting', function () {
    // Disable the cache purging
    config(['cloudflare-cache.enabled' => false]);

    // No need to mock Client here, as the command should exit early

    $this->artisan('cloudflare:purge')
         ->expectsOutput('Cloudflare Cache is disabled in configuration.')
         ->assertExitCode(1); // Expect exit code 1 when disabled
});
