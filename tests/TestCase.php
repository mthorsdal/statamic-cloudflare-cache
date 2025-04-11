<?php

namespace Eminos\StatamicCloudflareCache\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Eminos\StatamicCloudflareCache\CloudflareCacheServiceProvider;
use Statamic\Providers\StatamicServiceProvider;
// Removed unused imports: PurgeCache, Client, Kernel

abstract class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            StatamicServiceProvider::class,
            CloudflareCacheServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Setup Cloudflare Cache config
        $app['config']->set('cloudflare-cache.enabled', true);
        $app['config']->set('cloudflare-cache.api_token', 'test-token');
        $app['config']->set('cloudflare-cache.zone_id', 'test-zone-id');

        // Set base URL for URL generation
        $app['config']->set('app.url', 'http://localhost');
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
