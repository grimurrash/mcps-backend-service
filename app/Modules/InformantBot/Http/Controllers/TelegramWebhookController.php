<?php

namespace App\Modules\InformantBot\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\InformantBot\Contracts\InformantBotWebhookServiceInterface;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramWebhookController extends Controller
{
    public function __construct(
        private readonly InformantBotWebhookServiceInterface $botWebhookService,
    )
    {
    }

    public function webhook()
    {
        Telegram::setDefaultBot('informantBot');

        $update = Telegram::commandsHandler(true);

        $this->botWebhookService->handler($update);

        return response('ok');
    }
}
