<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\CheckOutRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\UserAddress;
use App\Models\StockMovement;

class OrderController extends Controller
{
    public function index()
    {
        return Order::where('user_id', auth()->id())
            ->with('orderDetails.product')
            ->latest()
            ->get();
    }

    public function checkout(CheckOutRequest $request)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data, $request) {

            // ======================
            // Ambil alamat
            // ======================
            if (!empty($data['address_id'])) {
                $address = $request->user()
                    ->addresses()
                    ->findOrFail($data['address_id']);

                $shipping = [
                    'shipping_name' => $address->recipient_name,
                    'shipping_phone' => $address->phone,
                    'shipping_address' => $address->address,
                    'city' => $address->city,
                    'postal_code' => $address->postal_code,
                ];
            } else {
                $shipping = [
                    'shipping_name' => $data['shipping_name'],
                    'shipping_phone' => $data['shipping_phone'],
                    'shipping_address' => $data['shipping_address'],
                    'city' => $data['city'],
                    'postal_code' => $data['postal_code'],
                ];
            }

            // ======================
            // Hitung total
            // ======================
            $total = 0;

            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    abort(400, "Stok {$product->name} tidak mencukupi");
                }

                $total += $product->price * $item['quantity'];
            }

            // ======================
            // Simpan order
            // ======================
            $order = Order::create(array_merge([
                'user_id' => $request->user()->id,
                'order_date' => now(),
                'status' => 'pending',
                'total_price' => $total,
            ], $shipping));

            // ======================
            // Order details & stok
            // ======================
            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);

                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'out',
                    'quantity' => $item['quantity'],
                    'note' => 'Order #' . $order->id,
                ]);
            }

            return new OrderResource(
                $order->load('orderDetails.product')
            );
        });
    }

    public function show($id)
    {
        return Order::where('id', $id)
            ->where('user_id', auth()->id())
            ->with('orderDetails.product')
            ->firstOrFail();
    }
}
