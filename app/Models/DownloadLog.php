<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DownloadLog extends Model
{
    use HasFactory;


    public $fillable = [
        'user_store_id',
        'csv_file_name',
        'ip_downloaded_file'
    ];


    protected static function boot()
    {
        parent::boot();

        DownloadLog::creating(function ($model) {
            $model->last_download = date('l jS \of F Y h:i:s A');
            $model->last_download_unix = time();
        });
    }
    
}
