<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    /** @use HasFactory<\Database\Factories\AuthorFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'anilist_id'
    ];

    public function series()
    {
        return $this->belongsToMany(Series::class, 'author_series')->withPivot('role');
    }
}
