<?php

namespace App\Console\Commands\App;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class productionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set:production  {--APP_CLIENT_ID=} {--APP_SECRET=} {--REDIRECT_URL=} {--PLUGIN_LINK=} {--DB_HOST=} {--DB_DATABASE=} {--DB_USERNAME=} {--DB_PASSWORD=} {--QUEUE_CONNECTION=} {--SCOPE=} {--PLUGIN_CODE=} {--ACCESS_TYPE=} {--URL_AUTHORIZE=} {--DB_PORT=} {--APP_URL=} {--URL_ACCESS_TOKEN=} {--URL_RESOURCE_OWNER_DETAILS=}  {--GOOGLE_CLIENT_ID=} {--GOOGLE_CLIENT_SECRET=} {--GOOGLE_REDIRECT_URL=} {--GOOGLE_APPROVAL_PROMPT=} {--GOOGLE_ACCESS_TYPE=} {--GOOGLE_SCOPE_FOR_REFRESH_TOKEN=} {--GOOGLE_SCOPE_FOR_API_AUTH=} {--APP_DEV=}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->updateEnv([
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'SCOPE' => str_replace('-', ' ', $this->option('SCOPE')),
            'PLUGIN_CODE' => $this->option('PLUGIN_CODE'),
            'DB_PORT' => $this->option('DB_PORT'),
            'APP_URL' => $this->option('APP_URL'),
            'ACCESS_TYPE' => $this->option('ACCESS_TYPE'),
            'URL_AUTHORIZE' => $this->option('URL_AUTHORIZE'),
            'URL_ACCESS_TOKEN' => $this->option('URL_ACCESS_TOKEN'),
            'URL_RESOURCE_OWNER_DETAILS' => $this->option('URL_RESOURCE_OWNER_DETAILS'),
            'APP_CLIENT_ID' => $this->option('APP_CLIENT_ID'),
            'APP_SECRET' => $this->option('APP_SECRET'),
            'REDIRECT_URL' => $this->option('REDIRECT_URL'),
            'PLUGIN_LINK' => $this->option('PLUGIN_LINK'),
            'DB_HOST' => $this->option('DB_HOST'),
            'DB_DATABASE' => $this->option('DB_DATABASE'),
            'DB_USERNAME' => $this->option('DB_USERNAME'),
            'DB_PASSWORD' => $this->option('DB_PASSWORD'),
            'QUEUE_CONNECTION' => $this->option('QUEUE_CONNECTION'),
            "APP_DEV" => false
        ]);
    }







    public function updateEnv($data = array())
    {
        if (!count($data)) {
            return;
        }
        $pattern = '/([^\=]*)\=[^\n]*/';
        $envFile = base_path() . '/.env';
        $lines = file($envFile);
        $newLines = [];
        foreach ($lines as $line) {
            preg_match($pattern, $line, $matches);
            if (!count($matches)) {
                $newLines[] = $line;
                continue;
            }
            if (!key_exists(trim($matches[1]), $data)) {
                $newLines[] = $line;
                continue;
            }
            $line = trim($matches[1]) . "='{$data[trim($matches[1])]}'\n";
            $newLines[] = $line;
        }
        $newContent = implode('', $newLines);
        file_put_contents($envFile, $newContent);
    }
}
