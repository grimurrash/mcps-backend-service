<?php

namespace App\Modules\InformantBot\Services;

use App\Modules\InformantBot\Contracts\InformantBotWebhookServiceInterface;
use App\Modules\InformantBot\Enums\InformantBotStepEnum;
use App\Modules\InformantBot\Models\InformantBotData;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class InformantBotWebhookService implements InformantBotWebhookServiceInterface
{
    private InformantBotData $informantBotData;

    private const EXCLUDE_TEXT = [
        'Продолжим',
        'Подтвердить выбор'
    ];

    public function handler(Update $update): void
    {
        $informantBotData = InformantBotData::where('chat_id', $update->getChat()->get('id'))->first();
        if (is_null($informantBotData)) {
            return;
        }
        $this->informantBotData = $informantBotData;

        if ($update->objectType() !== 'message') {
            return;
        }

        $text = $update->getMessage()->get('text');
        $messageId = $update->getMessage()->get('message_id');

        if (!$this->informantBotData->step->isCustomMessage() && !empty($this->informantBotData->step->getInlineButtons()) && !in_array($text, self::EXCLUDE_TEXT, true) && !$this->inArrayR($text, $this->informantBotData->step->getInlineButtons())) {
            $this->sendMessage('Выберите из предложенных вариантов', removeButtons: false);
            return;
        }

        if ($this->informantBotData->step->isTest()) {
            $this->processTest($text);
            return;
        }

        $this->processMessage($text, $messageId);
    }

    private function sendMessage(string $text, ?string $replyMessageId = null, array $buttons = [], bool $removeButtons = true  ): void
    {
        $params = [];

        if (!is_null($replyMessageId)) {
            $params['reply_to_message_id'] = $replyMessageId;
        }
        if (!empty($buttons)) {
            $markup = Keyboard::make(['resize_keyboard' => true]);

            foreach ($buttons as $button) {
                if (is_string($button)) {
                    $row = [Keyboard::button(['text' => $button])];
                } elseif (is_array($button)) {
                    $row = [];
                    foreach ($button as $btn) {
                        $row[] = Keyboard::button(['text' => $btn]);
                    }
                } else {
                    $row = [];
                }
                $markup->row($row);
            }
            $params['reply_markup'] = $markup;
        } elseif ($removeButtons) {
            $params['reply_markup'] = Keyboard::remove();
        }

        Telegram::sendMessage([
            'chat_id' => $this->informantBotData->chat_id,
            'text' => $text,
            'parse_mode' => 'HTML',
            ...$params
        ]);
    }

    private function inArrayR(mixed $needle, array $haystack): bool
    {
        foreach ($haystack as $arrayItem) {
            if (is_array($arrayItem)) {
                if (in_array($needle, $arrayItem, true)) {
                    return true;
                }
            } elseif (str_contains($arrayItem, trim($needle, "\n\r\t\v\0.,…"))) {
                return true;
            }
        }

        return false;
    }

    private function processTest(string $text): void
    {
        $step = $this->informantBotData->step;

        if ($step->isManyResultTest()) {
            $answerCache = Cache::get('chat' . $this->informantBotData->chat_id) ?? '';
            $answers = array_filter(explode('|', $answerCache));
            $answers[] = $text;
            if ($text !== 'Подтвердить выбор') {
                Cache::set('chat' . $this->informantBotData->chat_id, implode('|', $answers));
                $buttons = array_diff($step->getInlineButtons(), $answers);
                $markup = Keyboard::make(['resize_keyboard' => true]);
                foreach ($buttons as $button) {
                    $markup->row([Keyboard::button(['text' => $button])]);
                }
                $markup->row([Keyboard::button(['text' => 'Подтвердить выбор'])]);
                Telegram::sendMessage([
                    'chat_id' => $this->informantBotData->chat_id,
                    'reply_markup' => $markup,
                    'text' => 'Выбери ещё, или подтверди выбор'
                ]);
                return;
            }

            $text = implode('|', $answers);
        }

        if ($step->isRightTestAnswer($text)) {
            $this->informantBotData->test_points++;
            $this->informantBotData->save();
        }

        $this->moveNextStep();
    }

    private function processMessage(string $text, string $messageId): void
    {
        if ($this->informantBotData->step === InformantBotStepEnum::FINISH) {
            return;
        }

        if ($this->informantBotData->step === InformantBotStepEnum::START_Q && $text === 'Не очень хочется, но придётся') {
            $this->sendMessage(InformantBotStepEnum::START_FAIL->getBotMessage());
            $this->informantBotData->update(['step' => InformantBotStepEnum::START_FAIL]);
            return;
        }

        if ($this->informantBotData->step === InformantBotStepEnum::START_FAIL) {
            $this->sendMessage(InformantBotStepEnum::START_FAIL->getReplyBotMessage(), replyMessageId: $messageId);
            $this->informantBotData->update(['step' => InformantBotStepEnum::FINISH, 'review' => $text]);
            return;
        }

        if ($this->informantBotData->step === InformantBotStepEnum::REVIEW) {
            $this->sendMessage(InformantBotStepEnum::REVIEW->getReplyBotMessage(), replyMessageId: $messageId);
            $this->informantBotData->update(['step' => InformantBotStepEnum::FINISH, 'review' => $text]);
            return;
        }

        if ($this->informantBotData->step === InformantBotStepEnum::S3_Q && $text !== 'все перечисленное верно') {
            $this->sendMessage('Подумай, я верю, что ты справишься', replyMessageId: $messageId, removeButtons: false);
            return;
        }

        if ($this->informantBotData->step === InformantBotStepEnum::S6_Q && $text !== 'Фамилия, дата рождения, данные о составе семьи, номер свидетельства о рождении') {
            $this->sendMessage('Попробуй ещё раз, я верю в тебя', replyMessageId: $messageId, removeButtons: false);
            return;
        }

        if ($this->informantBotData->step === InformantBotStepEnum::S7_Q) {
            $message = 'Хорошо, я сейчас тебе всё расскажу';
            if (str_contains($text, 'биометричес')) {
                $message = 'Молодец, уважаемый искатель знаний, верно';
            }

            $this->sendMessage($message, replyMessageId: $messageId);
        }

        if (($this->informantBotData->step === InformantBotStepEnum::S5_Q) && $text !== 'Продолжим') {
            $message = match ($text) {
                'Да, конечно' => 'Я не сомневался в тебе, уважаемый искатель знаний',
                'Нет, но надо' => 'Думаю, что после прохождения курса, ты поймешь важность этих знаний',
                'Нет, устал от этой информации' => "Тогда, уважаемый искатель знаний, сделай паузу и послушай <a href='https://youtu.be/OT5zz9F7HSY'>музыку</a> и мы продолжим - пауза 3 минуты",
            };
            $buttons = [];
            if ($text === 'Нет, устал от этой информации') {
                $buttons = ['Продолжим'];
            }
            $this->sendMessage($message, replyMessageId: $messageId, buttons: $buttons);
            return;
        }

        $replyBotMessage = $this->informantBotData->step->getReplyBotMessage();

        if (!is_null($replyBotMessage)) {
            $this->sendMessage($replyBotMessage, replyMessageId: $messageId);
        }

        $this->moveNextStep();
    }

    private function moveNextStep(): void
    {
        $step = $this->informantBotData->step;
        while (true) {
            $step = $step->nextStep();
            if (is_null($step)) {
                return;
            }

            if (is_null($step->getPhoto())) {
                $this->sendMessage($step->getBotMessage(), buttons: $step->getInlineButtons());
            } else {
                Telegram::sendPhoto([
                    'chat_id' => $this->informantBotData->chat_id,
                    'photo' => InputFile::create($step->getPhoto()),
                    'caption' => $step->getBotMessage()
                ]);
            }

            if ($step->isCustomMessage() || !empty($step->getInlineButtons())) {
                break;
            }
        }

        $this->informantBotData->update(['step' => $step]);
    }
}