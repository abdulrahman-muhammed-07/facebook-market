<?php

namespace App\Jobs\Facebook;

use App\Helpers\DatabaseErrorLog;
use App\Mail\ReportErrorFetchEmail;
use App\Models\UserSetting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class FetchErrorReportForProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $uploadSessionId;

    public $facebookAccessToken;

    public $reportStatus;

    public function __construct(public User $user)
    {
        //
    }

    public function handle()
    {
        $thirdPartyFacebookData = UserSetting::where('user_store_id', '=', $this->user->store_id)->first();

        if (!$thirdPartyFacebookData) return;

        if (!$this->uploadSessionId) {

            $uploadFetchSessionId = $this->getLatestUploadSessionId($thirdPartyFacebookData);
        } else {

            $uploadFetchSessionId = $this->uploadSessionId;
        }

        try {
            $fetchErrorReportWithLastSessionIdFromFacebook = Http::get(
                "https://graph.facebook.com/$uploadFetchSessionId",

                [
                    'access_token' => $this->facebookAccessToken,
                    'fields' => "error_report"
                ]
            );

            //

        } catch (\Throwable $th) {

            DatabaseErrorLog::log(['message' => $th->getMessage()], $this->user->store_id);
        }

        if ($fetchErrorReportWithLastSessionIdFromFacebook->json()) {

            $responseAboutReportFromFacebook = $fetchErrorReportWithLastSessionIdFromFacebook->json();

            $fileHandle = $responseAboutReportFromFacebook['error_report']['file_handle'] ?? null;

            $reportStatus = $responseAboutReportFromFacebook['error_report']['report_status'];
        }

        if (!$reportStatus) return;

        $this->checkReportStatus($reportStatus,  $fileHandle);

        echo ('Done job');
    }

    public function getLatestUploadSessionId($thirdPartyFacebookData)
    {
        $thirdPartyFacebookData = $thirdPartyFacebookData->toArray();

        $feedId = $thirdPartyFacebookData['facebook_feed_id'];

        if (!$feedId) return;

        $this->facebookAccessToken = $thirdPartyFacebookData['access_token'];

        try {

            $uploadFetchSessionIdResponse = Http::get("https://graph.facebook.com/{$feedId}/uploads", [

                "access_token" => $this->facebookAccessToken,

            ]);

            //

        } catch (\Throwable $th) {

            DatabaseErrorLog::log(['message' => $th->getMessage()], $this->user->store_id);
            //
        }

        if (isset($uploadFetchSessionIdResponse->json()['data'][0])) {

            $this->uploadSessionId = $uploadFetchSessionIdResponse->json()['data'][0]['id'];

            //

        } elseif (isset($uploadFetchSessionIdResponse->json()['data']['id'])) {

            $this->uploadSessionId = $uploadFetchSessionIdResponse->json()['data']['id'];

            //

        } else {

            return;
        }

        $uploadSessionId = $this->uploadSessionId;

        return $uploadSessionId;
    }

    public function checkReportStatus($reportStatus, $fileHandle = null)
    {
        $uploadSessionId = $this->uploadSessionId;

        if ($reportStatus == 'NOT_REQUESTED') {

            try {

                $requestErrorReportToMAkeFromFacebookFirst = Http::post("https://graph.facebook.com/$uploadSessionId/error_report", ['access_token' => $this->facebookAccessToken]);
            } catch (\Throwable $th) {

                dd($th->getMessage());
            }

            echo ("New report request for Session " . $uploadSessionId);

            sleep(10);

            dispatch(new FetchErrorReportForProductsJob($this->user));
        }

        if ($reportStatus == "SESSION_DATA_NOT_FOUND") {

            $this->noticeErrorInLastFeedUpload();

            return;
        };

        if ($reportStatus == "WRITE_FINISHED") {

            if (!$fileHandle) {

                DatabaseErrorLog::log(['message' => 'error in file handle not found'], $this->user->store_id);

                break;
            }

            $this->getErrorReportHandleFileFromFacebook($fileHandle);
        };

        if ($reportStatus == "SESSION_DATA_NOT_FOUND") {

            $this->noticeErrorInLastFeedUpload();

            return;
        };
    }

    public function getErrorReportHandleFileFromFacebook($fileHandle)
    {
        $directory = storage_path() . "/app/public/{$this->user->store_id}";

        if (!is_dir($directory)) {

            mkdir($directory);
        }

        $errorReportFile = md5($this->user->store_id);

        $file_path = $directory . "/{$errorReportFile}-report1.csv";

        touch($file_path);

        $fileHandle = file_get_contents($fileHandle);

        try {

            file_put_contents($file_path, $fileHandle);
        } catch (\Throwable $th) {

            DatabaseErrorLog::log(['message' => $th->getMessage()], $this->user->store_id);

            break;
        }

        echo ('Done getting the error report - ');

        return;
    }

    public function noticeErrorInLastFeedUpload()
    {
        $userEmailToNotice = $this->user->email;

        $userEmailToNotice = 'abdelrahman.elnegery@Application.com';

        Mail::to($userEmailToNotice)->send(new ReportErrorFetchEmail());

        dump('sent mail to admin');

        return;
    }
}
