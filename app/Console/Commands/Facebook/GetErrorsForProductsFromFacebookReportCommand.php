<?php

namespace App\Console\Commands\Facebook;

use App\Jobs\Facebook\GetErrorsForProductsFromFacebookReportJob;
use App\Models\User;
use Illuminate\Console\Command;

class GetErrorsForProductsFromFacebookReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'getErrors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users = User::get();

        foreach ($users as $user) {

            dispatch(new GetErrorsForProductsFromFacebookReportJob($user));
            
        }
    }
}
