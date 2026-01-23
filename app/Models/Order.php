<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_id',
        'order_type',
        'items',
        'total_bill',
        'status',
        'customer_phone',
        'delivery_charges',
        'service_charges',
        'discount',
        'net_bill',
        'business_day_id',
        'table_no',
    ];

    protected $casts = [
        'items' => 'array',
    ];

    public function businessDay()
    {
        return $this->belongsTo(
            BusinessDay::class,
            'business_day_id', // foreign key in orders table
            'id'               // primary key in business_days table
        );
    }
}
