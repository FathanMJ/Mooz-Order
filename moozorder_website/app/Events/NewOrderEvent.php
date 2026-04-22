<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewOrderEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function broadcastOn()
    {
        return new Channel('orders');
    }

    public function broadcastAs()
    {
        return 'new-order';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->order->id,
            'message' => 'Pesanan baru telah diterima!',
            'time' => now()->toISOString(),
            'details' => [
                'items' => $this->order->details->map(function($detail) {
                    return [
                        'name' => $detail->produk->nama,
                        'quantity' => $detail->jumlah,
                        'price' => $detail->harga_satuan
                    ];
                }),
                'total' => $this->order->details->sum(function($detail) {
                    return $detail->harga_satuan * $detail->jumlah;
                })
            ]
        ];
    }
}
