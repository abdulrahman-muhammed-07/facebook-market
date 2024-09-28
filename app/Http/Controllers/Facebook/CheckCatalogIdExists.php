<?php

namespace App\Http\Controllers\Facebook;

use App\Models\UserSetting;
use Illuminate\Http\Request;

class CheckCatalogIdExists
{
    public function checkCatalogIdExists(Request $request)
    {
        $requestedFileToDownloadWithToken = UserSetting::where('user_store_id', '=', $request->store_id)->first();

        if (!$requestedFileToDownloadWithToken) return response()->json([
            'status' => 'error',
            'message' => 'Invalid or non exist store.'
        ]);

        if (!isset($requestedFileToDownloadWithToken->facebook_catalog_id)) return response()->json([
            'status' => 'error',
            'message' => 'catalog id is mandatory , provide please to proceed'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'catalog Id is found'
        ]);
    }
}
