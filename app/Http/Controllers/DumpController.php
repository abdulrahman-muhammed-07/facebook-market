<?php

namespace App\Http\Controllers;

use App\Jobs\Facebook\FetchErrorReportForProductsJob;
use App\Models\Products;
use App\Models\UserSetting;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use FacebookAds\Api;
use FacebookAds\Object\Fields\ProductCatalogFields;
use FacebookAds\Object\Fields\ProductFeedFields;
use FacebookAds\Object\ProductCatalog;
use FacebookAds\Object\ProductFeed;
use FacebookAds\Object\ProductFeedUpload;

class DumpController extends Controller
{
    public $user;

    public $storeId;

    public $uploadSessionId;

    public $facebookAccessToken;

    public function __construct()
    {
        $this->user =  User::where('store_id', 13)->first();

        $this->storeId = 13;
    }

    public function index()
    {
        $userSettings = UserSetting::where('user_store_id', $this->storeId)->first();

        $access_token = $userSettings->access_token;
        $app_secret = env('FACEBOOK_CLIENT_SECRET');
        $app_id = env('FACEBOOK_CLIENT_ID');

        Api::init($app_id, $app_secret, $access_token);

        $catalog = new ProductCatalog(null, '547425340510793');

        $catalog->setData(array(
            ProductCatalogFields::NAME => 'Your Catalog Name'
        ));

        $catalog =  $catalog->create();

        dd($catalog);

        $feed = new ProductFeed(null, 'your-feed-id');
        $feed->setData(array(
            ProductFeedFields::NAME => 'Your Product Feed Name',
            ProductFeedFields::SCHEDULE => array(
                'interval' => 'DAILY',
                'url' => 'https://example.com/feed.csv',
            ),
        ));

        $feed->create();

        $upload = new ProductFeedUpload(null, null);
        $upload->setProductId($feed->id);
        $upload->setUrl('https://example.com/feed.csv');
        $upload->create();

        $catalog->createProductSet(array(
            ProductCatalogProductSetFields::NAME => 'All Products',
        ));
    }
    public function old()
    {
        $data = Products::where('user_store_id', 13)->with('variants')->get()->toArray();

        $thirdPartyFacebookData = UserSetting::where('user_store_id', '=', $this->user->store_id)->first();

        dd($thirdPartyFacebookData);

        if (!$thirdPartyFacebookData) return;

        $thirdPartyFacebookData = $thirdPartyFacebookData->toArray();

        $feedId = $thirdPartyFacebookData['facebook_feed_id'];

        $this->facebookAccessToken = $thirdPartyFacebookData['access_token'];

        $uploadFetchSessionId = Http::get("https://graph.facebook.com/{$feedId}/uploads", [

            "access_token" => $this->facebookAccessToken,

        ]);

        if (isset($uploadFetchSessionId->json()['data'][0])) {

            $this->uploadSessionId = $uploadFetchSessionId->json()['data'][0]['id'];
        } else {

            $this->uploadSessionId = $uploadFetchSessionId->json()['data']['id'];
        }

        $uploadSessionId = $this->uploadSessionId;

        $fetchErrorReportWithLastSessionIdFromFacebook = Http::get(

            "https://graph.facebook.com/$uploadSessionId",

            [
                'access_token' => $this->facebookAccessToken,
                'fields' => "error_report"
            ]
        );

        if ($fetchErrorReportWithLastSessionIdFromFacebook->json()) {

            $responseAboutReportFromFacebook = $fetchErrorReportWithLastSessionIdFromFacebook->json();

            $fileHandle = $responseAboutReportFromFacebook['error_report']['report_status'];

            $reportStatus = $responseAboutReportFromFacebook['error_report']['report_status'];
        }

        if (!$reportStatus) return;

        $checkedReportStatus =  $this->checkReportStatus($reportStatus,  $fileHandle);
    }

    public function checkReportStatus($reportStatus, $fileHandle)
    {
        $uploadSessionId = $this->uploadSessionId;

        if ($reportStatus == 'NOT_REQUESTED') {

            $requestErrorReportToMAkeFromFacebookFirst = Http::post("https://graph.facebook.com/$uploadSessionId/error_report", ['access_token' => $this->facebookAccessToken]);

            dispatch(new FetchErrorReportForProductsJob($this->user, 'REQUESTED'));
        }

        if ($reportStatus == "WRITE_FINISHED") {

            $this->getErrorReportHandleFileFromFacebook($fileHandle);
        };


        dd($reportStatus);
    }

    public function getErrorReportHandleFileFromFacebook($fileHandle)
    {
        $directory = storage_path() . "/app/public/{$this->user->store_id}";

        if (!is_dir($directory)) {

            mkdir($directory);
        }

        $errorReportFile = md5($this->store_id);

        $file_path = $directory . "/{$errorReportFile}-report.csv";

        touch($file_path);

        $fileHandle = file_get_contents($fileHandle);

        file_put_contents($file_path, $fileHandle);

        return;
    }
}
