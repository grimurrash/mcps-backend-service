<?php

namespace App\Modules\InformantBot;

use App\Modules\InformantBot\Contracts\InformantBotServiceInterface;
use App\Modules\InformantBot\Enums\InformantBotStepEnum;
use App\Modules\InformantBot\Models\InformantBotData;
use Illuminate\Contracts\Container\BindingResolutionException;

class InformantBotDataObserver
{
    /**
     * @throws BindingResolutionException
     */
    public function updated(InformantBotData $informantBotData): void
    {
        if ($informantBotData->step === InformantBotStepEnum::FINISH) {
            $service = app()->make(InformantBotServiceInterface::class);
            $service->saveTable($informantBotData);
        }
    }
}
