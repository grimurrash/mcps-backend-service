<?php

namespace App\Modules\InformantBot\Contracts;

use Telegram\Bot\Objects\Update;

interface InformantBotWebhookServiceInterface
{
    public function handler(Update $update): void;
}