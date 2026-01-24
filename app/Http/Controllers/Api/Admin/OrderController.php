<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Services\OrderCancelledServices;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Order::latest()->get();
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        return $order->load('orderDetails.product', 'user');
    }

    public function updateStatus(Order $order, UpdateOrderStatusRequest $request)
    {
        // Jika sudah completed / cancelled, tidak bisa diubah lagi
        if (in_array($order->status, ['completed', 'cancelled'])) {
            return response()->json([
                'message' => 'Order sudah selesai atau dibatalkan, status tidak bisa diubah lagi.'
            ], 422);
        }

        $order->update([
            'status' => $request->input('status')
        ]);

        return response()->json([
            'message' => 'Status order berhasil diupdate.',
            'order' => $order
        ]);
    }

    public function cancel(Order $order, OrderCancelledServices $canceller)
    {
        if ($order->status === 'cancelled') {
            return response()->json([
                'message' => 'Order sudah dibatalkan.'
            ], 422);
        }
        $canceller->cancel($order, 'Admin membatalkan pesanan.');
        return response()->json([
            'message' => 'Order berhasil dibatalkan oleh admin.',
            'order' => $order
        ]);
    }
}
