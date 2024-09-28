<?php

namespace App\Http\Controllers\Facebook;

use App\Http\Controllers\Controller;
use App\Models\UserSetting;
use Illuminate\Http\Request;

class CheckFacebookAccessTokenExpiryStatusController extends Controller
{
    public function FacebookAccessTokenCheck(Request $request)
    {
        $UserFacebookDatabaseData = UserSetting::select('expiry_date')->where('store_id', '=', $request->store_id)->first();

        if (!$UserFacebookDatabaseData || $UserFacebookDatabaseData->expiry_date == null) {

            return response()->json(
                [
                    'status' => 'info',
                    'message' => 'User has no active connection with facebook or data removed from our records'
                ]
            );
        }

        $expiryStatus =  time() > $UserFacebookDatabaseData->expiry_date ?  'expired' : 'valid';

        return response()->json([
            'status' => 'info',
            'message' => 'Facebook Access token is ' . $expiryStatus
        ]);
    }
}
