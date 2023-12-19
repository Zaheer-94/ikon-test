<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
    {
        //                dd($data);
        $user=User::create([
            'email'=>$data['email'],
            'name'=>$data['name'],
            'password'=>$data['api_key'],
            'type' => User::TYPE_MERCHANT
        ]);
        $uid=$user->id;
        if(!empty($uid)) {
            $merchant = Merchant::create([
                'user_id' => $user->id,
                'domain' => $data['domain'],
                'display_name' => $data['name'],
            ]);
        }
        return $merchant;
    }

    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {
        $user->update([
            'name'=>$data['name'],
            'email'=>$data['email'],
            'password'=>Hash::make($data['api_key']),
        ]);
        Merchant::where('user_id',$user->id)->update([
            'domain' => $data['domain'],
            'display_name' => $data['name'],
        ]);
    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {
        $user=User::where('email',$email)->first();
        if(!empty($user)) {
            $merchant = Merchant::where('user_id', $user->id)->first();
            return $merchant;
        }else{
            return null;
        }
    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function payout(Affiliate $affiliate)
    {
        $affiliateOrders=$affiliate->orders()->where('payout_status',Order::STATUS_UNPAID)->get();
        foreach ($affiliateOrders as $order){
            PayoutOrderJob::dispatch($order);
        }
    }
}
