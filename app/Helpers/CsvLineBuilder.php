<?php

namespace App\Helpers;

use DateTime;

class CsvLineBuilder
{
    public $mappingSettings;

    public $product;

    public $variant;

    public $productArrayCustomFields;

    public $variantArrayCustomFields;

    public function __construct($userSettings, $product, $variant)
    {
        $this->mappingSettings = json_decode($userSettings->mapping_settings, true);

        $this->product = $product;

        $this->variant = $variant;

        $productArrayCustomFields = [];

        $variantArrayCustomFields = [];

        $productCustomFields = ($this->product->getProductCustomFields());

        foreach ($productCustomFields as $productOneCustomFieldKey => $productOneCustomFieldValue) {

            $productArrayCustomFields[$productOneCustomFieldKey] =  $productOneCustomFieldValue;
        }

        $this->productArrayCustomFields = $productArrayCustomFields;

        $variantCustomFields = ($this->variant->getVariantCustomFields());

        foreach ($variantCustomFields as $variantOneCustomFieldKey => $variantOneCustomFieldValue) {

            $variantArrayCustomFields[$variantOneCustomFieldKey] =  $variantOneCustomFieldValue;
        }

        $this->variantArrayCustomFields = $variantArrayCustomFields;
    }

    public function makeProductMaterial()
    {
        return $this->getApplicationValue('material');
    }

    public function makeProductPattern()
    {
        return $this->getApplicationValue('pattern');
    }

    public function makeProductShipping()
    {
        return $this->getApplicationValue('shipping');
    }

    public function makeProductShippingWeight()
    {
        return $this->getApplicationValue('shipping_weight');
    }

    public function makeProductAgeGroup()
    {
        return $this->getApplicationValue('age_group');
    }

    public function makeProductSize()
    {
        return $this->getApplicationValue('size');
    }

    public function makeProductGender()
    {
        return $this->getApplicationValue('gender');
    }

    public function makeProductColor()
    {
        return $this->getApplicationValue('color');
    }

    public function makeProductSalePrice()
    {
        return $this->getApplicationValue('sale_price')->getDecimal()  . ' USD';
    }

    public function makeProductSalePriceEffectiveDate()
    {
        $start = $this->variant?->getVariantSaleStartUnwrapped();
        $end = $this->variant?->getVariantSaleEndUnwrapped();

        if ($start !== null || $end !== null) {

            $startDate = new DateTime('@' . $start);

            $endDate = $end > 0 ? new DateTime('@' . $end) : (new DateTime('@' . $start))->modify('+1 year');

            $startDateFormatted = $startDate->format('Ymd\THis');

            $endDateFormatted = $endDate->format('Ymd\THis');

            return $startDateFormatted . '/' . $endDateFormatted;
        }

        return '';
    }

    public function makeProductQuantityToSellOnFacebook()
    {
        return $this->getApplicationValue('quantity');
    }

    public function makeProductFaceBookProductCategory()
    {
        return $this->getApplicationValue('facebook_product_category');
    }

    public function makeProductGoogleProductCategory()
    {
        return $this->getApplicationValue('google_product_category');
    }

    public function makeProductItemGroupId()
    {
        return $this->getApplicationValue('item_group_id');
    }

    public function makeProductBrand()
    {
        return $this->getApplicationValue('brand');
    }

    public function makeProductImageLink()
    {
        return $this->variant->getVariantImage()?->getImageUrl() ?? $this->product->getProductImages()[0]?->getImageUrl() ?? '';
    }

    public function makeProductLink()
    {
        return $this->getApplicationValue('Link');
    }

    public function makeProductPrice()
    {
        return $this->getApplicationValue('price')->getDecimal() . " USD";
    }

    public function makeProductCondition()
    {
        return $this->getApplicationValue('condition') ?? 'new';
    }

    public function makeProductAvail()
    {
        return $this->product->getProductTotalQty() > 0 ? 'in stock' : 'out of stock';
    }

    public function makeProductId()
    {
        return strtolower($this->getApplicationValue('id'));
    }

    public function makeProductName()
    {
        return strtolower($this->getApplicationValue('name'));
    }

    public function makeProductDescription()
    {
        return strtolower(strip_tags($this->getApplicationValue('description')));
    }

    private function getApplicationValue($attribute)
    {
        $valueKey = $this->mappingSettings[$attribute];

        $mappings = [
            'custom_field_product_' => ['custom_product', $this->productArrayCustomFields],
            'custom_field_variant_' => ['custom_variant', $this->variantArrayCustomFields],
            'product_' => ['product', $this->product],
            'variant_' => ['variant', $this->variant],
        ];

        foreach ($mappings as $prefix => [$object, $data]) {

            if (str_starts_with($valueKey, $prefix)) {

                $valueKey = str_replace($prefix, '', $valueKey);

                if ($object === 'product' || $object === 'variant') {
                    $function = 'get' . ucfirst($object) . ucfirst($valueKey);
                    $function = str_replace('_', '', $function);
                    return call_user_func([$data, $function]);
                }

                return $data[$valueKey] ?? '';
            }
        }

        return $attribute;
    }
}
