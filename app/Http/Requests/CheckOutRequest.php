<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckOutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // karena sudah menggunakan sanctum
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',

            // alamat default jika tidak mengirimkan alamat pengiriman
            'address_id' => ['nullable|exists:user_addresses,id'],

            // alamat manual
            'shipping_name' => 'required_without:address_id|string|max:255',
            'shipping_phone' => 'required_without:address_id|string|max:20',
            'shipping_address' => 'required_without:address_id|string|max:500',
            'city' => 'required_without:address_id|string|max:100',
            'postal_code' => 'required_without:address_id|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Daftar item tidak boleh kosong.',
            'items.array' => 'Format item tidak valid.',
            'items.min' => 'Minimal harus ada satu item dalam pesanan.',
            'items.*.product_id.required' => 'ID produk wajib diisi untuk setiap item.',
            'items.*.product_id.exists' => 'Produk dengan ID tersebut tidak ditemukan.',
            'items.*.quantity.required' => 'Kuantitas wajib diisi untuk setiap item.',
            'items.*.quantity.integer' => 'Kuantitas harus berupa angka bulat.',
            'items.*.quantity.min' => 'Kuantitas minimal adalah 1.',
            'shipping_name.required_without' => 'Nama penerima wajib diisi jika alamat default tidak digunakan.',
            'shipping_phone.required_without' => 'Nomor telepon wajib diisi jika alamat default tidak digunakan.',
            'shipping_address.required_without' => 'Alamat pengiriman wajib diisi jika alamat default tidak digunakan.',
            'city.required_without' => 'Kota wajib diisi jika alamat default tidak digunakan.',
            'postal_code.required_without' => 'Kode pos wajib diisi jika alamat default tidak digunakan.',
        ];
    }
}
