<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Frame extends Model
{
    use HasFactory;
    public $table = 'frames';
    protected $fillable = ['name','cost','status','frame_id','frame_image','created_at','updated_at'];
}
