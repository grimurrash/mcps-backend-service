<?php

namespace App\Modules\InformantBot;

use App\Modules\InformantBot\Contracts\InformantBotWebhookServiceInterface;
use App\Modules\InformantBot\Service\InformantBotWebhookService;
use Illuminate\Support\ServiceProvider;

class Provider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InformantBotWebhookServiceInterface::class, InformantBotWebhookService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        $this->mergeConfigFrom(__DIR__ . '/Configs/informant_bot_configs.php', 'informant_bot');
    }
}
