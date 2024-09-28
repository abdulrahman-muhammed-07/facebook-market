<?php

namespace App\Http\Traits;

use App\Models\Oauth;
use Illuminate\Support\Facades\Http;

trait gRPCErrorLog
{
    use AccessToken;

    public function checkGrpcErrors(array $waited_array, $storeId): bool
    {
        if ($waited_array[1]->code == 16) {

            $store = Oauth::where('user_store_id', '=', $storeId)->first() ?: '';

            $storeName = $store->store_name;

            $link = env('PLUGIN_LINK');

            if (env('APP_ENV') === 'production' && env('APP_DEV') === false) {

                Http::post('https://hooks.slack.com/services/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', [
                    'text' => '<!everyone> Help ! I can\'t get an Access token in ' . $storeName . ' store, click  <' . $link . ' | Here> to help eBay'
                ]);
            }
        }

        if ($waited_array[0] == null) {

            return false;
        } elseif ($waited_array[0]->getFailure()) {

            $code = $waited_array[0]->getCode();

            $message = $waited_array[0]->getMessage();
            
            if ($code != '412' && $code != '240' && $code != '1001') {

                if (env('APP_ENV') === 'production') {

                    $this->log(["error_message" => sprintf('gRPC error code %d message %s', $waited_array[1]->code, $message)], $storeId);
                }

                return false;
            }
        }
        return true;
    }
}
