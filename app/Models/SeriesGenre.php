<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeriesGenre extends Model
{
    /** @use HasFactory<\Database\Factories\SeriesGenreFactory> */
    use HasFactory;

    protected $table = 'series_genre';

    protected $fillable = [
        'series_id',
        'genre_id',
    ];
}
