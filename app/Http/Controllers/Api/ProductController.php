<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        // Validate the incoming request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'image_url' => 'nullable|url', // Image URL is optional
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
        ]);

        if ($request->hasFile('image')) {
            $validated['image_url'] = $request->file('image')
                ->store('products', 'public');
        }

        // Create a new product using the validated data
        $product = Product::create($validated);

        // Return a JSON response with the created product
        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product,
        ], 201);
    }
}
