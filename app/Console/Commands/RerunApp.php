<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RerunApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rerun-app';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Refreshing database...');
        $this->call('migrate:fresh',[
            '--force' => true
        ]);


        $this->info('Generating random orders...');
        $this->call('app:generate-random-orders');

        $this->info('Running matching order engine...');
        $this->call('app:matched-random-orders');
    }
}
