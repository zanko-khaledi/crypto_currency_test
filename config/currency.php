<?php

return [

    'driver' => 'default',

    'drivers' => [
        'default' => \App\Services\Currencies\Drivers\CoingeckoDirver::class,

        'binance' => \App\Services\Currencies\Drivers\BinanceDriver::class,
        'coingecko' => \App\Services\Currencies\Drivers\CoingeckoDirver::class
    ]
];
