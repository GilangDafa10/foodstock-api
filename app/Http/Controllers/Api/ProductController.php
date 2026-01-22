<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return response()->json($products);
    }

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

    public function update(Request $request, Product $product)
    {
        // Validate the incoming request data
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category_id' => 'sometimes|required|exists:categories,id',
            'image_url' => 'sometimes|nullable|url', // Image URL is optional
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'unit' => 'sometimes|required|string|max:50',
        ]);

        if ($request->hasFile('image')) {
            $validated['image_url'] = $request->file('image')
                ->store('products', 'public');
        }

        // Update the product with the validated data
        $product->update($validated);

        // Return a JSON response with the updated product
        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product,
        ]);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }
}
