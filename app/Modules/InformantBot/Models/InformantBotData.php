<?php

namespace App\Modules\InformantBot\Models;

use App\Modules\InformantBot\Enums\InformantBotStepEnum;
use Illuminate\Database\Eloquent\Model;

class InformantBotData extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'step' => InformantBotStepEnum::class
    ];
}
