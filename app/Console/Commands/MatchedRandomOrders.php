<?php

namespace App\Console\Commands;

use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class MatchedRandomOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:matched-random-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $matchedCount = 0;
        $start = microtime(true);

        $records = [
            'buy' => [],
            'sell' => []
        ];

        while (true) {

            $sellIds = Redis::zrange('orders:sell', 0, 0);
            $buyIds = Redis::zrange('orders:buy', 0, 0);

            if (empty($sellIds) || empty($buyIds)) break;

            $sell = Redis::hgetall("order:{$sellIds[0]}");
            $buy = Redis::hgetall("order:{$buyIds[0]}");

            if (!$sell || !$buy) break;

            $sellPrice = (float)$sell['price'];
            $buyPrice = (float)$buy['price'];

            $sell['amount'] = (float)$sell['amount'];
            $buy['amount'] = (float)$buy['amount'];

            if ($buyPrice >= $sellPrice) {

                $tradeAmount = min((float)$buy['amount'], (float)$sell['amount']);

                $buy['amount'] -= $tradeAmount;
                $sell['amount'] -= $tradeAmount;

                $matchedCount++;


                Redis::pipeline(function ($pipe) use ($buy, $sell) {
                    $buyStatus = $buy['amount'] <= 0 ? OrderStatusEnum::MATCHED->value : OrderStatusEnum::OPEN->value;
                    $pipe->hset("order:{$buy['id']}", 'amount', $buy['amount']);
                    if ($buyStatus === OrderStatusEnum::MATCHED->value) {
                        $pipe->zrem('orders:buy', $buy['id']);
                        $pipe->del("order:{$buy['id']}");
                    }

                    $sellStatus = $sell['amount'] <= 0 ? OrderStatusEnum::MATCHED->value : OrderStatusEnum::OPEN->value;
                    $pipe->hset("order:{$sell['id']}", 'amount', $sell['amount']);
                    if ($sellStatus === OrderStatusEnum::MATCHED->value) {
                        $pipe->zrem('orders:sell', $sell['id']);
                        $pipe->del("order:{$sell['id']}");
                    }
                });


                if ($buy['side'] === OrderTypeEnum::BUY->value) {
                    $records['buy'][$buy['id']] = [
                        'id' => $buy['id'],
                        'user_id' => $buy['user_id'],
                        'price' => $buy['price'],
                        'amount' => $buy['amount'],
                        'status' => $buy['amount'] <= 0 ? 'matched' : 'open',
                        'side' => OrderTypeEnum::BUY->value
                    ];
                }

                if ($sell['side'] === OrderTypeEnum::SELL->value) {
                    $records['sell'][$sell['id']] = [
                        'id' => $sell['id'],
                        'user_id' => $sell['user_id'],
                        'price' => $sell['price'],
                        'amount' => $sell['amount'],
                        'status' => $sell['amount'] <= 0 ? 'matched' : 'open',
                        'side' => OrderTypeEnum::SELL->value
                    ];
                }

            } else
                break;
        }

        dump($records);

        $data = [...$records['buy'], ...$records['sell']];
        DB::table('orders')->upsert($data,'id',['amount','status']);
        unset($records['buy'], $records['sell']);

        $duration = round((microtime(true) - $start) * 1000, 2);
        $this->info("Matched {$matchedCount} orders in {$duration} ms");

        return Command::SUCCESS;
    }
}
