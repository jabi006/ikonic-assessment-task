<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property User $user
 * @property Merchant $merchant
 * @property float $commission_rate
 */
class CommissionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'affiliate_id',
        'order_id',
        'subtotal_price',
        'commission_amount'
    ];
}
