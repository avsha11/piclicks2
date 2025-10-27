<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Ensure this is used
use Illuminate\Database\Eloquent\Model;

class Admin extends Authenticatable
{
    use HasFactory;
    public $table = 'admins';
    protected $guard = 'admins';
    protected $fillable = ['name','email','password','admin_profile','created_at','updated_at'];
}
