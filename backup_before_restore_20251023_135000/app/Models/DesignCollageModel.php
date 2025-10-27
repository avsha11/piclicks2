<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;



class DesignCollageModel extends Model

{

    use HasFactory;

    protected $table = "design_collage";

    protected $guarded = [];



    public function designCollage()

    {

        return $this->belongsTo(DesignCollageModel::class, 'product_id', 'unique_id');
    }

    protected $casts = [
        'empty' => 'integer',
        'seq' => 'integer',
        'is_deleted' => 'integer',
    ];
}
