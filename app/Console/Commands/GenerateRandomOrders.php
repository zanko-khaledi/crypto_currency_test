<?php

namespace App\Console\Commands;

use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Redis;
use Illuminate\Support\Facades;

class GenerateRandomOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-random-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $batch_size = 1000;
        $total_random_orders = 1_000_000;
        $faker = \Faker\Factory::create();
        $increased = 0;

        if (User::all()->count() < 1000) {
            User::factory()->count(1000)->create();
        }

        $users_count = User::query()->get()->count();


        for ($i = 0; $i < $total_random_orders; $i += $batch_size) {

            $orders = [];

            for ($j = 0; $j < $batch_size; $j++) {

                $side = $faker->randomElement([OrderTypeEnum::BUY->value, OrderTypeEnum::SELL->value]);
                $price = $side === 'buy' ?
                    $faker->randomFloat(8, 10_000, 30_000)
                    :
                    $faker->randomFloat(8,15_000,25_000);

                $orders[] = [
                    'user_id' => $faker->numberBetween(1, $users_count),
                    'side' => $side,
                    'price' => $price,
                    'amount' => $faker->randomFloat(8, 0.0001, 10),
                    'status' => OrderStatusEnum::OPEN->value,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            DB::table('orders')->insert($orders);

            $lastIds = DB::table('orders')
                ->where('status', 'open')
                ->orderByDesc('id')
                ->limit(count($orders))
                ->pluck('id')
                ->toArray();
            $lastIds = array_reverse($lastIds);

            Facades\Redis::pipeline(function ($pipe) use ($lastIds, $orders) {

                foreach ($orders as $index => $order) {
                    $id = $lastIds[$index];
                    $key = "order:$id";

                    $pipe->hmset($key, [
                        'id' => $id,
                        'user_id' => $order['user_id'],
                        'side' => $order['side'],
                        'price' => $order['price'],
                        'amount' => $order['amount'],
                        'status' => $order['status'],
                        'created_at' => $order['created_at']->toDateTimeString()
                    ]);

                    $zset = $order['side'] === OrderTypeEnum::BUY->value ? 'orders:buy' : 'orders:sell';
                    $pipe->zadd($zset, [
                        $id => (float)$id // Because created_at(timestamp) for many of records was equal I had to use Id for FIFO stack instead of using created_at
                    ]);
                }
            });

            $increased += count($orders);
            $this->info("Increased $increased / $total_random_orders orders");
        }

        $this->info('Done.');
    }
}
