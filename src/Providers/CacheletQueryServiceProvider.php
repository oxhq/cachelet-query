<?php

namespace Oxhq\Cachelet\Query\Providers;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Support\ServiceProvider;
use Oxhq\Cachelet\CacheletManager;
use Oxhq\Cachelet\Query\Support\QueryCacheletManager;
use Oxhq\Cachelet\Query\Support\QueryPayloadFactory;

class CacheletQueryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(QueryPayloadFactory::class, function ($app) {
            return new QueryPayloadFactory((array) $app['config']->get('cachelet', []));
        });

        $this->app->singleton(QueryCacheletManager::class, function ($app) {
            return new QueryCacheletManager(
                (array) $app['config']->get('cachelet', []),
                $app->make(QueryPayloadFactory::class),
            );
        });

        $this->app->alias(QueryCacheletManager::class, 'cachelet.query');
    }

    public function boot(): void
    {
        CacheletManager::macro('forQuery', function (EloquentBuilder|BaseBuilder $query, ?string $prefix = null) {
            return app(QueryCacheletManager::class)->for($query, $prefix);
        });

        EloquentBuilder::macro('cachelet', function (?string $prefix = null) {
            return app(QueryCacheletManager::class)->for($this, $prefix);
        });

        BaseBuilder::macro('cachelet', function (?string $prefix = null) {
            return app(QueryCacheletManager::class)->for($this, $prefix);
        });

        EloquentBuilder::macro('rememberWithCachelet', function (?string $prefix = null) {
            return app(QueryCacheletManager::class)->for($this, $prefix)->rememberQuery();
        });

        BaseBuilder::macro('rememberWithCachelet', function (?string $prefix = null) {
            return app(QueryCacheletManager::class)->for($this, $prefix)->rememberQuery();
        });
    }
}
