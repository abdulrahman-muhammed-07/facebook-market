<?php

namespace App\Helpers;

use App\Models\User;
use Application\V1\Blog\RuleSortBy;
use Application\V1\Products\ProductInfoRequest;
use Application\V1\Products\ProductInfoUpdateInt;
use Application\V1\Products\ProductListingInfo;

class ApplicationProducts
{
    static function getApplicationProducts(User $user, int $last_updated = 1, int $display_result = 50)
    {
        $product_client = ClientsBuilder::getProductsClient();

        $newPit = '';
        $request = new ProductListingInfo();

        $request->setRuleQuery("product.last.update > $last_updated && product.id = \"DsruENsWQQKnZ6sJCUjdG4\"");

        $sortByLu = new RuleSortBy();

        $sortByLu->setColumnName('product.last.update');

        $sortByLu->setSortOrder(0);

        $sortByN = new RuleSortBy();

        $sortByN->setColumnName('product.name');

        $sortByN->setSortOrder(0);

        $request->setSortBy([$sortByLu, $sortByN]);

        $request->setDisplayResult($display_result);

        $products_array = [];

        $counter = 0;

        while (true) {
            $request->setPit($newPit);

            $product_arr = $product_client->GetProductListingByID($request, MetaData::get($user->store_id));

            $product_arr = $product_arr->wait();

            if (GrpcErrorHandle::checkGrpcErrors($product_arr, $user->store_id) == false) return GrpcErrorHandle::checkGrpcErrors($product_arr, $user->store_id);

            if (($counter >= 1) || ($product_arr[0]->getMessage() == 'no products available')) {

                yield $products_array;

                if ($counter == 0) {
                    break;
                } elseif ($product_arr[0]->getMessage() == 'no products available') {
                    break;
                }

                $products_array = [];
            }

            $counter++;

            $product_result_response = $product_arr[0];

            $newPit = $product_result_response->getProductsListing()->getPit();

            $products = $product_result_response->getProductsListing()->getProductsListing(); //ProductInfoRequest Object

            foreach ($products as $product) {

                $product_images = [];

                foreach ($product->getProductImages() as $image) {
                    $product_images[] = $image;
                }

                $product_options = [];
                foreach ($product->getProductOption() as $option) {
                    $product_options[] = $option;
                }

                $product_custom_fields = [];
                foreach ($product->getProductCustomFields() as $keyp => $custom_field) {
                    $product_custom_fields[$keyp] = $custom_field;
                }

                $product_variants = [];
                foreach ($product->getProductVariants() as $variant) {
                    $variant_custom_fields = [];

                    foreach ($variant->getVariantCustomFields() as $key => $value) {
                        $variant_custom_fields[$key] = $value;
                    }

                    $complete_variant = [
                        'variant_id' => $variant->getVariantId(),
                        'variant_price' => $variant->getVariantPrice()->getAmount(),
                        'variant_discount_price' => $variant->getVariantDiscountPrice()->getAmount(),
                        'variant_whole_sale_price' => $variant->getVariantWholesalePrice()->getAmount(),
                        'variant_whole_sale_price' => $variant->getVariantWholesaleDiscountPrice()->getAmount(),
                        'variant_sale_end' => $variant->getVariantSaleEndUnwrapped(),
                        'variant_limited_qty' => $variant->getVariantLimitedQty(),
                        'variant_qty' => $variant->getVariantQty(),
                        'variant_allow_backorder' => $variant->getVariantAllowBackorder(),
                        'variant_weight' => $variant->getVariantWeight(),
                        'variant_sold_qty' => $variant->getVariantSoldQty(),
                        'variant_barcode' => $variant->getVariantBarcode(),
                        'variant_sku' => $variant->getVariantSku(),
                        'variant_image' => $variant->getVariantImage(),
                        'variant_length' => $variant->getVariantLength(),
                        'variant_width' => $variant->getVariantWidth(),
                        'variant_height' => $variant->getVariantHeight(),
                        'variant_sale_start' => $variant->getVariantSaleStartUnwrapped(),
                        'variant_on_sale' => $variant->getVariantOnSale(),
                        'available_inventory' => $variant->getAvailableInventory(),
                        'variant_custom_fields' => $variant_custom_fields,
                        'variant_msrp' => $variant->getStockInternalInfo() != null && $variant->getStockInternalInfo()->getMsrp()  != null ? $variant->getStockInternalInfo()->getMsrp()->getAmount() : '0',
                        'variant_cost' => $variant->getStockInternalInfo() != null && $variant->getStockInternalInfo()->getCost()  != null ? $variant->getStockInternalInfo()->getCost()->getAmount() : '0',
                        'variant_reordering_point' => $variant->getStockInternalInfo() != null ? $variant->getStockInternalInfo()->getReorderingPoint() : 0,
                        'desired_inventory_level' => $variant->getStockInternalInfo() != null ? $variant->getStockInternalInfo()->getDesiredInventoryLevel() : 0

                    ];
                    foreach ($variant->getVariantOptionValue() as $value) {
                        //escape value
                        $value = addslashes($value);
                        $complete_variant['variants'][] = $value;
                    }
                    $product_variants[$variant->getVariantId()] = $complete_variant;
                    $complete_variant = [];
                }

                $product_tags = [];
                foreach ($product->getProductTags() as $product_tag) {
                    $product_tags[] = $product_tag;
                }

                $prod_array = [
                    'product_id' => $product->getProductId(),
                    'product_name' => $product->getProductName(),
                    'product_model' => $product->getProductModel(),
                    'product_description' => strip_tags($product->getProductDescription()),
                    'product_images' => $product_images,
                    'product_virtual' => $product->getProductVirtual(),
                    'product_allow_recurring' => $product->getProductAllowRecurring(),
                    'product_date_added' => $product->getProductDateAdded(),
                    'product_date_available' => $product->getProductDateAvailable(),
                    'product_weight' => $product->getProductWeight(),
                    'product_dim_type' => $product->getProductDimType(),
                    'product_sold_qty' => $product->getProductSoldQty(),
                    'product_number_of_reviews' => $product->getProductNumberOfReviews(),
                    'product_stars_average' => $product->getProductStarsAverage(),
                    'product_order_min' => $product->getProductOrderMin(),
                    'product_order_max' => $product->getProductOrderMax(),
                    'product_order_units' => $product->getProductOrderUnits(),
                    'product_seo_title' => $product->getProductSeoTitle(),
                    'product_seo_description' => $product->getProductSeoDescription(),
                    'product_seo_url' => $product->getProductSeoUrl(),
                    'product_brand' => $product->getProductBrand(),
                    'product_options' => $product_options,
                    'product_custom_fields' => $product_custom_fields,
                    'product_variants' => $product_variants,
                    'product_total_qty' => $product->getProductTotalQty(),
                    'product_on_sale' => $product->getProductOnSale(),
                    'ProductTags' => $product_tags,
                    'product_type' => $product->getProductType(),
                    'product_date_update' => $product->getProductDateUpdate(),
                    'product_vendor' => $product->getProductVendor(),
                    'product_tax' => $product->getProductTaxed() == true ? 'true' : 'false'
                ];

                $products_array[] = $prod_array;
            }
        }
    }

