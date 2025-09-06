<?php

use App\Services\Currencies\CurrencyService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redis;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


\Illuminate\Support\Facades\Schedule::job(new \App\Jobs\GetCurrenciesPriceJob(new CurrencyService()))
    ->everyThirtySeconds();

//\Illuminate\Support\Facades\Schedule::command('app:matched-random-orders')
//    ->everyMinute();

