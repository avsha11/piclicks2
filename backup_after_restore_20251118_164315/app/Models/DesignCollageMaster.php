<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DesignCollageMaster extends Model
{
    use HasFactory;
    protected $table = "design_collage_master";
    protected $guarded = [];

    // public function DesignCollageMasters()
    // {
    //     return $this->belongsTo(DesignCollageMaster::class, 'product_id', 'unique_id');
    // }

    public function designs_data()
    {
        return $this->hasMany(DesignCollageModel::class, 'unique_id', 'unique_id');
    }

    public function admin_data()
    {
        return $this->hasOne(DesignCollageAdmin::class, 'unique_id', 'unique_id');
    }

    public function favorites_data()
{
    return $this->hasMany(ArtGalleryFavourite::class, 'unique_id', 'unique_id');
}


    protected $casts = [
        'id' => 'integer',
        'unique_id' => 'integer',
        'price_id' => 'integer',
        'user_id' => 'integer',
        'grid_rows' => 'integer',
        'grid_columns' => 'integer',
        'width' => 'double',
        'height' => 'double',
        'total_tiles' => 'integer',
        'status' => 'integer',
        'artgallery_unique_id' => 'integer',
    ];
}


