<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use GuzzleHttp\Client;
use App\Helpers\AccessToken;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterFormRequest;
use App\Http\Resources\PrivateUserResource;
use League\OAuth2\Client\Provider\GenericProvider;

class LoginController extends Controller
{
    public function __construct(public GenericProvider $provider)
    {
        //
    }

    public function action(RegisterFormRequest $request)
    {
        $user = User::with('oauth')->firstOrCreate([

            'email' => $request->safe()->extra['admin_email'],
            'store_id' => $request->safe()->store_id

        ], $request->safe()->only(['password']) + ['name' => $request->safe()->extra['admin_name']]);

        if (!$token = auth()->attempt([

            'email' => $request->safe()->extra['admin_email'],
            'password' => $request->safe()->password

        ])) {
            return response()->json([

                'errors' => [

                    'email' => 'Couldn\'t sign you in with these credentials'

                ]

            ], 422);
        }

        $user = User::where('store_id', $request->safe()->store_id)->first();

        if (isset($user->oauth->expiry_date) && time() > (int) $user->oauth->expiry_date - 50) {

            if (env("ACCESS_TOKEN_SOURCE") == 'Proxy') {

                $newAccessToken = AccessToken::newProxyAccessToken($user->store_id);
                //

            } else {

                $newAccessToken = AccessToken::newProviderAccessToken($user->store_id);
                //
            }

            if ($newAccessToken) {

                $user = User::where('store_id',  $request->safe()->store_id)->first();
            }
        }

        $expiry = isset($user->oauth->expiry_date) ? $user->oauth->expiry_date : 0;

        $plugin_origin = env('PLUGIN_ORIGIN');

        return (new PrivateUserResource($user))

            ->additional([

                'meta' => [

                    'token' => $token,

                    'plugin_origin' => $plugin_origin,

                    'expiry' => $expiry

                ] + $this->getAuthUrl($user)

            ]);
    }

    protected function getAuthUrl(User $user)
    {
        $user->fresh();

        $options = [
            'scope' => [env('SCOPE')],
            'access_type' => env('ACCESS_TYPE'),
            'plugin_code' => env('PLUGIN_CODE')
        ];

        $url = $this->provider->getAuthorizationUrl($options);

        $user->state()->updateOrCreate(['user_store_id' => $user->store_id], [
            'state' => md5($this->provider->getState()),
            'expiry_date' => time() + 300
        ]);

        $redirect = true;

        if (isset($user->oauth) && ($user->oauth->access_token != null)) {

            $redirect = false;
        }

        return ['authUrl' => $url, 'redirect' => $redirect];
    }
}
