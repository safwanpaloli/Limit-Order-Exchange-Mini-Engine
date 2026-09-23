<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trade extends Model
{
    protected $fillable = ['maker_order_id', 'taker_order_id', 'symbol', 'price', 'amount'];

    public function makerOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'maker_order_id');
    }

    public function takerOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'taker_order_id');
    }
}
