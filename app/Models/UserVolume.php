<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVolume extends Model
{
    /** @use HasFactory<\Database\Factories\UserVolumeFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'volume_id',
        'readed',
        'page',
        'purchase_date',
        'note',
        'rating'
    ];

    protected $casts = [
        'readed' => 'boolean',
        'purchase_date' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function volume()
    {
        return $this->belongsTo(Volume::class);
    }
}
