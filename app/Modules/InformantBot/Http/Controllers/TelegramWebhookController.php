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
        Telegram::setAccessToken(config('informant_bot.bot_token'));
    }

    public function webhook(Request $request)
    {
//        $update = Telegram::commandsHandler(true);
//
//        $this->botWebhookService->handler($update);

        Telegram::sendMessage([
            'chat_id' => '332158440',
            'text' => $request->getContent(),
            'disable_web_page_preview' => true,
        ]);

        return response('ok');
    }
}
