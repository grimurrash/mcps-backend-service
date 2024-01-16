<?php

namespace App\Modules\InformantBot\Observers;

use App\Modules\InformantBot\Contracts\InformantBotServiceInterface;
use App\Modules\InformantBot\Models\InformantBotData;
use Illuminate\Contracts\Container\BindingResolutionException;

class InformantBotDataObserver
{
    /**
     * @throws BindingResolutionException
     */
    public function created(InformantBotData $informantBotData): void
    {
        $service = app()->make(InformantBotServiceInterface::class);
        $service->saveTable($informantBotData);
    }

    /**
     * @throws BindingResolutionException
     */
    public function updated(InformantBotData $informantBotData): void
    {
        $service = app()->make(InformantBotServiceInterface::class);
        $service->saveTable($informantBotData);
    }
}
