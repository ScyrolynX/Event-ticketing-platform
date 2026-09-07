<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sends event reminder emails once a day to customers with paid tickets
// for events happening in the next 24 hours (spec 4.3: "Notifications").
Schedule::command('reminders:send')->daily();
