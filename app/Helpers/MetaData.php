<?php

namespace App\Helpers;

class MetaData
{
    public static function get($storeId)
    {
        return array(
            'authorization' => array('Bearer ' . AccessToken::getAccessToken($storeId)),
            'x-client-id' => array((string)$storeId)
        );
    }
}
