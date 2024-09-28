<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Traits\AccessToken;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterFormRequest;
use App\Http\Resources\PrivateUserResource;
use App\Models\Oauth;
use League\OAuth2\Client\Provider\GenericProvider;
use Symfony\Component\Console\Output\Output;

class WidgetController extends Controller
{
    use AccessToken;

    public function validateWidget(RegisterFormRequest $request)
    {
        $user = User::with('oauth')->where('store_id', $request->safe()->store_id)->first();

        $expiry_date = $user->oauth->expiry_date;

        if ($user == null || !$expiry_date) {

            return response()->json([
                'status' => 'error',
                'message' => 'Please Install the app'
            ], 422);
        }


        if ($expiry_date && time() > (int) $expiry_date - 50) {

            return response()->json([
                'status' => 'error',
                'message' => 'Please refresh the app'
            ], 422);
        }

        return
            response()->json(
                [
                    'status' => 'success',
                    'message' => 'plugin is installed , redirect to get data',
                ],
                300
            );
    }
}
