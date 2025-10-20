<?php

namespace App\Repository\Admin;



use App\Models\Tag;



use Illuminate\Contracts\Cache\Repository as Cache;

use Illuminate\Support\Facades\Log;

use DB;



class TagRepository

{



    protected $model;

    protected $cache;

    protected $contactmodel;

    protected $NotificationModel;



    public function __construct(

        Tag $model,

    ) {

        $this->model = $model;

    }



    //its a create function used insert data 



    public function create($allData)

    {

        try {

            return $this->model->create($allData);

        } catch (\Exception $e) {

            Log::error("Error in TagRepository.create(): " . $e->getMessage());

            throw $e;

            // return response()->json(['status' => '0', 'message' => __('message.statusZero')]);

        }

    }



    public function update($byWhere, $update)

    {

        try {

            return $this->model->where($byWhere)->update($update);

        } catch (\Exception $e) {

            Log::error("Error in TagRepository.create(): " . $e->getMessage());

            throw $e;

            // return response()->json(['status' => '0', 'message' => __('message.statusZero')]);

        }

    }



    public function getOne($byWhere)

    {

        try {

            $data = $this->model->select('*')->where($byWhere)->first();

            return $data;

        } catch (\Exception $e) {

            Log::error("Error in TagRepository.getUser(): " . $e->getMessage());

            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);

        }

    }





    public function getAll()

    {

        try {

            return $this->model->orderBy('name', 'asc')->get();

        } catch (\Exception $e) {

            Log::error("Error in TagRepository.userList(): " . $e->getMessage());

            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);

        }

    }



    public function delete($byWhere)

    {

        try {

            if(empty($byWhere)) {

                return 0;

            }
            return $this->model->where($byWhere)->delete();

        } catch (\Exception $e) {

            Log::error("Error in TagRepository.deleteData(): " . $e->getMessage());

            throw $e;

            // return response()->json(['status' => '0', 'message' => __('message.statusZero')]);

        }

    }





    public function getByWhere($byWhere, $orderBy = ['id' => 'asc'])

    {

        try {

            $query = $this->model->where(function ($query) use ($byWhere) {



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

            Log::error("Error in TagRepository.getUsersByWhere(): " . $e->getMessage());

            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);

        }

    }



}