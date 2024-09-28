<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class ErrorLogger
{
    static function logError($th, $storeId, $additionalMessage = null): void
    {
        Config::set("logging.channels.custom-$storeId", [
            'driver' => 'daily',
            'path' => storage_path("logs/FaceBook-store-$storeId.log"),
            'level' => 'debug',
            'days' => 14,
        ]);

        $message = !$additionalMessage ? ($th->getMessage() . " in store " . $storeId) : ($th->getMessage() . " in store " . $storeId . 'and ' . $additionalMessage);

        $context = [
            'stacktrace' => $th->getTraceAsString()
        ];

        Log::channel("custom-$storeId")->error($message, $context);
    }
}
