<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaceBookExportStatus extends Model
{
    use HasFactory;

    public $fillable = [
        'user_store_id',
        'log',
        'exported_at',
        'number_of_exported_products'
    ];
}
