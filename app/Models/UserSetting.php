<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_store_id',
        'settings',
        'smtp',
        'client_id',
        'client_secret',
        'redirect_uri',
        'access_token',
        'refresh_token',
        'expiry_date',
        'facebook_catalog_id',
        'download_token',
        'facebook_feed_id',
        'facebook_feed_fetch_url',
        'mapping_settings'
    ];

    protected $primaryKey = 'user_store_id';

    protected static function boot()
    {
        parent::boot();

        UserSetting::creating(function ($model) {

            $model->download_token = date("Ymdhis") . md5(hash("sha256", rand()) . hash("sha256", rand()) . hash("sha256", rand()) . hash("sha256", rand()));

            $model->mapping_settings = json_encode([
                'id' => 'variant_id',
                'model' => 'product_model',
                'name' => 'product_name',
                'description' => 'product_description',
                'brand' => 'product_brand',
                'quantity' => 'product_quantity',
                'shipping_label' => 'carrier',
                'price' => 'variant_price',
                'sale_price' => 'variant_discount_price',
                'condition' => 'custom_field_variant_condition',
                'size' => 'custom_field_variant_size',
                'age_group' => 'custom_field_variant_age_roup',
                'material' => 'custom_field_variant_material',
                'pattern' => 'custom_field_variant_pattern',
                'gtin' => 'custom_field_variant_gtin',
                'google_product_category' => 'custom_field_product_google_product_category',
                'facebook_product_category' => 'custom_field_product_facebook_product_category',
                "gender" => "custom_field_variant_gender",
                'content_language' => 'English',
                'target_country' => "United States",
                "shipping_weight" => 'variant_weight',
                "color" => "custom_field_variant_color",
                'item_group_id' => "product_id",
                "barcode" => "variant_barcode",
                'dimension_unit' => 'in',
                'weight_unit' => 'lbs',
                "data" =>
                [
                    "product_description",
                    "product_name",
                    "variant_id",
                    "product_brand",
                    'product_model',
                    'product_brand',
                    'variant_price',
                    'product_type'
                ],
                'accepted_values' => [
                    'dimension_unit' => [
                        'in',
                        'cm'
                    ],
                    'weight_unit' => [
                        'kg',
                        'lbs'
                    ]

                ]
            ]);
        });
    }
}
