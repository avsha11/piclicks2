<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderGiftcard extends Model
{

    use HasFactory;
    protected $table = 'order_giftcard';
    protected $guarded = [];
    public function order_data()
    {
        return $this->belongsTo(Order::class, 'master_id');
    }
    public function order_giftcard_used()
    {
        return $this->belongsTo(Order::class, 'used_on_order_id');
    }
    public function giftcard_data()
    {
        return $this->belongsTo(Giftcard::class, 'giftcard_id', 'id');
    }
    public function redeem_by_data()
    {
        return $this->belongsTo(User::class, 'redeem_by', 'id');
    }


    
    protected $casts = [
        'price' => 'double',
        'quantity' => 'integer',
        'total_amt' => 'double',
        'status' => 'integer',
        'redeem_by' => 'integer',
        'used_on_order_id' => 'integer',
    ];
}
