<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArtGalleryFavourite extends Model
{
    use HasFactory;
    protected $table = 'artgallery_favorites';
    protected $guarded = [];
}
