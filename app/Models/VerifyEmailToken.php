<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerifyEmailToken extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'token'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
