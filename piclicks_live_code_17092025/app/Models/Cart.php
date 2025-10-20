<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['user_id', 'product_id', 'price_id', 'name', 'price', 'quantity', 'size_horiz', 'size_vert', 'frame', 'giftcard_name', 'email', 'message'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function designCollage()
    {
        return $this->hasMany(DesignCollageModel::class, 'unique_id', 'product_id');
    }

    public function designCollageMaster()
    {
        return $this->hasOne(DesignCollageMaster::class, 'unique_id', 'product_id');
    }

    public function giftcard()
    {
        return $this->hasOne(Giftcard::class, 'id', 'product_id');
    }
}