    static function get_all_Application_products(int $storeId, int $lastSynced = 1, int $display_result = 50, $rule_query = null)
    {
        $product_client = ClientsBuilder::getProductsClient();

        $newPit = '';

        $request = new ProductListingInfo();

        $request->setRuleQuery(isset($rule_query) ? $rule_query : "product.last.update > $lastSynced");

        $sortBy = new RuleSortBy();

        $sortBy->setColumnName('product.last.update');

        $sortBy->setSortOrder(1);

        $sortByName = new RuleSortBy();

        $sortByName->setColumnName('product.name');

        $sortByName->setSortOrder(1);

        $request->setSortBy([$sortBy, $sortByName]);

        $request->setDisplayResult($display_result);

        $products_array = [];

        $counter = 0;

        while (true) {
            $request->setPit($newPit);

            $product_arr = $product_client->GetProductListingByID($request, MetaData::get($storeId));

            $product_arr = $product_arr->wait();

            if (GrpcErrorHandle::checkGrpcErrors($product_arr, $storeId) == false) {

                dump('access token error');

                return false;
            }

            if (($counter >= 1) || ($product_arr[0]->getMessage() == 'no products available')) {

                yield   $products_array;

                if ($counter == 0) {

                    break;
                } elseif ($product_arr[0]->getMessage() == 'no products available') {

                    break;
                }

                $products_array = [];
            }

            $counter++;

            $product_result_response = $product_arr[0];

            $newPit = $product_result_response->getProductsListing()->getPit();

            $products = $product_result_response->getProductsListing()->getProductsListing();

            foreach ($products as $product) {
                $product_images = [];
                foreach ($product->getProductImages() as $image) {
                    $product_images[] = $image->getImageUrl();
                }
                $product_options = [];
                foreach ($product->getProductOption() as $option) {
                    $product_options[] = $option;
                }
                $product_custom_fields = [];
                foreach ($product->getProductCustomFields() as $key => $custom_field) {
                    $product_custom_fields[$key] = $custom_field;
                }
                $product_variants = [];
                foreach ($product->getProductVariants() as $variant) {
                    $variant_custom_fields = [];
                    foreach ($variant->getVariantCustomFields() as $key => $custom_field) {
                        $variant_custom_fields[$key] = $custom_field;
                    }
                    $complete_variant = [
                        'variant_id' => $variant->getVariantId(),
                        'variant_status' => $variant->getVariantStatus(),
                        'variant_price' => ($variant->getVariantPrice()) != null ? str_replace('$', '', $variant->getVariantPrice()->getDecimal()) : '',
                        'variant_currency_code' => ($variant->getVariantPrice()) != null ?  $variant->getVariantPrice()->getCurrencyCode() : '',
                        'variant_discount_price' => ($variant->getVariantDiscountPrice()) != null ? str_replace('$', '', $variant->getVariantDiscountPrice()->getDecimal()) : '',
                        'variant_whole_sale_price' => ($variant->getVariantWholesalePrice()) != null ? $variant->getVariantWholesalePrice()->getAmount() : '',
                        'variant_sale_end' => $variant->getVariantSaleEndUnwrapped(),
                        'variant_limited_qty' => $variant->getVariantLimitedQty(),
                        'variant_qty' => $variant->getVariantQty(),
                        'variant_allow_backorder' => $variant->getVariantAllowBackorder(),
                        'variant_weight' => $variant->getVariantWeight(),
                        'variant_sold_qty' => $variant->getVariantSoldQty(),
                        'variant_barcode' => $variant->getVariantBarcode(),
                        'variant_sku' => $variant->getVariantSku(),
                        'variant_image' => $variant->getVariantImage() != null ? $variant->getVariantImage()->getImageUrl() : ($product_images[0] ?? ''),
                        'variant_length' => $variant->getVariantLength(),
                        'variant_width' => $variant->getVariantWidth(),
                        'variant_height' => $variant->getVariantHeight(),
                        'variant_sale_start' => $variant->getVariantSaleStartUnwrapped(),
                        'variant_on_sale' => $variant->getVariantOnSale(),
                        'available_inventory' => $variant->getAvailableInventory(),
                        'variant_custom_fields' => $variant_custom_fields,
                    ];
                    foreach ($variant->getVariantOptionValue() as $value) {
                        $value = addslashes($value);
                        $complete_variant['variants'][] = $value;
                    }
                    $product_variants[] = $complete_variant;
                    $complete_variant = [];
                }
                $product_tags = [];
                foreach ($product->getProductTags() as $product_tag) {
                    $product_tags[] = $product_tag;
                }
                $prod_array = [
                    'product_id' => $product->getProductId(),
                    'product_name' => $product->getProductName(),
                    'product_model' => $product->getProductModel(),
                    'product_status' => $product->getProductStatus(),
                    'product_description' => strip_tags($product->getProductDescription()),
                    'product_images' => $product_images,
                    'product_virtual' => $product->getProductVirtual(),
                    'product_allow_recurring' => $product->getProductAllowRecurring(),
                    'product_date_added' => $product->getProductDateAdded(),
                    'product_date_available' => $product->getProductDateAvailable(),
                    'product_weight' => $product->getProductWeight(),
                    'product_dim_type' => $product->getProductDimType(),
                    'product_sold_qty' => $product->getProductSoldQty(),
                    'product_number_of_reviews' => $product->getProductNumberOfReviews(),
                    'product_stars_average' => $product->getProductStarsAverage(),
                    'product_order_min' => $product->getProductOrderMin(),
                    'product_order_max' => $product->getProductOrderMax(),
                    'product_order_units' => $product->getProductOrderUnits(),
                    'product_seo_title' => $product->getProductSeoTitle(),
                    'product_seo_description' => $product->getProductSeoDescription(),
                    'product_seo_url' => $product->getProductSeoUrl(),
                    'product_options' => $product_options,
                    'product_custom_fields' => $product_custom_fields,
                    'product_variants' => $product_variants,
                    'product_total_qty' => $product->getProductTotalQty(),
                    'product_on_sale' => $product->getProductOnSale(),
                    'product_tags' => $product_tags,
                    'product_type' => $product->getProductType(),
                    'product_date_update' => $product->getProductDateUpdate(),
                    'product_vendor' => $product->getProductVendor(),

                ];
                $products_array[] = $prod_array;
            }
        }
    }

