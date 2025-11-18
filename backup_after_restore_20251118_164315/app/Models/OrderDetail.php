<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;



class OrderDetail extends Model

{

    use HasFactory;

    protected $table = 'order_detail';

    protected $guarded = [];



    public function order_data()

    {
        return $this->belongsTo(Order::class, 'master_id');
    }

    public function design_collage_master()
    {
        return $this->hasOne(DesignCollageMaster::class, 'unique_id', 'collage_unique_id');
    }

    public function design_collage()
    {
        return $this->hasMany(DesignCollageModel::class, 'unique_id', 'collage_unique_id');
    }

    public function giftcard_data()
    {
        return $this->hasOne(Giftcard::class, 'id', 'giftcard_id');
    }
  
  


}

