<?php

namespace App\Http\Controllers\Facebook;

use App\Http\Controllers\Controller;
use App\Models\UserSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use FacebookAds\Object\ProductCatalog;
use FacebookAds\Api;
use FacebookAds\Logger\CurlLogger;

class CreateFeedByCatalogIdInFacebookController extends Controller
{
    public function createFacebookFeed(Request $request)
    {
        $userSettings = UserSetting::where('user_store_id', '=', $request->store_id)->first();

        if ($userSettings->facebook_feed_id != null) {

            return response()->json([
                'status' => 'info',
                'message' => 'Feed has already been created to this user'
            ]);
        }

        if (!$userSettings->access_token || !$userSettings->facebook_catalog_id || !$userSettings->download_token) {

            $errorMessages = [];

            if (!$userSettings->access_token) {
                $errorMessages[] = 'Access Token missing ,redirect to make authentication with facebook';
            }

            if (!$userSettings->facebook_catalog_id) {
                $errorMessages[] = 'No facebook Catalog Id was Provided.';
            }

            if (!$userSettings->download_token) {
                $errorMessages[] = 'No facebook download token was Provided.';
            }

            return response()->json([
                'status' => 'error',
                'message' => implode(' ', $errorMessages)
            ]);
        }

        try {

            $facebookCatalogId = $userSettings->facebook_catalog_id;

            $response = Http::post("https://graph.facebook.com/v17.0/$facebookCatalogId/product_feeds", [
                'name' => 'Catalogue_new_Products',
                'schedule' => [
                    'interval' => 'DAILY',
                    'url' =>  env("APP_API") . '/csv/download?download_token=' . $userSettings->download_token,
                    'hour' => 20
                ],
                'access_token' => $userSettings->access_token
            ]);

            //

        } catch (\Throwable $th) {

            return response()->json([
                'status' => 'error',
                'message' => 'Feed error creation',
                'data' => $th->getMessage()
            ]);
        }

        if (array_key_exists('error', $response->json())) {

            return response()->json([
                'status' => 'error',
                'message' =>  $response->json()['error']['message'] ?? 'Bad Request sent to facebook'
            ]);
        }

        if (!isset($response['id'])) {

            return response()->json([
                'status' => 'error',
                'message' => 'Feed creation error , no Feed Id returned from Facebook response'
            ]);
        }

        $userSettings->facebook_feed_id = $response['id'];

        $userSettings->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Feed created successfully'
        ]);
    }

    public function createFacebookFeedOld(Request $request)
    {
        $userSettings = UserSetting::where('user_store_id', '=', $request->store_id)->first();

        $app_id = env('FACEBOOK_CLIENT_ID');

        $app_secret = env('FACEBOOK_CLIENT_SECRET');

        $facebookCatalogId = $userSettings->facebook_catalog_id;

        $response = Http::get("https://graph.facebook.com/v17.0/$facebookCatalogId/data_sources", [
            'access_token' => $userSettings->access_token
        ]);

        dd($response->json());

        // $api = Api::init($app_id, $app_secret, $access_token);

        // $api->setLogger(new CurlLogger());

        // $catalogId = '5151341414976889';

        // $fields = array();

        // $params = array(
        //     'name' => 'Test Feed',
        //     'schedule' => array('interval' => 'DAILY', 'url' => 'YOUR_FEED_URL', 'hour' => '22'),
        //     'access_token' => $access_token
        // );

        // $response = (new ProductCatalog($catalogId))->createProductFeed(
        //     $fields,
        //     $params
        // )->exportAllData();

        // dd($response);
        // if ($response->isSuccess()) {
        //     $feed = $response->getData();
        // } else {
        //     $error = $response->getError();
        // }
    }

}
