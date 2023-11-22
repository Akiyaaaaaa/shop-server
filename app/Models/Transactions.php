<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    use HasFactory;
    protected $fillable = [
        'reference_no',
        'price',
        'quantity',
        'payment_amount',
        'product_id',
    ];

    public function product()
    {
        return $this->belongsTo(Products::class);
    }
}
