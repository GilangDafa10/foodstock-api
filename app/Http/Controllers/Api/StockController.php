<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockMovement;
use App\Models\Product;

class StockController extends Controller
{
    public function StockIn(Request $request)
    {
        // Validate the incoming request data
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:1',
        ]);

        $stockEntry = StockMovement::create([
            'product_id' => $validated['product_id'],
            'quantity' => $validated['quantity'],
            'movement_type' => 'in',
            'note' => 'Stok Masuk',
        ]);

        // Return a JSON response indicating success
        return response()->json([
            'message' => 'Stock entry created successfully',
            // 'stock_entry' => $stockEntry,
        ], 201);
    }

    public function stockOut(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($product->stock < $request->quantity) {
            return response()->json([
                'message' => 'Stok tidak mencukupi'
            ], 400);
        }

        return StockMovement::create([
            'product_id' => $request->product_id,
            'type'       => 'out',
            'quantity'   => $request->quantity,
            'note'       => 'Stock keluar',
        ]);
    }
}
