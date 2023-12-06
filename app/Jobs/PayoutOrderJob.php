<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\User;
use App\Services\ApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class PayoutOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Order $order
    ) {
        $this->order = $order;
    }

    /**
     * Use the API service to send a payout of the correct amount.
     * Note: The order status must be paid if the payout is successful, or remain unpaid in the event of an exception.
     *
     * @return void
     */
    public function handle(ApiService $apiService)
    {
        // TODO: Complete this method
        DB::beginTransaction();
        try {
            $apiService->sendPayout($this->order->affiliate->user->email,$this->order->commission_owed);
            Order::where('id',$this->order->id)
            ->update([
                'payout_status' => Order::STATUS_PAID
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Error Processing Request", $e);
            
        }
    }
}
