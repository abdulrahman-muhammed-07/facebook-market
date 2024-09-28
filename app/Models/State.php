<?php
namespace App\Models;

use App\Models\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;
    // use BelongsToUser;
    protected $fillable = [
        'user_store_id',
        'state',
        'expiry_date'
    ];

    public $timestamps = false;
    protected $primaryKey = 'user_store_id';
    public $incrementing = false;
}
