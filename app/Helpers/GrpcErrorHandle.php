<?php

namespace App\Helpers;

use App\Models\Oauth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class GrpcErrorHandle
{
    static function checkGrpcErrors(array $waited_array, int $storeId): bool | array
    {
        if ($waited_array[1]->code == 16) {

            $store = Oauth::where('user_store_id', '=', $storeId)->first() ?: '';

            if (!empty($store)) {

                $storeName = $store->store_name;

                $pluginName = env('PLUGIN_NAME');

                $link = env('PLUGIN_LINK');

                if (env('APP_ENV') === 'production') {

                    Http::post(
                        'https://hooks.slack.com/services/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',
                        [
                            'text' => '<!everyone> Help ! I can\'t get an Access token in ' . $storeName . ' store, click  <' . $link . ' | Here> to help ' . $pluginName
                        ]
                    );
                }
            }

            return
                [
                    'status' => false,
                    'message' => $waited_array[1]->details,
                    'code' => $waited_array[1]->code
                ];
        } elseif ($waited_array[1]->code) {

            return
                [
                    'status' => false,
                    'message' => $waited_array[1]->details,
                    'code' => $waited_array[1]->code
                ];
        };

        if ($waited_array[0] == null || $waited_array[0]->getFailure()) {

            $code = $waited_array[0]->getCode();

            $message = $waited_array[0]->getMessage();

            if ($code != '412' && $code != '240' && $code != '1001') {

                if (env('APP_ENV') === 'production') {

                    DatabaseErrorLog::log(["error_message" => "gRPC error code {$waited_array[1]->code}: {$message}"], $storeId);
                }

            }
        }

        return [
            'status' => 'success',
            'message' => $waited_array[1]->details,
            'code' => $waited_array[1]->code
        ];
    }
}
