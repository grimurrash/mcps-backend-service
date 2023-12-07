<?php

namespace App\Console;

use App\Console\Commands\OldDocumentForgetCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
         $schedule->command(OldDocumentForgetCommand::class)
             ->runInBackground()
             ->hourly();
    }
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
