<?php

namespace App\Modules\InformantBot\Contracts;

use App\Modules\InformantBot\Models\InformantBotData;

interface InformantBotServiceInterface
{
    public function saveTable(InformantBotData $informantBotData, bool $isForced): void;
}