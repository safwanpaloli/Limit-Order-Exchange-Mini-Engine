<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderbookUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $symbol;

    public function __construct(string $symbol)
    {
        $this->symbol = $symbol;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('orderbook.' . $this->symbol),
        ];
    }
}
