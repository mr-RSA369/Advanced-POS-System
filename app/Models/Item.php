<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'name',
         'category_id',
         'price',
         'status',
         'description',
         'image'
    ];

    public function category() {
        return $this->belongsTo(Category::class);
    }

    protected $casts = [
        'description' => 'array',
    ];
}
