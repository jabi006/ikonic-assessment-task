<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Order;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Response as Resp;

class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {}

    /**
     * Useful order statistics for the merchant API.
     * 
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        // TODO: Complete this method
        $from = $request->from;
        $to = $request->to;

        $count = Order::where('created_at', '>=', $from)
        ->where('created_at', '<=', $to)
        ->count();

        $commission_owed = Order::where('created_at', '>=', $from)
        ->where('created_at', '<=', $to)
        ->where('payout_status', 'unpaid')
        ->whereHas('affiliate')
        ->sum('commission_owed');

        $revenue = Order::where('created_at', '>=', $from)
        ->where('created_at', '<=', $to)
        ->sum('subtotal');

        $data = [
            'count'   =>  $count, 
            'commissions_owed'      =>  $commission_owed,
            'revenue'       =>  $revenue
        ];

        $response = new JsonResponse($data, 200);

        return $response;
    }
}
