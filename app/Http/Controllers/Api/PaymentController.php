<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use App\Models\Order;
use App\Models\Payment;
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

        DB::beginTransaction();

        try {

            // 🔹 Generate external_id unik untuk Midtrans
            $externalId = 'ORDER-' . $order->id . '-' . time();

            // 🔹 Simpan payment lokal dulu
            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_method' => $request->payment_method,
                'payment_provider' => 'midtrans',
                'external_id' => $externalId,
                'amount' => $order->total_price,
                'status' => 'pending'
            ]);

            // 🔹 Konfigurasi Midtrans Sandbox
            Config::$serverKey = config('midtrans.server_key');
            Config::$isProduction = false; // WAJIB false untuk sandbox
            Config::$isSanitized = true;
            Config::$is3ds = true;

            // 🔹 Parameter transaksi
            $params = [
                'transaction_details' => [
                    'order_id' => $externalId,
                    'gross_amount' => (int) $order->total_price,
                ],
                'customer_details' => [
                    'first_name' => $request->user()->name,
                    'email' => $request->user()->email,
                ],
            ];

            // 🔹 Generate Snap Token
            $snapToken = Snap::getSnapToken($params);

            DB::commit();

            return response()->json([
                'message' => 'Pembayaran berhasil dibuat',
                'snap_token' => $snapToken,
                'data' => $payment
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Gagal membuat pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Simulasi awal callback dari payment gateway
    public function callback(Request $request, OrderCancelledServices $canceller)
    {
        // Konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = false;

        $serverKey = config('midtrans.server_key');

        $orderId = $request->order_id;
        $statusCode = $request->status_code;
        $grossAmount = $request->gross_amount;
        $signatureKey = $request->signature_key;

        // 🔒 Validasi Signature Key
        $generatedSignature = hash(
            'sha512',
            $orderId . $statusCode . $grossAmount . $serverKey
        );

        if ($generatedSignature !== $signatureKey) {
            return response()->json([
                'message' => 'Invalid signature'
            ], 403);
        }

        return DB::transaction(function () use ($request, $canceller) {

            $payment = Payment::lockForUpdate()
                ->where('external_id', $request->order_id)
                ->firstOrFail();

            if ($payment->status !== 'pending') {
                return response()->json([
                    'message' => 'Pembayaran sudah diproses sebelumnya',
                    'data' => $payment
                ], 200);
            }

            $transactionStatus = $request->transaction_status;

            // 🎯 Mapping status Midtrans
            if (in_array($transactionStatus, ['settlement', 'capture'])) {
                $payment->status = 'paid';
                $payment->paid_at = now();
                $payment->order->update([
                    'status' => 'paid'
                ]);
            } elseif (in_array($transactionStatus, ['expire'])) {
                $payment->status = 'expired';
                $canceller->cancel(
                    $payment->order,
                    'Pembayaran expired'
                );
            } elseif (in_array($transactionStatus, ['cancel', 'deny'])) {
                $payment->status = 'failed';
                $canceller->cancel(
                    $payment->order,
                    'Pembayaran gagal'
                );
            }

            $payment->save();

            return response()->json([
                'message' => 'Callback berhasil diproses'
            ]);
        });
    }

    // Retry pembayaran yang expired atau failed
    public function retry(Request $request, $orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $payment = Payment::where('order_id', $order->id)->firstOrFail();

        // Validasi status pembayaran yang bisa di-retry
        if (!in_array($payment->status, ['expired', 'failed', 'cancelled', 'pending'])) {
            return response()->json([
                'message' => 'Pembayaran tidak dapat di-retry',
                'current_status' => $payment->status
            ], 400);
        }

        try {
            // 🔹 Konfigurasi Midtrans Sandbox
            Config::$serverKey = config('midtrans.server_key');
            Config::$isProduction = false;
            Config::$isSanitized = true;
            Config::$is3ds = true;

            // 🔹 Generate external_id baru untuk retry
            $externalId = 'ORDER-' . $order->id . '-' . time();

            // 🔹 Parameter transaksi
            $params = [
                'transaction_details' => [
                    'order_id' => $externalId,
                    'gross_amount' => (int) $order->total_price,
                ],
                'customer_details' => [
                    'first_name' => $request->user()->name,
                    'email' => $request->user()->email,
                ],
            ];

            // 🔹 Generate Snap Token
            $snapToken = Snap::getSnapToken($params);

            // 🔹 Update payment dengan external_id baru
            $payment->update([
                'external_id' => $externalId,
                'status' => 'pending',
                'paid_at' => null
            ]);

            return response()->json([
                'message' => 'Pembayaran berhasil di-retry',
                'snap_token' => $snapToken,
                'data' => $payment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal retry pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
