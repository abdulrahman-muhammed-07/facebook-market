<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsvFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'user_store_id',
        'expiry_date'
    ];
}
