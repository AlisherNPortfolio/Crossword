<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'competition_id',
        'solution_data',
        'completed',
        'score',
        'time_taken',
        'ranking'
    ];

    protected $casts = [
        'solution_data' => 'array',
        'completed' => 'boolean',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function competition() {
        return $this->belongsTo(Competition::class);
    }
}
