<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'category_id',
        'image_url',
        'description',
        'price',
        'unit',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getImageUrlAttribute($value)
    {
        // Jika $value kosong, return null
        if (!$value) {
            return null;
        }

        // Cek apakah $value sudah mengandung 'http' (untuk menghindari double link)
        if (strpos($value, 'http') === 0) {
            return $value;
        }

        // Return URL lengkap
        // Hasilnya akan: http://localhost:8000/storage/products/namafile.jpg
        return asset('storage/' . $value);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getStockAttribute()
    {
        $in  = $this->stockMovements()->where('type', 'in')->sum('quantity');
        $out = $this->stockMovements()->where('type', 'out')->sum('quantity');
        $stockAvailable = $in - $out;
        return $stockAvailable;
    }
}
