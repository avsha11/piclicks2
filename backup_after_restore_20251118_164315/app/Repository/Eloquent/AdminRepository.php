<?php

namespace App\Repository\Eloquent;

use App\Models\ContactUs;
use App\Models\Admin;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Facades\Log;
use DB;

class AdminRepository extends BaseRepository
{
    protected $model,$adminmodel;
    protected $cache;
    public function __construct(
        ContactUs $model,
        Admin $adminmodel,
        Cache $cache
    ) {
        $this->model = $model;
        $this->adminmodel = $adminmodel;
        parent::__construct($model, $cache);
    }

    public function getContact()
    {
        try {
            return $this->model->with('user')->orderBy('id', 'desc')->get();
        } catch (\Exception $e) {
            Log::error("Error in AdminRepository.getContact(): " . $e->getMessage());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }

    public function getSingleContact($byWhere)
    {
        try {
            return $this->model->where($byWhere)->first();
        } catch (\Exception $e) {
            Log::error("Error in AdminRepository.getSingleContact(): " . $e->getMessage());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }


    public function create($allData)

    {

        try {
            // dd($allData);
            return $this->adminmodel->create($allData);
        } catch (\Exception $e) {

            Log::error("Error in AdminRepository.createRound(): " . $e->getMessage());

            throw $e;

            // return response()->json(['status' => '0', 'message' => __('message.statusZero')]);

        }
    }

    public function update($byWhere, $update)

    {

        try {

            return $this->adminmodel->where($byWhere)->update($update);
        } catch (\Exception $e) {

            Log::error("Error in AdminRepository.create(): " . $e->getMessage());

            throw $e;

            // return response()->json(['status' => '0', 'message' => __('message.statusZero')]);

        }
    }



    public function getOne($byWhere)

    {

        try {

            $data = $this->adminmodel->select('*')->where($byWhere)->first();

            return $data;
        } catch (\Exception $e) {

            Log::error("Error in AdminRepository.getUser(): " . $e->getMessage());

            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }





    public function getAll()

    {

        try {

            return $this->adminmodel->orderBy('id', 'desc')->get();
        } catch (\Exception $e) {

            Log::error("Error in AdminRepository.userList(): " . $e->getMessage());

            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }



    public function delete($byWhere)

    {

        try {

            $model = $this->adminmodel->where($byWhere)->first();
            if ($model) {
                return $model->delete(); // This will perform a soft delete
            }
            return false;
            // return $this->model->where($byWhere)->delete();

        } catch (\Exception $e) {

            Log::error("Error in AdminRepository.deleteData(): " . $e->getMessage());

            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }





    public function getByWhere($byWhere, $orderBy = ['id' => 'desc'])

    {

       try {

            $query = $this->adminmodel->where(function ($query) use ($byWhere) {



                foreach ($byWhere as $column => $condition) {

                    if (is_array($condition)) {

                        

                        if ($condition[0] === "IN") {

                            unset($condition[0]);

                            $query->whereIn($column, $condition);

                        } else {

                            $query->where($column, $condition[0], $condition[1]);

                        }

                    } else {

                        $query->where($column, $condition);

                    }

                }

            });





            // Construct the order by string

            $orderByString = '';

            foreach ($orderBy as $column => $direction) {

                $orderByString .= "$column $direction, ";

            }

            $orderByString = rtrim($orderByString, ', ');



            return $query->orderByRaw($orderByString)->get();

        } catch (\Exception $e) {

            Log::error("Error in AdminRepository.getUsersByWhere(): " . $e->getMessage());

            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);

        }

    }
}
