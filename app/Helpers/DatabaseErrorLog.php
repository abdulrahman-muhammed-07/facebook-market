<?php

namespace App\Helpers;

use App\Models\Log;

class DatabaseErrorLog
{
    static function log(array $log, int $storeId, $issues = null): void
    {
        try {
            Log::create([
                'user_store_id' => $storeId,
                'log' => json_encode($log),
                'issues' =>  $issues
            ]);
        } catch (\Throwable $th) {
            dd([
                'user_store_id' => $storeId,
                'log' => json_encode($log),
                'issues' =>  $issues,
                'error' => $th->getMessage(),
                'error' => $th
            ]);
        }
    }
}
