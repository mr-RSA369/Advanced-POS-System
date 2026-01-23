<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class PurchaseCategory extends Model
{
    protected $fillable = [
        'name'
    ];

    public $timestamps = false;

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
