<?php

namespace App\Http\Traits;

use App\Models\Log;

trait ErrorLog
{
    public function log(array $log, int $store_id, $issues = null): void
    {
        try {
            Log::create([
                'user_store_id' => $store_id,
                'log' => json_encode($log),
                'issues' => json_encode($issues)
            ]);
        } catch (\Throwable $th) {
            dd([
                'user_store_id' => $store_id,
                'log' => json_encode($log),
                'issues' =>  $issues,
                'error' => $th->getMessage(),
                'error_more' => $th
            ]);
        }
    }
}
