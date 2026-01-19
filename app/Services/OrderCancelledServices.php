<?php

namespace App\Services;

use App\Models\Order;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class OrderCancelledServices
{
    public function cancel(Order $order, string $reason = null): void
    {
        DB::transaction(function () use ($order, $reason) {

            if ($order->status !== 'pending') {
                abort(400, 'Order tidak dapat dibatalkan');
            }

            // Update order status
            $order->update([
                'status' => 'cancelled'
            ]);

            // Kembalikan stok
            foreach ($order->orderDetails as $detail) {
                StockMovement::create([
                    'product_id' => $detail->product_id,
                    'type' => 'in',
                    'quantity' => $detail->quantity,
                    'note' => 'Cancel Order #' . $order->id
                        . ($reason ? " - {$reason}" : ''),
                ]);
            }
        });
    }
}
