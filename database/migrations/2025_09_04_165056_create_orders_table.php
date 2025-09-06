<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (\Illuminate\Support\Facades\DB::table('users')->count() <= 0) {
            \Illuminate\Support\Facades\Artisan::call('db:seed', [
                '--class' => \Database\Seeders\UserSeeder::class,
                '--force' => true
            ]);
        }

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('side', [\App\Enums\OrderTypeEnum::values()]);
            $table->decimal('price', 18, 8);
            $table->decimal('amount', 18, 8);
            $table->enum('status', [\App\Enums\OrderStatusEnum::values()])->default(\App\Enums\OrderStatusEnum::OPEN->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
