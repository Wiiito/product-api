<?php

namespace App\Providers;

use App\Interfaces\Auth\AuthServiceInterface;
use App\Interfaces\Cache\CacheKeyGeneratorInterface;
use App\Interfaces\Products\ProductRepositoryInterface;
use App\Interfaces\Products\ProductServiceInterface;
use App\Repositories\Products\ProductRepository;
use App\Services\Auth\AuthService;
use App\Services\Cache\CacheKeyGenerator;
use App\Services\Products\ProductService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CacheKeyGeneratorInterface::class, CacheKeyGenerator::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(ProductServiceInterface::class, ProductService::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
