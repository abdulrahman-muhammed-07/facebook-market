<?php

namespace App\Console\Commands\Facebook;

use App\Jobs\Facebook\FetchErrorReportForProductsJob;
use App\Models\User;
use Illuminate\Console\Command;

class FetchErrorReportForProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'FetchReport';

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
            dispatch(new FetchErrorReportForProductsJob($user));
        }
    }
}
