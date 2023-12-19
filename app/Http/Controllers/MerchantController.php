<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Order;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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
//        {count: total number of orders in range,
//            commission_owed: amount of unpaid commissions for orders with an affiliate,
//            revenue: sum order subtotals}
        $orders = Order::where('created_at', '>=', $request->from)
            ->where('created_at', '<=', $request->to);
//            ->get();
        $ordersSummary=$orders->get();
        $count = $ordersSummary->count();

        $commissionOwed = $orders->whereHas('affiliate', function ($query) {
            $query->where('payout_status', Order::STATUS_UNPAID);
        })->sum('commission_owed');

        $revenue = $ordersSummary->sum('subtotal');
        $response = [
            'count' => $count,
            'commissions_owed' => $commissionOwed,
            'revenue' => $revenue,
        ];//dd($response);
        return response()->json($response);

    }
}
