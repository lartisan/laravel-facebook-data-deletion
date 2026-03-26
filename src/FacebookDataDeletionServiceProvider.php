<?php

namespace Lartisan\FacebookDataDeletion;

use Illuminate\Support\ServiceProvider;
use Lartisan\FacebookDataDeletion\Contracts\DeletesFacebookDeletionSubjectData;
use Lartisan\FacebookDataDeletion\Contracts\ResolvesFacebookDeletionSubject;
use Lartisan\FacebookDataDeletion\Services\ConfirmationCodeGenerator;
use Lartisan\FacebookDataDeletion\Services\SignedRequestDecoder;
use Lartisan\FacebookDataDeletion\Support\NullDeletionHandler;
use Lartisan\FacebookDataDeletion\Support\NullDeletionSubjectResolver;

class FacebookDataDeletionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/facebook-data-deletion.php', 'facebook-data-deletion');

        $this->app->singleton(ConfirmationCodeGenerator::class);
        $this->app->singleton(SignedRequestDecoder::class);

        $this->app->bind(ResolvesFacebookDeletionSubject::class, function ($app) {
            $resolverClass = $app['config']->get(
                'facebook-data-deletion.resolver',
                NullDeletionSubjectResolver::class,
            );

            return $app->make($resolverClass);
        });

        $this->app->bind(DeletesFacebookDeletionSubjectData::class, function ($app) {
            $handlerClass = $app['config']->get(
                'facebook-data-deletion.deletion_handler',
                NullDeletionHandler::class,
            );

            return $app->make($handlerClass);
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/facebook_data_deletion.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'facebook-data-deletion');

        $this->publishes([
            __DIR__.'/../config/facebook-data-deletion.php' => config_path('facebook-data-deletion.php'),
        ], 'facebook-data-deletion-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/facebook-data-deletion'),
        ], 'facebook-data-deletion-views');
    }
}
