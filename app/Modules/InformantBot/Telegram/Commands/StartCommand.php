<?php
namespace App\Modules\InformantBot\Telegram\Commands;

use App\Exceptions\SuccessException;
use App\Modules\InformantBot\Contracts\InformantBotServiceInterface;
use App\Modules\InformantBot\Enums\InformantBotStepEnum;
use App\Modules\InformantBot\Models\InformantBotData;
use App\Modules\InformantBot\Services\InformantBotService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Telegram;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Keyboard\Keyboard;

class StartCommand extends Command
{
    protected string $name = 'start';
    protected string $description = 'Start Command to get you started';

    /**
     * @throws SuccessException
     */
    public function handle(): void
    {
        $update = $this->getUpdate();

        $informantBot = InformantBotData::query()->createOrFirst([
            'chat_id' => $update->getChat()->get('id')
        ], [
            'step' => InformantBotStepEnum::START,
        ]);
        $this->replyWithPhoto([
            'photo' => InputFile::create(InformantBotStepEnum::START->getPhoto()),
            'caption' =>  InformantBotStepEnum::START->getBotMessage(),
            'parse_mode' => 'HTML'
        ]);

        $markup = Keyboard::make(['resize_keyboard' => true]);

        foreach (InformantBotStepEnum::START_Q->getInlineButtons() as $buttonText) {
            $markup->row([Keyboard::button(['text' => $buttonText])]);
        }

        Telegram::sendMessage([
            'chat_id' => $informantBot->chat_id,
            'text' => InformantBotStepEnum::START_Q->getBotMessage(),
            'reply_markup' => $markup
        ]);

        $informantBot->update([
            'step' => InformantBotStepEnum::START_Q,
            'review' => null,
            'test_points' => null
        ]);
        throw new SuccessException();
    }
}