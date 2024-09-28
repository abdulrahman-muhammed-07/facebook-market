<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\SettingsRequest;
use App\Models\UserSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function store(SettingsRequest $request)
    {
        try {

            UserSetting::updateOrCreate(
                [
                    'user_store_id' =>  $request->store_id
                ],
                [
                    'mapping_settings' => json_decode($request->mapping_settings, true),
                    'facebook_catalog_id' => $request->facebook_catalog_id
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Settings Submitted!'
            ]);

            //

        } catch (\Throwable $th) {

            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ]);
        }
    }

    public function update(SettingsRequest $request)
    {
        try {

            UserSetting::where('user_store_id', '=', $request->store_id)->update(
                [
                    'mapping_settings' => json_decode($request->mapping_settings, true),
                    'facebook_catalog_id' => $request->facebook_catalog_id
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Settings Submitted!'
            ]);

            //

        } catch (\Throwable $th) {

            return response()->json([

                'status' => 'error',
                'message' => $th->getMessage()
            ]);
        }
    }

    public function delete(Request $request)
    {
        try {

            UserSetting::where('user_store_id', '=', $request->store_id)->delete();

            return response()->json([

                'status' => 'success',
                'message' => 'Settings reset!'

            ]);

            //

        } catch (\Throwable $th) {

            return response()->json([

                'status' => 'error',
                'message' => $th->getMessage()

            ]);
        }
    }
}
