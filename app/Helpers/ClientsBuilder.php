<?php

namespace App\Helpers;

use Grpc;
use Application\V1\Customers\CustomersClient;
use Application\V1\Menu\MenusClient;
use Application\V1\Products\ProductsClient;
use Application\V1\Settings\SettingsClient;
use Application\V1\Store_credit\StoreCreditClient;

class ClientsBuilder
{
    public static function getCredentials()
    {
        if (env("PLUGIN_ORIGIN") == 'Dev') {

            return [
                'credentials' => Grpc\ChannelCredentials::createInsecure()
            ];
        } else {

            return [
                'credentials' => Grpc\ChannelCredentials::createSsl()
            ];
        }
    }

    public static function getHostName()
    {
        if (env("PLUGIN_ORIGIN") == 'Dev') {

            return env('DEV_HOST_NAME');
        } else {

            return 'api.Application.com:443';
        }
    }

    public static function getStoreCreditClient()
    {
        return new StoreCreditClient(self::getHostName(), self::getCredentials());
    }

    public static function getCustomersClient()
    {
        return new CustomersClient(self::getHostName(), self::getCredentials());
    }

    public static function getMenusClient()
    {
        return new MenusClient(self::getHostName(), self::getCredentials());
    }

    public static function getProductsClient()
    {
        return new ProductsClient(self::getHostName(), self::getCredentials());
    }

    public static function getSettingsClient()
    {
        return new SettingsClient(self::getHostName(), self::getCredentials());
    }
}
