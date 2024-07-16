<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MyLoginQrCode extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_id',
        'token',
        'is_taken',
        'expires_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
