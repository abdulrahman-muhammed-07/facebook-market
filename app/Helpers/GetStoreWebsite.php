<?php

namespace App\Helpers;

use Google\Protobuf\GPBEmpty;
use Grpc;

class GetStoreWebsite
{
    static function getStoreWebsite(int $storeId)
    {
        $settings = self::getSettings($storeId);

        if (isset($settings[1]) && $settings[1]->details == 'system error') {

            dd('authorization missing or expired');
            return false;
        }
        
        $website = $settings[0]->getGeneralSettings()->getStoreDetails()->getStoreWebsite();

        return $website;
    }

    static function getStoreName($storeId)
    {
        $settings = self::getSettings($storeId);

        if (GrpcErrorHandle::checkGrpcErrors($settings, $storeId) == false) {
            return false;
        }

        $storName = $settings[0]->getGeneralSettings()->getStoreDetails()->getStoreNameUnwrapped();
        return $storName;
    }

    private static function getSettings($storeId)
    {
        $settings_client = ClientsBuilder::getSettingsClient();

        $setting_request = new GPBEmpty();

        $settings = $settings_client->GetStoreWebsite($setting_request, MetaData::get($storeId));

        $settings = $settings->wait();

        return  $settings;
    }
}
