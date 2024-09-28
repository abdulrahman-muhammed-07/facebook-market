<?php

namespace App\Http\Controllers\Facebook;

use App\Http\Controllers\Controller;
use App\Models\UserSetting;
use Illuminate\Http\Request;

class CheckFacebookLoginController extends Controller
{
    public function checkFacebookLogin(Request $request)
    {
        $userFacebookAccessToken = UserSetting::where('user_store_id', '=', $request->store_id)->first();

        if (!$userFacebookAccessToken || $userFacebookAccessToken == [] || $userFacebookAccessToken == null) {

            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Facebook login does not exist on our records . redirect to try login process'
                ]
            );
        }

        $returnResponse = [
            'status' => 'success',
            'message' => 'Login done'
        ];

        if ($userFacebookAccessToken->expiry_date) {

            $returnResponse[] = ['data' => 'Access Token will expire in ' . $userFacebookAccessToken->expiry_date];
        }
        
        return response()->json($returnResponse);
    }
}