    static function get_all_Application_variants(int $storeId, int $last_updated = 1, int $display_result = 50)
    {
        $product_client = ClientsBuilder::getProductsClient();

        $newPit = '';
        $request = new ProductListingInfo();
        $request->setRuleQuery("product.last.update > $last_updated");
        $sortBy = new RuleSortBy();
        $sortBy->setColumnName('product.last.update');
        $sortBy->setSortOrder(0);
        $sortByName = new RuleSortBy();
        $sortByName->setColumnName('product.name');
        $sortByName->setSortOrder(0);
        $request->setSortBy([$sortBy, $sortByName]);
        $request->setDisplayResult($display_result);
        $product_variants = [];
        $counter = 0;

        while (true) {

            $request->setPit($newPit);

            $product_arr = $product_client->GetProductListingByID($request,  MetaData::get($storeId));

            $product_variants = $product_arr->wait();

            if (GrpcErrorHandle::checkGrpcErrors($product_variants, $storeId) == false) {
                break;
            }

            if (($counter >= 1) || ($product_arr[0]->getMessage() == 'no products available')) {
                yield  $product_variants;
                if ($counter == 0) {
                    // $this->log(['message' => 'There\'s no products available'], 0);
                    dd($product_variants[0]->serializeToJsonString());
                    break;
                } elseif ($product_variants[0]->getMessage() == 'no products available') {
                    dump($product_variants[0]->serializeToJsonString());
                    break;
                }
                $product_variants = [];
            }

            $counter++;
            $product_result_response = $product_arr[0];

            $newPit = $product_result_response->getProductsListing()->getPit();
            $products = $product_result_response->getProductsListing()->getProductsListing(); //ProductInfoRequest Object
            foreach ($products as $product) {
                $product_images = [];
                foreach ($product->getProductImages() as $image) {
                    $product_images[] = $image->getImageUrl();
                }
                $product_options = [];
                foreach ($product->getProductOption() as $option) {
                    $product_options[] = $option;
                }
                $product_variants = [];
                foreach ($product->getProductVariants() as $variant) {
                    $variant_custom_fields = [];
                    foreach ($variant->getVariantCustomFields() as $key => $custom_field) {
                        $variant_custom_fields[$key] = $custom_field;
                    }
                    $complete_variant = [
                        'product_id' => $product->getProductId(),
                        'product_name' => $product->getProductName(),
                        'product_vendor' => $product->getProductVendor(),
                        'product_images' => $product_images,
                        'product_type' => $product->getProductType(),
                        'product_status' => $product->getProductStatus(),
                        'product_dim_type' => $product->getProductDimType(),
                        'product_date_update' => $product->getProductDateUpdate(),
                        'product_options' => $product_options,
                        'variant_id' => $variant->getVariantId(),
                        'variant_status' => $variant->getVariantStatus(),
                        'variant_price' => ($variant->getVariantPrice()) != null ? str_replace('$', '', $variant->getVariantPrice()->getDecimal()) : '',
                        'variant_currency_code' => ($variant->getVariantPrice()) != null ?  $variant->getVariantPrice()->getCurrencyCode() : '',
                        'variant_discount_price' => ($variant->getVariantDiscountPrice()) != null ? str_replace('$', '', $variant->getVariantDiscountPrice()->getDecimal()) : '',
                        'variant_whole_sale_price' => ($variant->getVariantWholesalePrice()) != null ? $variant->getVariantWholesalePrice()->getAmount() : '',
                        'variant_sale_end' => $variant->getVariantSaleEndUnwrapped(),
                        'variant_limited_qty' => $variant->getVariantLimitedQty(),
                        'variant_qty' => $variant->getVariantQty(),
                        'variant_allow_backorder' => $variant->getVariantAllowBackorder(),
                        'variant_weight' => $variant->getVariantWeight(),
                        'variant_sold_qty' => $variant->getVariantSoldQty(),
                        'variant_barcode' => $variant->getVariantBarcode(),
                        'variant_sku' => $variant->getVariantSku(),
                        'variant_image' => $variant->getVariantImage() != null ? $variant->getVariantImage()->getImageUrl() : ($product_images[0] ?? ''),
                        'variant_length' => $variant->getVariantLength(),
                        'variant_width' => $variant->getVariantWidth(),
                        'variant_height' => $variant->getVariantHeight(),
                        'variant_sale_start' => $variant->getVariantSaleStartUnwrapped(),
                        'variant_on_sale' => $variant->getVariantOnSale(),
                        'available_inventory' => $variant->getAvailableInventory(),
                        'variant_custom_fields' => $variant_custom_fields,
                    ];
                    foreach ($variant->getVariantOptionValue() as $value) {
                        //escape value
                        $value = addslashes($value);
                        $complete_variant['variants'][] = $value;
                    }
                    $product_variants[] = $complete_variant;
                    $complete_variant = [];
                }

                $product_variants[] = $product_variants;
            }
        }
    }

