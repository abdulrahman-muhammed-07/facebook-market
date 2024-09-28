<?php

namespace App\Http\Controllers\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CsvFile;
use App\Models\DownloadLog;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class DownloadCsvFileForFeed extends Controller
{
    public function downloadCsvFile(Request $request)
    {
        $this->validate($request, ['download_token' => 'required']);

        $requestedFileToDownloadWithToken =   UserSetting::where('download_token', '=', $request->download_token)->first();

        if (!$requestedFileToDownloadWithToken) {

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired download token provided'
            ]);
        }

        $storeId = $requestedFileToDownloadWithToken->user_store_id;

        $fileName = CsvFile::select('file_name')->where('user_store_id', '=', $storeId)->orderBy('id', 'desc')->first();

        if (isset($fileName) && file_exists(storage_path() . "/app/public/{$storeId}/{$fileName->file_name}.csv")) {

            DownloadLog::create([
                'user_store_id' => $storeId,
                'csv_file_name' => $fileName->file_name,
                'ip_downloaded_file' => $request->ip()
            ]);

            return Response::download(storage_path() . "/app/public/{$storeId}/{$fileName->file_name}.csv");
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Sorry , No feed files found for this user , create one to be able to download'
        ]);
    }

    public function downloadCsvFileForAdmin(Request $request)
    {
        $requestedFileToDownloadWithToken =   UserSetting::where('user_store_id', '=', $request->store_id)->first();

        if (!$requestedFileToDownloadWithToken) {

            return response()->json([

                'status' => 'error',
                'message' => 'Invalid user'
            ], 401);
        }

        $storeId =  $request->store_id;

        $fileName = CsvFile::select('file_name')->where('user_store_id', '=', $storeId)->orderBy('id', 'desc')->first();

        if (isset($fileName) && file_exists(storage_path() . "/app/public/{$storeId}/{$fileName->file_name}.csv")) {

            DownloadLog::create([
                'user_store_id' => $storeId,
                'csv_file_name' => $fileName->file_name,
                'ip_downloaded_file' => $request->ip()
            ]);

            return Response::download(storage_path() . "/app/public/{$storeId}/{$fileName->file_name}.csv");
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Sorry , No feed files found for this user , create one to be able to download'
        ]);
    }
}
