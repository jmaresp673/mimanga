<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AuthorSeries extends Pivot
{
    /** @use HasFactory<\Database\Factories\AuthorSeriesFactory> */
    use HasFactory;

    protected $table = 'author_series';

    protected $fillable = [
        'author_id',
        'series_id',
        'role',
    ];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function series()
    {
        return $this->belongsTo(Series::class);
    }
}
