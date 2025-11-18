<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transaction';
    protected $guarded = [];

    public function order_data()
    {
        return $this->belongsTo(Order::class, 'master_id');
    }
}
