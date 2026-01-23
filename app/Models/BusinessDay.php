<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessDay extends Model
{
    protected $fillable = [
        'business_date',
        'opened_at',
        'closed_at',
        'is_open',
    ];

    public function order()
    {
        return $this->hasMany(Order::class);
    }
}
