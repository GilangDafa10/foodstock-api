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

    public function getImageAttribute()
    {
        return $this->image_url
            ? asset('storage/' . $this->image_url)
            : null;
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
