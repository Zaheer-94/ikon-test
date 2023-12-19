<?php

namespace App\Http\Controllers;

use App\Services\AffiliateService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $requests=['order_id'=>$request->order_id,
            'subtotal_price'=>$request->subtotal_price,
            'merchant_domain'=>$request->merchant_domain,
            'discount_code'=>$request->discount_code];
        $this->orderService->processOrder($requests);
        return  response()->json(['Message'=>'succes']);
    }
}
