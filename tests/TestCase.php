<?php

namespace Lartisan\FacebookDataDeletion\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lartisan\FacebookDataDeletion\FacebookDataDeletionServiceProvider;
use Lartisan\FacebookDataDeletion\Tests\Fixtures\TestDeletionHandler;
use Lartisan\FacebookDataDeletion\Tests\Fixtures\TestDeletionSubjectResolver;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            FacebookDataDeletionServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('queue.default', 'sync');
        $app['config']->set('facebook-data-deletion.app_secret', 'test-facebook-secret');
        $app['config']->set('facebook-data-deletion.resolver', TestDeletionSubjectResolver::class);
        $app['config']->set('facebook-data-deletion.deletion_handler', TestDeletionHandler::class);
        $app['config']->set('facebook-data-deletion.route.prefix', 'api/facebook');
        $app['config']->set('facebook-data-deletion.route.name_prefix', 'facebook-data-deletion');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }
}
