<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Hash;

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
        // TODO: Complete this method
        $checkExistedMerchant = User::where('type', User::TYPE_MERCHANT)
        ->where('email', $email)
        ->first();

        $checkExistedAffiliate = User::where('type', User::TYPE_AFFILIATE)
        ->where('email', $email)
        ->first();

        if($checkExistedMerchant){
            throw new AffiliateCreateException;
        }

        if($checkExistedAffiliate){
            throw new AffiliateCreateException;
        }
        try {
            $user = User::create([
                'email' => $email,
                'name' => $name,
                'password' => Hash::make('password'),
                'type' => User::TYPE_AFFILIATE
            ]);

            $affiliate = Affiliate::create([
                'user_id' => $user->id,
                'merchant_id' => $merchant->id,
                'commission_rate' => $commissionRate,
                'discount_code' => $this->apiService->createDiscountCode($merchant)['code']
            ]);
            $affiliate = Affiliate::find($affiliate->id);

            Mail::to($email)
            ->send(new AffiliateCreated($affiliate));
            return $affiliate;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
