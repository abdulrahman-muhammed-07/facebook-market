<?php

namespace App\Http\Controllers\Controllers;

use App\Jobs\Facebook\ForceExportCsvFileForFeedJob;
use Illuminate\Http\Request;

class ForceExportCsvFileForFeed
{
    public function ForceExportCsvFileForFeed(Request $request)
    {
        $storeId = $request->get('store_id');

        dispatch(new ForceExportCsvFileForFeedJob($storeId));

        return response()->json(
            [
                'status' => 'success',
                'message' => 'File export is processing',
                'data' => 'check logs to make sure the process is done with no errors!'
            ]
        );
    }
}
