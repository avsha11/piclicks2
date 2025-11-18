<?php

namespace App\Repository\Admin;
use App\Models\Countries;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class ShippingAmountRepository implements ShippingAmountRepositoryInterface
{
    protected $model;
    protected $auth;


    public function __construct(Countries $tile, Auth $auth)
    {
        $this->model = $tile;
        $this->auth = $auth::guard('admins');
    }

    public function getAll($where = null)
    {
        try {
            $query = $this->model;
        
            if ($where !== null) {
                foreach ($where as $key => $value) {
                    if (is_array($value) && count($value) === 2) {
                        if (strtoupper($value[0]) === 'IS' || strtoupper($value[0]) === 'IS NOT') {
                            // Handle IS NULL / IS NOT NULL
                            $query = $query->whereRaw("$key {$value[0]} NULL");
                        } else {
                            // Handle normal where conditions
                            $query = $query->where($key, $value[0], $value[1]);
                        }
                    } else {
                        // Default where condition
                        $query = $query->where($key, $value);
                    }
                }
            }
        
            return $query->get();
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to fetch single data from repository.']);
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

    public function create($create){
        try {

            return $this->model->create($create);

        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to tile create from repository.']);
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

    public function delete($where){
        try {

            return $this->model->where($where)->delete();

          } catch (Exception $e) {
              Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
              return response()->json(['status' => 0, 'message' => 'Failed to delete data from repository.']);
          }
    }

}
