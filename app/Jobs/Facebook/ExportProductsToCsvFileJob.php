<?php

namespace App\Jobs\Facebook;

use App\Helpers\CsvLineBuilder;
use App\Helpers\ErrorLogger;
use App\Helpers\GetStoreWebsite;
use App\Application\Getter\Products\ProductsGetter;
use App\Models\CsvFile;
use App\Models\FaceBookExportStatus;
use App\Models\Products;
use App\Models\User;
use App\Models\UserSetting;
use App\Models\Variants;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use League\Csv\Writer;

class ExportProductsToCsvFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $websiteUrl;

    public $writer;

    public $products;

    public $fileName;

    public $userSettings;

    public $number_of_exported_products;

    public function __construct(public User $user)
    {
        $this->number_of_exported_products = 0;

        $this->userSettings = UserSetting::where('user_store_id', $user->store_id)->first();
    }

    public function handle()
    {
        $this->websiteUrl = $this->getStoreWebsiteName($this->user->store_id);

        $this->startCsvFileWriter();

        $products = $this->getApplicationProducts();

        foreach ($products as $product) {

            $this->number_of_exported_products++;

            foreach ($product->getProductVariants() as $key => $variant) {

                $this->makeLineInCsvFile($product, $variant);
            }
        }

        $this->moveTheNewCsvFileToStorage();
    }

    public function getApplicationProducts()
    {
        $productsGetter = new ProductsGetter($this->user);

        return $productsGetter
            ->setRuleQuery('product.last.update > 1')
            ->setDisplayResult(50)
            ->setRuleSortBy('product.last.update', 0)
            ->setRuleSortBy('product.name', 0)
            ->setRuleQuery('product.status=true')
            // ->setRuleQuery('product.channel="FACEBOOK"')
            ->getProductsIterableArrayable();
    }

    public function getStoreWebsiteName($storeId)
    {
        return GetStoreWebsite::getStoreWebsite($storeId);
    }

    public function startCsvFileWriter()
    {
        $filePath = $this->PrepareFilePath();

        $writer = Writer::createFromPath($filePath, 'w+');

        $writer->insertOne([
            'id',
            'title',
            'description',
            'availability',
            'condition',
            'price',
            'link',
            'image_link',
            'brand',
            'item_group_id',
            'google_product_category',
            'fb_product_category',
            'quantity_to_sell_on_facebook',
            'sale_price',
            'sale_price_effective_date',
            'gender',
            'color',
            'size',
            'age_group',
            'material',
            'pattern',
            'shipping',
            'shipping_weight'
        ]);

        $this->writer = $writer;
    }

    public function makeLineInCsvFile($product, $variant)
    {
        $line = $this->prepareLine($product, $variant);

        $this->writer->insertOne($line['product_data']);

        $this->assignProductToDatabase($product, $variant);

        $line = [];
    }

    public function moveTheNewCsvFileToStorage()
    {
        $directory = storage_path() . "/app/public/{$this->user->store_id}";

        if (!is_dir($directory)) {
            mkdir($directory);
        }

        rename(storage_path() . "/app/public/temp/{$this->fileName}.csv", storage_path() . "/app/public/{$this->user->store_id}/{$this->fileName}.csv");

        CsvFile::updateOrCreate([
            'user_store_id' => $this->user->store_id,
        ], [
            'file_name' => $this->fileName,
            'expiry_date' => time() + 86400
        ]);

        FaceBookExportStatus::create([
            'user_store_id' => $this->user->store_id,
            'log' => json_encode(["message" => "Exporting is successfully done."]),
            'exported_at' => date("Y/m/d H:i:s", time()),
            'number_of_exported_products' => $this->number_of_exported_products
        ]);
    }

    public function assignProductToDatabase($product, $variant)
    {
        $ProductTagsArray = $this->getProductTagsArray($product);

        $ProductImagesArray = $this->getProductImagesArray($product);

        try {

            Products::updateOrCreate(
                [
                    'user_store_id' => $this->user->store_id,
                    'product_id' => $product->getProductId()
                ],
                [
                    'product_description' => (strip_tags($product->getProductDescription())),
                    'product_seo_url' => $this->websiteUrl . '/product/' . $product->getProductSeoUrl(),
                    'product_image_url' => json_encode(['images' => $ProductImagesArray]),
                    'product_tags' => json_encode(['tags' => $ProductTagsArray]),
                    'sent_to_facebook_feed' => 1
                ]
            );

            Variants::updateOrCreate(
                [
                    'user_store_id' => $this->user->store_id,
                    'variant_id' => $variant->getVariantId(),
                    'product_variant_id' =>   $product->getProductId()
                ],
                [
                    'variant_image_url' => json_encode(['image' => $variant->getVariantImage() == null ? null : $variant->getVariantImage()]),
                    'sent_to_facebook_feed' => 1
                ]
            );
        } catch (\Throwable $th) {

            ErrorLogger::logError($th, $this->user->store_id);
        }
    }

    public function getProductImagesArray($product)
    {
        $ProductImages = ($product->getProductImages());

        $ProductImagesArray = [];

        foreach ($ProductImages as $ProductImage) {

            $ProductImagesArray[] = $ProductImage->getImageUrl();
        }

        return  $ProductImagesArray;
    }

    public function getProductTagsArray($product)
    {
        $ProductTags = $product->getProductTags();

        $ProductTagsArray = [];

        foreach ($ProductTags as $ProductTag) {

            $ProductTagsArray[] = $ProductTag;
        }

        return  $ProductTagsArray;
    }

    public function prepareLine($product, $variant)
    {
        $lineBuilder = new CsvLineBuilder($this->userSettings, $product, $variant);

        $line['product_data'][] = $lineBuilder->makeProductId();

        $line['product_data'][] = $lineBuilder->makeProductName();

        $line['product_data'][] = $lineBuilder->makeProductDescription();

        $line['product_data'][] = $lineBuilder->makeProductAvail();

        $line['product_data'][] = $lineBuilder->makeProductCondition();

        $line['product_data'][] = $lineBuilder->makeProductPrice();

        $line['product_data'][] = 'https://' . $this->websiteUrl . '/product/' . $product->getProductSeoUrl();

        $line['product_data'][] = $lineBuilder->makeProductImageLink();

        $line['product_data'][] = $lineBuilder->makeProductBrand();

        $line['product_data'][] = $lineBuilder->makeProductItemGroupId();

        $line['product_data'][] = $lineBuilder->makeProductGoogleProductCategory();

        $line['product_data'][] = $lineBuilder->makeProductFaceBookProductCategory();

        $line['product_data'][] = $lineBuilder->makeProductQuantityToSellOnFacebook();

        $line['product_data'][] = $lineBuilder->makeProductSalePrice();

        $line['product_data'][] = $lineBuilder->makeProductSalePriceEffectiveDate();

        $line['product_data'][] = $lineBuilder->makeProductGender();

        $line['product_data'][] = $lineBuilder->makeProductColor();

        $line['product_data'][] = $lineBuilder->makeProductSize();

        $line['product_data'][] = $lineBuilder->makeProductAgeGroup();

        $line['product_data'][] = $lineBuilder->makeProductMaterial();

        $line['product_data'][] =  $lineBuilder->makeProductPattern();

        $line['product_data'][] = $lineBuilder->makeProductShippingWeight();

        return $line;
    }

    public function PrepareFilePath()
    {
        $fileName = md5($this->user->store_id);

        $this->fileName = $fileName;

        if (file_exists(storage_path() . '/app/public/temp/' . $fileName . '.csv')) {
            unlink(storage_path() . '/app/public/temp/' . $fileName . '.csv');
        }

        $directory = storage_path() . "/app/public/temp/";

        if (!is_dir($directory)) {
            mkdir($directory);
        }

        $filePath = storage_path() . "/app/public/temp/{$fileName}.csv";

        touch($filePath);

        return $filePath;
    }
}
