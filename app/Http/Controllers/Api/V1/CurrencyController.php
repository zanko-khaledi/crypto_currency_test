<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CurrencyCoinsEnum;
use App\Http\Controllers\Controller;
use App\Services\Currencies\CurrencyService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CurrencyController extends Controller
{

    /**
     * @param Request $request
     * @param CurrencyService $service
     * @return JsonResponse
     */
    public function index(Request $request, CurrencyService $service): JsonResponse
    {
        try {
            $response = $service->getService()->getCachedData();

            if (empty($response) || (is_array($response) && count($response) <= 0)) {
                $response = $service->getService()->store();

                if (isset($response['error']) && $response['status'] === 0) {
                    return new JsonResponse($response['error'], 500);
                }

                return new JsonResponse($response['data']);
            }

        } catch (\Throwable $e) {
            return new JsonResponse([
                'message' => $e->getMessage()
            ], 500);
        }

        return new JsonResponse($response);
    }
}
