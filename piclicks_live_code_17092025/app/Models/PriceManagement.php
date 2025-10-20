<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceManagement extends Model
{
    use HasFactory;
    protected $table = 'price_management';
    protected $guarded = [];
}
