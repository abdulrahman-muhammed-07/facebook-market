<?php

namespace App\Http\Traits;

use App\Helpers\ErrorLogger;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use League\OAuth2\Client\Provider\GenericProvider;

trait AccessToken
{
    public function getAccessToken(int $storeId): ?string
    {
        $user = User::where('store_id', $storeId)->first();

        if (!$user->oauth || !$user->oauth->access_token) {

            if (env("REPORT_TOKENS_EXPIRY") == true) {

                Http::post('https://hooks.slack.com/services/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', [
                    'text' => '<!everyone> Help ! I can\'t get an Access token in ' . $user->name . ' store, click  <' . env('PLUGIN_LINK') . ' | Here> to help FaceBook'
                ]);
            }

            return null;
        }

        if (time() > (int) $user->oauth->expiry_date - 15) {

            $newAccessToken = $this->newAccessToken($user->store_id);

            if ($newAccessToken) {

                return  $newAccessToken;
            } else {

                return null;
            }
        }
        return $user->oauth->fresh()->access_token;
    }

    public function newAccessToken(int $storeId): ?string
    {
        $user = User::where('store_id', $storeId)->first();

        if ($user->oauth->refresh_token) {

            try {

                $provider = app(GenericProvider::class);

                $accessToken = $provider->getAccessToken('refresh_token', [

                    'refresh_token' => $user->oauth->refresh_token
                ]);

                //

            } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

                ErrorLogger::logError($e, $storeId);

                if (env("REPORT_TOKENS_EXPIRY") == true) {

                    Http::post('https://hooks.slack.com/services/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', [
                        'text' => '<!everyone> Help ! I can\'t get an Access token in ' . $user->name . ' store, click  <' . env('PLUGIN_LINK') . ' | Here> to help FaceBook'
                    ]);
                }

                return null;
            }

            $user->oauth->update([
                'access_token' => $accessToken->getToken(),
                'refresh_token' => $accessToken->getRefreshToken(),
                'expiry_date' => $accessToken->getExpires()
            ]);

            return $accessToken->getToken();
        }

        return null;
    }
}
