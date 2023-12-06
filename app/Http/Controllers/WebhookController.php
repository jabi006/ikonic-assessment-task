<?php

namespace App\Http\Controllers;

use App\Services\AffiliateService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DB,Validator;

class WebhookController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Pass the necessary data to the process order method
     * 
     * @param  Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        // TODO: Complete this method
        $data = $request->all();

        try {
            $this->orderService->processOrder($data);

            return response()->json(['message' => 'Order processed successfully'], 200);
        } catch (\Exception $e) {
            \Log::error('Error processing order: ' . $e->getMessage());
            return response()->json(['error' => 'Error processing order'], 500);
        }

    }
}
