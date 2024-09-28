<?php

namespace App\Models;

use App\Models\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Oauth extends Model
{
    use HasFactory;
    // use BelongsToUser;

    protected $fillable = [
        'user_store_id',
        'access_token',
        'refresh_token',
        'expiry_date',
        'store_name'
    ];
    protected $primaryKey = 'user_store_id';

    public $incrementing = false;
}
