<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled tasks
|--------------------------------------------------------------------------
|
| These only run if the server has a cron entry calling Laravel's scheduler:
|
|   * * * * * cd /path/to/site && php artisan schedule:run >> /dev/null 2>&1
|
*/

// Nightly database backup, kept for two weeks. Orders are business records —
// losing them is not recoverable.
Schedule::command('backup:database')
    ->dailyAt('02:30')
    ->withoutOverlapping()
    ->onOneServer();
