<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockMovement;
use App\Models\Product;
use App\Http\Requests\Admin\StockAdjustmentRequest;

class StockController extends Controller
{
    public function adjust(StockAdjustmentRequest $request)
    {
        $product = Product::findOrFail($request->product_id);

        if ($request->type == 'out' && $product->stock < $request->quantity) {
            return response()->json([
                'message' => 'Stok tidak mencukupi'
            ], 400);
        }

        return StockMovement::create([
            'product_id' => $request->product_id,
            'type'       => $request->type,
            'quantity'   => $request->quantity,
            'note'       => $request->note,
        ]);

        return response()->json([
            'message' => 'Stock adjusted successfully',
            'current_stock' => $product->stock,
        ], 201);
    }

    public function history()
    {
        return StockMovement::with('product')
            ->latest()
            ->paginate(20);
    }
}