    static function deleted_products(int $storeId, int $product_last_update = 1)
    {
        $product_client = ClientsBuilder::getProductsClient();

        $request = new ProductInfoUpdateInt();

        $request->setValue($product_last_update);

        $prod_arr_api = $product_client->GetDeletedProductInfo($request, MetaData::get($storeId));

        $prod_arr_api = $prod_arr_api->wait();

        if (GrpcErrorHandle::checkGrpcErrors($prod_arr_api, $storeId) == false) {
            return;
        }

        $products = $prod_arr_api[0]->getProductsListing()->getProductsListing();

        $deleted_arr = [];

        foreach ($products as $product) {

            $deleted_product_id = $product->getProductId();

            if (count($product->getProductVariants()) == 0) {

                $deleted_arr[] = (object)['product_id' => $deleted_product_id];
            } else {

                $varaiant_ids = [];

                foreach ($product->getProductVariants() as $variant) {
                    $varaiant_ids[] = $variant->getVariantId();
                }

                $deleted_arr[] = (object)['product_id' => $deleted_product_id, 'variants_ids' => $varaiant_ids];
            }
        }

        if (isset($product)) {
            $deleted_arr[] = (object)['last_updated' => $product->getProductDateUpdate()];
        }

        return $deleted_arr;
    }

