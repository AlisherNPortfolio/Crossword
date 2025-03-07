<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSolution extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'crossword_id',
        'solution_data',
        'completed',
        'score',
        'time_taken'
    ];

    protected $casts = [
        'solution_data' => 'array',
        'completed' => 'boolean',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function crossword() {
        return $this->belongsTo(Crossword::class);
    }
}
