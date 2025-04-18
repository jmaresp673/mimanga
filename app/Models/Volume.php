<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Volume extends Model
{
    /** @use HasFactory<\Database\Factories\VolumeFactory> */
    use HasFactory;

    protected $fillable = [
        'series_id',
        'edition_id',
        'volume_number',
        'total_pages',
        'isbn',
        'release_date',
        'cover_image_url',
        'google_books_id',
        'buy_link', // Ebook buylink
    ];

    public function series()
    {
        return $this->belongsTo(Series::class);
    }

    public function edition()
    {
        return $this->belongsTo(Edition::class);
    }

    public function userVolumes()
    {
        return $this->hasMany(UserVolume::class);
    }
}
