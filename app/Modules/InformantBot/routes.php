<?php

use App\Modules\InformantBot\Http\Controllers\TelegramWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/informant-bot/webhook', [TelegramWebhookController::class, 'webhook']);