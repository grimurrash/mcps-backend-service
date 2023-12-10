<?php

namespace App\Modules\InformantBot\Service;

use App\Modules\InformantBot\Contracts\InformantBotWebhookServiceInterface;
use Telegram\Bot\Objects\Update;

class InformantBotWebhookService implements InformantBotWebhookServiceInterface
{
    public function handler(Update $update): void
    {
        match ($update->objectType()) {
            'callback_query' => $this->processCallback($update),
            'message' => $this->processMessage($update),
            default => null
        };
    }

    private function processCallback(): void
    {

    }

    private function processMessage(): void
    {

    }

}