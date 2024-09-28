<?php

namespace App\Helpers;

use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use League\OAuth2\Client\Provider\GenericProvider;

class AccessToken
{
    static function getAccessToken(int $storeId)
    {
        $user = User::where('store_id', $storeId)->first();

        if (!$user->oauth || !$user->oauth->access_token) {

            ReportAccessTokenExpiry::report($user);

            return null;
        }

        if (isset($user->oauth) && time() > (int) $user->oauth->expiry_date - 50) {

            if (env("ACCESS_TOKEN_SOURCE") == 'Proxy') {

                $newAccessToken = self::newProxyAccessToken($storeId);
            } else {

                $newAccessToken = self::newProviderAccessToken($storeId);
            }

            if ($newAccessToken) {
                return  $newAccessToken;
            } else {
                return null;
            }
        }

        return $user->oauth->fresh()->access_token;
    }

    static function newProxyAccessToken(int $storeId)
    {
        $user = User::where('store_id', $storeId)->first();

        $client = new Client();

        $headers =    [
            'Content-Type: application/json'
        ];

        $storeIdRequest = (string) $storeId;

        $body = json_encode([
            'store_id' => $storeIdRequest,
            'client_id' => env("APP_CLIENT_ID"),
            'client_secret' => env("APP_SECRET"),
            'redirect_url' => env("REDIRECT_URL"),
            'scopes' => env("SCOPE"),
            'ACCESS_TYPE' => env("ACCESS_TYPE"),
            'refresh_token' => $user->oauth->refresh_token,
            'access_token' => $user->oauth->access_token,
            'auth_url' => env("URL_AUTHORIZE"),
            'token_url' => env("URL_ACCESS_TOKEN")
        ]);

        try {

            $curlGetAccessTokenRequest = new Request('POST', 'http://localhost:3333/exchnge', $headers, $body);

            $resultAccessTokenCurlFetch = $client->sendAsync($curlGetAccessTokenRequest)->wait();

            $resultBody =  ($resultAccessTokenCurlFetch->getBody()->getContents());

        } catch (\Throwable $th) {

            ErrorLogger::logError($th, $storeId);

            ReportAccessTokenExpiry::report($user);

            return null;
        }

        $arrayResultResponse = json_decode($resultBody, true);

        $newAccessToken = $arrayResultResponse['AccessToken'];

        if ($newAccessToken) {

            try {

                $user->oauth->updateOrCreate(
                    [
                        'user_store_id' =>  $user->store_id
                    ],
                    [
                        'access_token' => $newAccessToken,
                        'refresh_token' => $arrayResultResponse['RefreshToken'],
                        'expiry_date' => $arrayResultResponse['Expiry'],
                        'origin' => env("PLUGIN_ORIGIN")
                    ]
                );
                //

            } catch (\Throwable $th) {

                ErrorLogger::logError($th, $storeId);

                ReportAccessTokenExpiry::report($user);

                return null;
            }

            return $newAccessToken;
        }


        return null;
    }

    static function newProviderAccessToken(int $storeId): ?string
    {
        $user = User::where('store_id', $storeId)->first();

        if ($user->oauth->refresh_token) {

            $provider = app(GenericProvider::class);

            try {

                $accessToken = $provider->getAccessToken('refresh_token', [
                    'refresh_token' => $user->oauth->refresh_token
                ]);

            } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

                ErrorLogger::logError($e, $storeId);

                ReportAccessTokenExpiry::report($user);

                return null;
            }

            $user->oauth->update([
                'access_token' => $accessToken->getToken(),
                'refresh_token' => $accessToken->getRefreshToken(),
                'expiry_date' => $accessToken->getExpires(),
                'origin' => env("PLUGIN_ORIGIN")
            ]);

            return $accessToken->getToken();
        }

        return null;
    }
}
