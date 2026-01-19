<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use App\Models\Order;
use App\Services\OrderCancelledServices;

class PaymentController extends Controller
{
    // Membuat invoice
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|string'
        ]);

        $order = Order::where('id', $request->order_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Order tidak dapat dibayar'
            ], 400);
        }
        if ($order->payment) {
            return response()->json([
                'message' => 'Pembayaran sudah dibuat',
            ], 400);
        }

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => $request->payment_method,
            'amount' => $order->total_price,
            'status' => 'pending'
        ]);
        return response()->json([
            'message' => 'Pembayaran berhasil dibuat',
            'data' => $payment
        ]);
    }

    // Simulasi awal callback dari payment gateway
    public function callback(Request $request, OrderCancelledServices $canceller)
    {
        $request->validate([
            'payment_id' => ['required', 'exists:payments,id'],
            'status' => ['required', 'in:paid,failed,expired']
        ]);

        return DB::transaction(function () use ($canceller, $request) {
            $payment = Payment::lockForUpdate()->findOrFail($request->payment_id);
            if ($payment->status !== 'pending') {
                return response()->json([
                    'message' => 'Pembayaran sudah diproses sebelumnya',
                    'data' => $payment
                ], 400);
            }

            $payment->status = $request->status;
            if ($request->status === 'paid') {
                $payment->paid_at = now();
                $payment->order->update([
                    'status' => 'paid'
                ]);
            }
            if (in_array($request->status, ['failed', 'expired'])) {
                $canceller->cancel(
                    $payment->order,
                    'Pembayaran ' . $request->status
                );
            }
            $payment->save();

            return response()->json([
                'message' => 'Callback pembayaran berhasil diproses',
                'data' => $payment
            ]);
        });
    }
}
