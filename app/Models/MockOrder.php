<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MockOrder extends Model
{
    // Hi, i'am a model for data mockup for producer
    /** @use HasFactory<\Database\Factories\MockOrderFactory> */
    use HasFactory;

    protected $table = 'mock_orders';

    protected $fillable = [
        'customer_id',
        'customer_name',
        'item_id',
        'item_name',
        'price',
    ];
}
