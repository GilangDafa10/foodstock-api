<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $fillable = [
        "user_id",
        "label",
        "recipient_name",
        "phone",
        "address",
        "city",
        "postal_code",
        "is_default",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
