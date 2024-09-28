<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Models\Oauth;
use App\Models\State;
use App\Models\UserSetting;
use App\Models\User;
use App\Models\UserSetting;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use GuzzleHttp\Psr7\Request as GuzzleHttp;

class UninstallController extends Controller
{
    public function uninstall(Request $request)
    {
        $decoded = JWT::decode($request->session_token, env('APP_SECRET'), array('HS256'));
        $payload = json_decode(json_encode($decoded), true);
        $storeId = (int)$payload['store_id'];

        Oauth::where('user_store_id', '=', $storeId)->delete();
        State::where('user_store_id', '=', $storeId)->delete();
        Log::where('user_store_id', '=', $storeId)->delete();
        UserSetting::where('user_store_id', '=', $storeId)->delete();
        UserSetting::where('user_store_id', '=', $storeId)->delete();
        // User::where('store_id', '=', $storeId)->delete();

        $this->removeClientIdFromProxyRequest($storeId);
    }

    private function removeClientIdFromProxyRequest($storeId)
    {
        $client = new Client();

        $clientId = env("APP_CLIENT_ID");

        try {

            $curlGetAccessTokenRequest = new GuzzleHttp('GET', "http://localhost:3333/clear?client_id=$clientId&store_id=$storeId");

            $client->sendAsync($curlGetAccessTokenRequest)->wait();

            //

        } catch (\Throwable $th) {

            $this->log(['message' => $th->getMessage()], $storeId, ['info' => $th]);
        }
    }
}
