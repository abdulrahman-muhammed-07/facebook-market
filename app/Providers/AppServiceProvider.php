<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\OptionProvider\HttpBasicAuthOptionProvider;
use GuzzleHttp\Client;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(GenericProvider::class, function () {

            if (env("PLUGIN_ORIGIN") == 'Dev') {

                $provider = new GenericProvider([
                    'clientId'                => env('APP_CLIENT_ID'),
                    'clientSecret'            => env('APP_SECRET'),
                    'redirectUri'             => env('DEV_REDIRECT_URL'),
                    'urlAuthorize'            => env('DEV_URL_AUTHORIZE'),
                    'urlAccessToken'          => env('DEV_URL_ACCESS_TOKEN'),
                    'urlResourceOwnerDetails' => env('DEV_URL_RESOURCE_OWNER_DETAILS'),

                ], ['optionProvider' => new HttpBasicAuthOptionProvider()]);

                $guzzyClient = new Client([
                    'defaults' => [
                        \GuzzleHttp\RequestOptions::CONNECT_TIMEOUT => 5,
                        \GuzzleHttp\RequestOptions::ALLOW_REDIRECTS => true
                    ],
                    \GuzzleHttp\RequestOptions::VERIFY => false,
                ]);

                $provider = $provider->setHttpClient($guzzyClient);

                return $provider;
            }

            return new GenericProvider([
                'clientId'                => env('APP_CLIENT_ID'),
                'clientSecret'            => env('APP_SECRET'),
                'redirectUri'             => env('REDIRECT_URL'),
                'urlAuthorize'            => env('URL_AUTHORIZE'),
                'urlAccessToken'          => env('URL_ACCESS_TOKEN'),
                'urlResourceOwnerDetails' => env('URL_RESOURCE_OWNER_DETAILS'),

            ], ['optionProvider' => new HttpBasicAuthOptionProvider()]);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
