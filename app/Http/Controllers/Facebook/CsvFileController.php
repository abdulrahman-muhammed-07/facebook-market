<?php

namespace App\Http\Controllers\Facebook;

use App\Http\Controllers\Controller;
use App\Models\CsvFile;
use App\Models\DownloadLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class CsvFileController extends Controller
{
    public function download(Request $request)
    {
        if (strlen($request->token) == 46) {

            $storeId = $request->store_id;

            if ($storeId == 0 || $storeId == null) {

                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'The link file you trying to reach is either wrong or expired'
                    ]
                );
            }

            $file_name = CsvFile::select('file_name')->where('user_store_id', '=', $storeId)->orderBy('id', 'desc')->first();

            if ($file_name != null) {

                $file_name = $file_name->value('file_name');

                if (file_exists(storage_path() . '/app/public/' . $file_name . '.csv')) {

                    DownloadLog::create(
                        [
                            'file_name' => $file_name,
                            'ip_downloaded_file' => $request->ip()
                        ]
                    );

                    return Response::download(storage_path() . '/app/public/' . $file_name . '.csv');
                }
            }
        }

        return response()->json(
            [
                'status' => 'error',
                'message' => 'The link file you trying to reach is either wrong or expired'
            ]
        );
    }
}
