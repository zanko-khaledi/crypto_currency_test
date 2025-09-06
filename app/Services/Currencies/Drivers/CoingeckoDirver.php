<?php

namespace App\Services\Currencies\Drivers;

use App\Enums\CurrencyCoinsEnum;
use App\Models\Currency;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CoingeckoDirver implements CurrencyServiceInterface
{

    public const CACHE_KEY = "latest:price";
    private readonly ?string $url;

    public function __construct()
    {
        $this->url = "https://api.coingecko.com/api/v3";
    }

    /**
     * @param array $coins
     * @param string $vs
     * @return ConnectionException|PromiseInterface|\Exception|Response|GuzzleException
     * @throws RequestException
     * @throws ConnectionException
     */
    public function fetchCoinsPrices(array $coins = [], string $vs = "usd"): ConnectionException|PromiseInterface|\Exception|Response|GuzzleException
    {

        $url = $this->url . "/simple/price";

        return Http::timeout(5)->retry(3)->get($url, [
            'ids' => implode(',', $coins),
            'vs_currencies' => $vs
        ])->throw();
    }


    /**
     * @return array
     */
    public function store(): array
    {

        try {
            DB::beginTransaction();

            $response = $this->fetchCoinsPrices([
                CurrencyCoinsEnum::Bitcoin->value,
                CurrencyCoinsEnum::Ethereum->value
            ]);

            if ($response->ok()) {
                if ($response->collect()->count() > 50) {
                    $response->collect()->chunk(50)->each(function ($items) {
                        foreach ($items as $key => $item) {
                            Currency::query()->updateOrCreate([
                                'name' => $key
                            ], [
                                'name' => $key,
                                'vs' => array_keys($item)[0],
                                'price' => $item[array_keys($item)[0]],
                                'source' => $this->url
                            ]);
                        }
                    });
                } else {
                    $response->collect()->each(function ($item, $key) {
                        Currency::query()->updateOrCreate([
                            'name' => $key
                        ], [
                            'name' => $key,
                            'vs' => array_keys($item)[0],
                            'price' => $item[array_keys($item)[0]],
                            'source' => $this->url
                        ]);
                    });
                }
            }

            $latest = Currency::query()->get([
                'id','name','vs','price','created_at','updated_at'
            ])->toArray();

            Cache::put(static::CACHE_KEY,$latest, now()->addSeconds(30));

            DB::commit();
        } catch (\Throwable $throwable) {
            DB::rollBack();

            return [
                'status' => 0,
                'error' => $throwable->getMessage(),
                'data' => $this->getCachedData()
            ];
        }

        return [
            'status' => $response->status(),
            'data' => $latest
        ];
    }

    public function getCachedData()
    {
        return Cache::get(static::CACHE_KEY,[]);
    }

    public function removeCacheData()
    {
        return Cache::forget(static::CACHE_KEY);
    }
}
