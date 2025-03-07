<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Crossword extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'grid_data',
        'words',
        'published',
        'created_by',
    ];

    protected $casts = [
        'grid_data' => 'array',
        'words' => 'array',
        'published' => 'boolean',
    ];

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function solutions() {
        return $this->hasMany(UserSolution::class);
    }

    public function competitions() {
        return $this->hasMany(Competition::class);
    }
}
