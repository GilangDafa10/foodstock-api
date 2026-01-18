<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        // Validate the incoming request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Create a new category using the validated data
        $category = Category::create($validated);

        // Return a JSON response with the created category
        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category,
        ], 201);
    }
}
