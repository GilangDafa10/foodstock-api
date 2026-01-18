<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        "user_id",
        "order_date",
        "status",
        "total_price",
        "shipping_name",
        "shipping_phone",
        "shipping_address",
        "city",
        "postal_code",
    ];

    protected $casts = [
        "order_date" => "datetime",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }
}
