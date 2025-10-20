<?php

namespace App\Repository\Admin;


use App\Models\Frame;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;



class FrameRepository implements FrameRepositoryInterface
{
    protected $model;
    protected $auth;


    public function __construct(Frame $frame, Auth $auth)
    {
        $this->model = $frame;
        $this->auth = $auth::guard('admins');
    }

    public function getAll(){
        try {

            return $this->model->get();

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

}
