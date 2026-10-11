<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            $ttl = (int) config('auth_pending.ttl_minutes', 60);
            \App\Models\PendingRegistration::where('created_at', '<=', now()->subMinutes($ttl))->delete();
            \Log::info('[PENDING CLEANUP] removed rows older than '.$ttl.' minutes');
        })
        ->everyMinute(); // testing; produksi bisa ->hourly()
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
