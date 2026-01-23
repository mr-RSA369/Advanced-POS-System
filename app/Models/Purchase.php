<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'purchase_category_id',
        'items',
        'bill_image',
        'total_bill',
        'business_day_id',
    ];

    protected $casts = [
        'items' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(PurchaseCategory::class, 'purchase_category_id');
    }

    public function businessDay()
    {
        return $this->belongsTo(BusinessDay::class, 'business_day_id');
    }
}
