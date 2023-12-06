<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use App\Models\CommissionLog;
use DB,Hash;

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
        // TODO: Complete this method
        $order = Order::where('external_order_id', $data['order_id'])
        ->first();

        if($order == null){
            $merchant = Merchant::where('domain', $data['merchant_domain'])
            ->first();
            $affiliate = Affiliate::where('discount_code', $data['discount_code'])
            ->first();
            $this->affiliateService->register($merchant,$data['customer_email'],$data['customer_name'], $merchant->default_commission_rate);

            // If no affiliate is found, create a new one
            if (!$affiliate) {
                $affiliate = $this->affiliateService->register($merchant,$data['customer_email'],$data['customer_name'], $merchant->default_commission_rate);
            }

            $order = Order::create([
                'external_order_id' => $data['order_id'],
                'subtotal' => $data['subtotal_price'],
                'merchant_id' => $merchant->id,
                'discount_code' => $data['discount_code'],
                'affiliate_id' => $affiliate->id,
                'commission_owed' => $data['subtotal_price']*$affiliate->commission_rate,
                'payout_status' => Order::STATUS_UNPAID
            ]);
        }
    }

    /**
     * Log the commission for the given affiliate and subtotal price.
     *
     * @param  int $affiliateId
     * @param  float $subtotalPrice
     * @return void
     */
    private function logCommission($order, $affiliate)
    {

        CommissionLog::create([
            'affiliate_id' => $affiliate->id,
            'subtotal_price' => $order->subtotal,
            'commission_amount' => $order->subtotal * $affiliate->commission_rate,
            'order_id' => $order->id            
        ]);
    }
}
