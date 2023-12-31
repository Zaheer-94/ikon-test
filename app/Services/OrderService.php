<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {}

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        dd($data);
        $order=Order::where('external_order_id',$data['order_id'])->first();
        //        Stop  duplication
        if(!empty($order)) {
            return $order;
        }
        $user=User::where('email',$data['customer_email'])->first();
        if(empty($user)){
            $user=User::create([
                'name'=>$data['customer_name'],
                'email'=>$data['customer_email'],
                'passwrord'=>Hash::make('12345678'),
                'type'=>User::TYPE_MERCHANT
            ]);
        }$affiliate='';
        $merchant=Merchant::where('domain',$data['merchant_domain'])->first();
        $affiliate=Affiliate::create([
            'user_id'=>$user->id,
            'merchant_id'=>$merchant->id,
            'discount_code'=>$data['discount_code'],
            'commission_rate'=>0.1
        ]);

            $this->affiliateService->register($merchant,$data['customer_email'],$data['customer_name'],0.1);
        Order::create([
            'merchant_id'=>$merchant->id,
            'affiliate_id'=>$affiliate->id,
            'subtotal'=>$data['subtotal_price'],
            'commission_owed'=>$data['subtotal_price']*$affiliate->commission_rate,
            'payout_status'=>Order::STATUS_UNPAID,
            'external_order_id'=>$data['order_id']
        ]);

    }
}
