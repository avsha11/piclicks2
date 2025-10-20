<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;
    protected $table = 'discounts';
    protected $guarded = [];

    public function priceManagement()
    {
        return $this->belongsTo(PriceManagement::class, 'price_management_id');
    }
}
