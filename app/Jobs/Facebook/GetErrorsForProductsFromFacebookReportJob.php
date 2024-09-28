<?php

namespace App\Jobs\Facebook;

use App\Mail\ReportEmailNoticeDone;
use App\Mail\ReportErrorFetchEmail;
use App\Models\User;
use App\Models\Variants;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use League\Csv\Reader;

class GetErrorsForProductsFromFacebookReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $storeId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public User $user)
    {
        $this->storeId = $user->store_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $errorReportFile = md5($this->storeId);

        $file = storage_path() . "/app/public/{$this->storeId}/{$errorReportFile}-report.csv";

        $handle = fopen($file, "r");

        $idToErrors = array();

        while (($row = fgetcsv($handle)) !== false) {

            $id = $row[0];

            if ($id == "ID") continue;

            $error = array(
                'line' => $row[1],
                'severity' => $row[2],
                'summary' => $row[3],
                'description' => $row[4],
                'property_names' => $row[5],
                'property_values' => $row[6],
                'error_blocks_capability' => $row[7],
                'capabilities' => $row[8]
            );

            if (array_key_exists($id, $idToErrors)) {

                $idToErrors[$id][] = $error;
            } else {

                $idToErrors[$id] = array($error);
            }
        }

        foreach ($idToErrors as $key => $productError) {

            Variants::where('variant_id', $key)->update([
                'facebook_feed_error' => json_encode($productError),
                'facebook_feed_error_last_fetched' => time()
            ]);
        }

        $userEmailToNotice = 'abdelrahman.elnegery@Application.com';

        Mail::to($userEmailToNotice)->send(new ReportEmailNoticeDone());
    }
}
