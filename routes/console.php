<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\MarkAbsentUsers;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Register custom commands
Artisan::command('absensi:mark-absent', function () {
    $this->call(MarkAbsentUsers::class);
})->purpose('Mark users as absent (alpha) if they miss both check-in and check-out');

// Schedule the absent marking command to run daily at 6 PM
if (app()->runningInConsole()) {
    app(Illuminate\Console\Scheduling\Schedule::class)
        ->command('absensi:mark-absent')
        ->dailyAt('18:00')
        ->withoutOverlapping();
}