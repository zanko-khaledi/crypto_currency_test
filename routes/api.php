<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::apiResource('currencies',\App\Http\Controllers\Api\V1\CurrencyController::class)
    ->only([
        'index'
    ]);
