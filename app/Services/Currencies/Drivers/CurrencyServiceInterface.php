<?php

namespace App\Services\Currencies\Drivers;

interface CurrencyServiceInterface
{
    public function store();

    public function getCachedData();
}
