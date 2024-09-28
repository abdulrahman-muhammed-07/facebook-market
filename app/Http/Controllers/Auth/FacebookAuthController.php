<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\UserSetting;
use Illuminate\Http\Request;

class FacebookAuthController extends Controller
{
  public function redirectToFacebook(Request $request)
  {
    $request->session()->put('store_id', $request->store_id);

    return Socialite::driver('facebook')->setScopes(['catalog_management'])->redirect();
  }

  public function handleFacebookCallBack(Request $request)
  {
    $storeId = $request->session()->get('store_id');

    if (!$storeId) return response()->json([
      'status' => 'error',
      'message' => 'authentication process failed or took too long time to response , try again'
    ]);

    try {

      $user = Socialite::driver('facebook')->stateless()->user();

      UserSetting::updateOrCreate(
        [
          'user_store_id' => $storeId
        ],
        [
          'access_token' => $user->token,
          'refresh_token' => $user->refreshToken ?? null,
          'expiry_date' =>  time() + $user->expiresIn - 20
        ]
      );

      return response()->json([
        'status' => 'success',
        'message' => 'Success adding user token in database'
      ]);
    } catch (\Throwable $th) {

      return response()->json([
        'status' => 'error',
        'message' => $th->getMessage()
      ]);
    }
  }
}
