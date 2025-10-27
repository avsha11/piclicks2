<?php

namespace App\Repository\Admin;
use App\Models\Coupon;
use Exception;
use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\Auth;



class CouponRepository implements CouponRepositoryInterface
{
    protected $model;
    protected $auth;


    public function __construct(Coupon $Coupon)
    {
        $this->model = $Coupon;
        // $this->auth = $auth::guard('admins');
    }

    public function getAll(){
        try {

            return $this->model->orderBY('id','DESC')->get();

        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to fetch all data from repository.']);
        }
    }

    public function getSingle($where){
        try {

            return $this->model->where($where)->first();

        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to fetch single data from repository.']);
        }
    }

    public function update($where,$update){
        try {

            return $this->model->where($where)->update($update);

        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to update data from repository.']);
        }
    }

    public function create($data){
        try {

          return  $this->model->create($data);

        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to update data from repository.']);
        }
    }

    public function delete($where){
        try {

            return $this->model->where($where)->delete();

        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to update data from repository.']);
        }
    }

}
