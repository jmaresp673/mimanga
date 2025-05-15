<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    /** @use HasFactory<\Database\Factories\SeriesFactory> */
    use HasFactory;

    protected $fillable = [
        'id',
        'title',
        'romaji_title',
        'native_title',
        'synopsis',
        'anilist_id',
        'status',
        'total_volumes',
        'cover_image_url',
        'banner_image_url',
        'start_year',
        'end_year',
        'type'
    ];

    public function editions()
    {
        return $this->hasMany(Edition::class);
    }

    public function volumes()
    {
        return $this->hasMany(Volume::class);
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'series_genre')
            ->using(SeriesGenre::class);
    }

    public function authors()
    {
        return $this->belongsToMany(Author::class, 'author_series')
            ->using(AuthorSeries::class)
            ->withPivot('role');
    }
}
