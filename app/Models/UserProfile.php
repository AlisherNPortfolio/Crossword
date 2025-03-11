<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bio',
        'first_name',
        'last_name',
        'total_solved',
        'total_competitions',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
