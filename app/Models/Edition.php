<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Edition extends Model
{
    /** @use HasFactory<\Database\Factories\EditionFactory> */
    use HasFactory;

    protected $fillable = [
        'id', // el id será el anilist_id concatenado al codigo de idioma, ej: 123456ES
        'series_id',
        'localized_title',
        'publisher_id',
        'language',
        'edition_total_volumes',
        'format',
        'type',
        'country_code'
    ];

    public function series()
    {
        return $this->belongsTo(Series::class);
    }

    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }

    public function volumes()
    {
        return $this->hasMany(Volume::class);
    }
}
