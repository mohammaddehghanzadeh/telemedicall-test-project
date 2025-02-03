<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'title',
        'description',
        'start_time',
        'end_time',
        'location',
        'user_id'
    ];

    // Define the relationship between Event and User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
