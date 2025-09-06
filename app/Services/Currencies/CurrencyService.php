<?php

namespace App\Services\Currencies;

use App\Enums\CurrencyApiDriversEnum;
use App\Enums\CurrencyCoinsEnum;
use App\Models\Currency;
use App\Services\Currencies\Drivers\BinanceDriver;
use App\Services\Currencies\Drivers\CoingeckoDirver;
use App\Services\Currencies\Drivers\CurrencyServiceInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\JsonResponse;

class CurrencyService
{

    /**
     * @param CurrencyApiDriversEnum $apiDriversEnum
     * @return CurrencyServiceInterface
     */
    public static function driver(CurrencyApiDriversEnum $apiDriversEnum = CurrencyApiDriversEnum::Coingecko):CurrencyServiceInterface
    {
         return match ($apiDriversEnum){
             CurrencyApiDriversEnum::Binance => new BinanceDriver(),
             default => new CoingeckoDirver()
         };
    }

    public function getService(CurrencyApiDriversEnum $apiDriversEnum = CurrencyApiDriversEnum::Coingecko): CurrencyServiceInterface
    {
        return static::driver($apiDriversEnum);
    }
}
