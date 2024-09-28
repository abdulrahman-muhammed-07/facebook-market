<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variants extends Model
{
    use HasFactory;

    public $fillable = [
        'user_store_id',
        'product_variant_id',
        'variant_id',
        'variant_images',
        'sent_to_facebook_feed'
    ];

    public function product()
    {

        return $this->hasOne(Products::class, 'product_id', 'product_variant_id');
    }
}
