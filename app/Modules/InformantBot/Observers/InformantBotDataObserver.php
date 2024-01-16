<?php

namespace App\Modules\InformantBot\Observers;

use App\Modules\InformantBot\Contracts\InformantBotServiceInterface;
use App\Modules\InformantBot\Enums\InformantBotStepEnum;
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
        $service->saveTable($informantBotData, true);
    }

    /**
     * @throws BindingResolutionException
     */
    public function updated(InformantBotData $informantBotData): void
    {
        $service = app()->make(InformantBotServiceInterface::class);
        $isForced = in_array($informantBotData->step, [
            InformantBotStepEnum::START,
            InformantBotStepEnum::START_Q,
            InformantBotStepEnum::FINISH,
            InformantBotStepEnum::START_FINISH
        ], true);
        $service->saveTable($informantBotData,$isForced);
    }
}
