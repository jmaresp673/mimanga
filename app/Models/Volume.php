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
        'price',
        'release_date',
        'cover_image_url',
        'google_books_id',
        'buy_link', // Ebook buylink
    ];

    protected $casts = [
        'release_date' => 'date:Y-m-d'
    ];


// Mutadores para manejar la conversión automática
    public function setPriceAttribute($value)
    {
        // Convertir "8,00" a 8.00 y "1.230,50" a 1230.50
        $this->attributes['price'] = (float)str_replace(
            [' ', '.', ','],
            ['', '', '.'],
            (string)$value
        );
    }

    public function setReleaseDateAttribute($value)
    {
        $this->attributes['release_date'] = \Carbon\Carbon::parse($value)->format('Y-m-d');
    }

    public function series()
    {
        return $this->belongsTo(Series::class);
    }

    public function edition()
    {
        return $this->belongsTo(Edition::class, 'edition_id', 'id');
    }

    public function userVolumes()
    {
        return $this->hasMany(UserVolume::class);
    }
}
