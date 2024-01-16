<?php

namespace App\Modules\InformantBot;

use App\Modules\InformantBot\Contracts\InformantBotServiceInterface;
use App\Modules\InformantBot\Contracts\InformantBotWebhookServiceInterface;
use App\Modules\InformantBot\Models\InformantBotData;
use App\Modules\InformantBot\Observers\InformantBotDataObserver;
use App\Modules\InformantBot\Services\InformantBotService;
use App\Modules\InformantBot\Services\InformantBotWebhookService;
use Illuminate\Support\ServiceProvider;

class Provider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InformantBotWebhookServiceInterface::class, InformantBotWebhookService::class);
        $this->app->bind(InformantBotServiceInterface::class, InformantBotService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        $this->loadRoutesFrom(__DIR__ . '/routes.php');

        InformantBotData::observe(InformantBotDataObserver::class);
    }
}
