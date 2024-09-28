<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\SettingsRequest;
use App\Models\UserSetting;
use Illuminate\Http\Request;

class CatalogIdAddToDatabase extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request, ['catalog_id' => 'required']);

        $requestedFileToDownloadWithToken = UserSetting::where('user_store_id', '=', $request->store_id)->first();

        if (!$requestedFileToDownloadWithToken) return response()->json([
            'status' => 'error',
            'message' => 'Invalid or non exist store.'
        ]);

        $requestedFileToDownloadWithToken->facebook_catalog_id = $request->catalog_id;

        $requestedFileToDownloadWithToken->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Catalog Id saved to our records and will be used as primary Catalog to post future feeds on Facebook'
        ]);
    }
}
