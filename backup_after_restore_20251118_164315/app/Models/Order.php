<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';
    protected $guarded = [];

    public function orderdetail_data()
    {
        return $this->hasMany(OrderDetail::class, 'master_id')->with('design_collage_master', 'design_collage', 'giftcard_data');
    }

    public function transaction_data()
    {
        return $this->hasMany(Transaction::class, 'master_id');
    }


    public function shipping_country_data()
    {
        return $this->belongsTo(Countries::class, 'shipping_country', 'code');
    }

    public function user_data()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'total_quantity' => 'integer',
        'total_amount' => 'double',
        'sub_total' => 'double',
        'frame_amount' => 'double',
        'shipping_amount' => 'double',
        'vat_amount' => 'double',
        'discount_amount' => 'double',
        'giftcard_amount' => 'double',
    ];
}
