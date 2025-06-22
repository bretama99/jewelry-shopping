<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Update metal prices every 5 minutes
        $schedule->command('metal-prices:update')
                 ->everyFiveMinutes()
                 ->withoutOverlapping();
    }
}
