<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'razorpay_payment_id',
        'amount',
        'credits_purchased',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
