<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Services\OrderCancelledServices;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExpirePendingPayments extends Command
{
    protected $signature = 'payments:expire';
    protected $description = 'Expire pending payments and cancel orders';

    public function handle(OrderCancelledServices $canceller)
    {
        $expiredAt = Carbon::now()->subHour();

        $payments = Payment::with('order.orderDetails')
            ->where('status', 'pending')
            ->where('created_at', '<=', $expiredAt)
            ->get();

        if ($payments->isEmpty()) {
            $this->info('No expired payments found.');
            return;
        }

        foreach ($payments as $payment) {
            DB::transaction(function () use ($payment, $canceller) {

                if ($payment->status !== 'pending') {
                    return;
                }

                $payment->update([
                    'status' => 'expired'
                ]);

                $canceller->cancel(
                    $payment->order,
                    'Payment expired (system)'
                );
            });

            $this->info("Payment {$payment->id} expired");
        }
    }
}