    static function getProduct(string $product_uuid, int $storeId)
    {
        $product_client = ClientsBuilder::getProductsClient();

        $request = new ProductInfoRequest();

        $request->setProductId($product_uuid);

        $res = $product_client->GetProductByID($request, MetaData::get($storeId));

        $res_arr = $res->wait();

        if (GrpcErrorHandle::checkGrpcErrors($res_arr, $storeId) == false) {
            return false;
        }

        if ($res_arr[0]->getProduct() == null) {

            return false;
        }

        $product = $res_arr[0]->getProduct();

        $product_images = [];

        foreach ($product->getProductImages() as $image) {

            $product_images[] = $image->getImageUrl();
        }

        $product_options = [];

        foreach ($product->getProductOption() as $option) {

            $product_options[] = $option;
        }

        $product_custom_fields = [];

        foreach ($product->getProductCustomFields() as $key =>  $custom_field) {

            $product_custom_fields[$key] = $custom_field;
        }

        $product_variants = [];

        foreach ($product->getProductVariants() as $variant) {

            $variant_custom_fields = [];

            foreach ($variant->getVariantCustomFields() as $key => $custom_field) {

                $variant_custom_fields[$key] = $custom_field;
            }

            $complete_variant = [
                'variant_id' => $variant->getVariantId(),
                'variant_price' => $variant->getVariantPrice()->getAmount(),
                'variant_discount_price' => $variant->getVariantDiscountPrice()->getAmount(),
                'variant_whole_sale_price' => $variant->getVariantWholesalePrice()->getAmount(),
                'variant_whole_sale_price' => $variant->getVariantWholesaleDiscountPrice()->getAmount(),
                'variant_sale_end' => $variant->getVariantSaleEndUnwrapped(),
                'variant_limited_qty' => $variant->getVariantLimitedQty(),
                'variant_qty' => $variant->getVariantQty(),
                'variant_allow_backorder' => $variant->getVariantAllowBackorder(),
                'variant_weight' => $variant->getVariantWeight(),
                'variant_sold_qty' => $variant->getVariantSoldQty(),
                'variant_barcode' => $variant->getVariantBarcode(),
                'variant_sku' => $variant->getVariantSku(),
                'variant_image' => $variant->getVariantImage() ? $variant->getVariantImage()->getImageUrl() : null,
                'variant_length' => $variant->getVariantLength(),
                'variant_width' => $variant->getVariantWidth(),
                'variant_height' => $variant->getVariantHeight(),
                'variant_sale_start' => $variant->getVariantSaleStartUnwrapped(),
                'variant_on_sale' => $variant->getVariantOnSale(),
                'available_inventory' => $variant->getAvailableInventory(),
                'variant_custom_fields' => $variant_custom_fields,
            ];

            foreach ($variant->getVariantOptionValue() as $value) {

                $value = addslashes($value);

                $complete_variant['variants'][] = $value;
            }

            $product_variants[] = $complete_variant;

            $complete_variant = [];
        }

        $product_tags = [];

        foreach ($product->getProductTags() as $product_tag) {

            $product_tags[] = $product_tag;
        }

        $prod_array = [
            'product_id' => $product->getProductId(),
            'product_name' => $product->getProductName(),
            'product_model' => $product->getProductModel(),
            'product_description' => strip_tags($product->getProductDescription()),
            'product_images' => $product_images,
            'product_virtual' => $product->getProductVirtual(),
            'product_allow_recurring' => $product->getProductAllowRecurring(),
            'product_date_added' => $product->getProductDateAdded(),
            'product_date_available' => $product->getProductDateAvailable(),
            'product_weight' => $product->getProductWeight(),
            'product_dim_type' => $product->getProductDimType(),
            'product_sold_qty' => $product->getProductSoldQty(),
            'product_number_of_reviews' => $product->getProductNumberOfReviews(),
            'product_stars_average' => $product->getProductStarsAverage(),
            'product_order_min' => $product->getProductOrderMin(),
            'product_order_max' => $product->getProductOrderMax(),
            'product_order_units' => $product->getProductOrderUnits(),
            'product_seo_title' => $product->getProductSeoTitle(),
            'product_seo_description' => $product->getProductSeoDescription(),
            'product_seo_url' => $product->getProductSeoUrl(),
            'product_options' => $product_options,
            'product_custom_fields' => $product_custom_fields,
            'product_variants' => $product_variants,
            'product_total_qty' => $product->getProductTotalQty(),
            'product_on_sale' => $product->getProductOnSale(),
            'ProductTags' => $product_tags,
            'product_type' => $product->getProductType(),
            'product_date_update' => $product->getProductDateUpdate()
        ];

        return $prod_array;
    }
}
