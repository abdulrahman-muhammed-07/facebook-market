<?php

namespace App\Http\Controllers\Facebook;

use App\Http\Controllers\Controller;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SaveFacebookCatalogIdController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'catalog_id' => 'required|numeric|digits_between:15,30'
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => $validator->errors()
                ]
            );
        }

        try {

            UserSetting::where('store_id', '=', $request->store_id)->update(['catalog_id' => $request->catalog_id]);

            return response()->json(
                [
                    'status' => 'success',
                    'message' => 'catalog id saved successfully'
                ]
            );

            //

        } catch (\Throwable $th) {

            return response()->json(
                [
                    'status' => 'error',
                    'message' => $th->getMessage()
                ]
            );

            //

        }
    }
}
