<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserAddress;
use App\Models\User;
use Illuminate\Container\Attributes\Auth;

class UserAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $addresses = UserAddress::where('user_id', auth()->id())->get();
        return response()->json($addresses);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'label' => 'required|string|max:100',
            'recipient_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'is_default' => 'sometimes|boolean',
        ]);
        $validate['user_id'] = auth()->id();
        $address = UserAddress::create($validate);
        return response()->json([
            'message' => 'Address created successfully',
            'address' => $address
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $address = UserAddress::findOrFail($id);
        $validate = $request->validate([
            'label' => 'sometimes|required|string|max:100',
            'recipient_name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
            'address' => 'sometimes|required|string|max:500',
            'city' => 'sometimes|required|string|max:100',
            'postal_code' => 'sometimes|required|string|max:20',
            'is_default' => 'sometimes|boolean',
        ]);
        $address->update($validate);
        return response()->json([
            'message' => 'Address updated successfully',
            'address' => $address
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $address = UserAddress::findOrFail($id);
        $address->delete();
        return response()->json([
            'message' => 'Address deleted successfully'
        ], 200);
    }
}
