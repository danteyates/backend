<?php

declare(strict_types=1);

namespace App\Providers;

use App\Business\Contracts\UserBusinessInterface;
use App\Business\UserBusiness;
use App\Models\PersonalAccessToken;
use App\Services\Contracts\EloquentServiceInterface;
use App\Services\EloquentService;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use App\Business\AuthBusiness;
use App\Business\Contracts\AuthBusinessInterface;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            EloquentServiceInterface::class,
            EloquentService::class,
        );

        $this->app->bind(
            UserBusinessInterface::class,
            UserBusiness::class,
        );

        $this->app->bind(
            AuthBusinessInterface::class,
            AuthBusiness::class,
        );
    }

    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(
            PersonalAccessToken::class
        );
    }
}
