<?php

namespace App\Jobs;

use App\Enums\CurrencyCoinsEnum;
use App\Models\Currency;
use App\Services\Currencies\CurrencyService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GetCurrenciesPriceJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected readonly CurrencyService $service)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->service->getService()->store();
    }
}
