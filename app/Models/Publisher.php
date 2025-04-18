<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Publisher extends Model
{
    /** @use HasFactory<\Database\Factories\PublisherFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'country',
        'website',
        'logo_url',
    ];

    public function editions()
    {
        return $this->hasMany(Edition::class);
    }
}
