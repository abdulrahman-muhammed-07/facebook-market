<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;

    public $fillable = [
        'user_store_id',
        'product_id',
        'product_images',
        'product_description',
        'product_seo_url',
        'product_tags',
        'sent_to_facebook_feed'
    ];

    public function variants()
    {

        return $this->hasMany(Variants::class, 'product_variant_id', 'product_id');
    }
}
