<?php

namespace App\Modules\InformantBot\Enums;

enum InformantBotStepEnum: int
{
    case START = 0;
    case S1 = 1;
    case S2 = 2;

    public function getBotMessage(): string
    {
        return match ($this) {
            self::START => 'Приветствуем тебя, уважаемый искатель знаний! (анимация, вид смайлика)
<br/> Я, информационный чат-бот «Информатор». Приятно познакомиться! (анимация, вид смайлика)
<br/> Я помогу тебе получить знания по информационной безопасности.
<br/>Знание основ теории информационной безопасности способствует умелым действиям в решении практических вопросов защиты информации в профессиональной деятельности.',
            self::S1 => 'Ты готов получить знания?',
            self::S2 => throw new \Exception('To be implemented'),
        };
    }

    public function getInlineButtons(): array
    {
        return match ($this) {
            self::START => [],
            self::S1 => [
                1 => 'Всегда готов!',
                2 => 'Ну ладно, буду учиться',
                3 => 'Не очень хочется, но придётся'
            ],
            self::S2 => throw new \Exception('To be implemented'),
        };
    }
}
