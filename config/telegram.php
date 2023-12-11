<?php

use App\Modules\InformantBot\Telegram\Commands\StartCommand;
use Telegram\Bot\Commands\HelpCommand;

return [
    /*
    |--------------------------------------------------------------------------
    | Your Telegram Bots
    |--------------------------------------------------------------------------
    | You may use multiple bots at once using the manager class. Each bot
    | that you own should be configured here.
    |
    | Here are each of the telegram bots config parameters.
    |
    | Supported Params:
    |
    | - name: The *personal* name you would like to refer to your bot as.
    |
    |       - token:    Your Telegram Bot's Access Token.
                        Refer for more details: https://core.telegram.org/bots#botfather
    |                   Example: (string) '123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11'.
    |
    |       - commands: (Optional) Commands to register for this bot,
    |                   Supported Values: "Command Group Name", "Shared Command Name", "Full Path to Class".
    |                   Default: Registers Global Commands.
    |                   Example: (array) [
    |                       'admin', // Command Group Name.
    |                       'status', // Shared Command Name.
    |                       Acme\Project\Commands\BotFather\HelloCommand::class,
    |                       Acme\Project\Commands\BotFather\ByeCommand::class,
    |             ]
    */
    'bots' => [
        'informantBot' => [
            'token' => env('INFORMANT_BOT_TOKEN', 'YOUR-BOT-TOKEN'),
            'certificate_path' => env('TELEGRAM_CERTIFICATE_PATH', 'YOUR-CERTIFICATE-PATH'),
            'webhook_url' => env('TELEGRAM_WEBHOOK_URL', 'YOUR-BOT-WEBHOOK-URL'),
            'commands' => [
                StartCommand::class
            ],
        ],
    ],
    'default' => 'informantBot',

    'async_requests' => env('TELEGRAM_ASYNC_REQUESTS', false),

    'http_client_handler' => null,

    'base_bot_url' => null,

    'resolve_command_dependencies' => true,

    'commands' => [
        HelpCommand::class,
    ],

    'command_groups' => [
        /* // Group Type: 1
           'commmon' => [
                Acme\Project\Commands\TodoCommand::class,
                Acme\Project\Commands\TaskCommand::class,
           ],
        */

        /* // Group Type: 2
           'subscription' => [
                'start', // Shared Command Name.
                'stop', // Shared Command Name.
           ],
        */

        /* // Group Type: 3
            'auth' => [
                Acme\Project\Commands\LoginCommand::class,
                Acme\Project\Commands\SomeCommand::class,
            ],

            'stats' => [
                Acme\Project\Commands\UserStatsCommand::class,
                Acme\Project\Commands\SubscriberStatsCommand::class,
                Acme\Project\Commands\ReportsCommand::class,
            ],

            'admin' => [
                'auth', // Command Group Name.
                'stats' // Command Group Name.
            ],
        */

        /* // Group Type: 4
           'myBot' => [
                'admin', // Command Group Name.
                'subscription', // Command Group Name.
                'status', // Shared Command Name.
                'Acme\Project\Commands\BotCommand' // Full Path to Command Class.
           ],
        */
    ],

    /*
    |--------------------------------------------------------------------------
    | Shared Commands [Optional]
    |--------------------------------------------------------------------------
    |
    | Shared commands let you register commands that can be shared between,
    | one or more bots across the project.
    |
    | This will help you prevent from having to register same set of commands,
    | for each bot over and over again and make it easier to maintain them.
    |
    | Shared commands are not active by default, You need to use the key name to register them,
    | individually in a group of commands or in bot commands.
    | Think of this as a central storage, to register, reuse and maintain them across all bots.
    |
    */
    'shared_commands' => [
        // 'start' => Acme\Project\Commands\StartCommand::class,
        // 'stop' => Acme\Project\Commands\StopCommand::class,
        // 'status' => Acme\Project\Commands\StatusCommand::class,
    ],
];
