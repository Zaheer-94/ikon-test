<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        $user=User::where('email',$merchant->email)->first();
        if($user->affiliate){
            return $user->affiliate;
        }
        return Affiliate::create([
            'user_id'=>$user->id,
            'merchant_id'=>$merchant->id,
            'discount_code'=>$this->apiService->createDiscountCode($merchant),
            'commission_rate'=>$commissionRate
        ]);
//        return  $affiliate;
    }
}
