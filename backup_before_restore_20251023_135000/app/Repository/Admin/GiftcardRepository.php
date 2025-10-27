<?php

namespace App\Repository\Admin;



use App\Models\Giftcard;



use Illuminate\Contracts\Cache\Repository as Cache;

use Illuminate\Support\Facades\Log;

use DB;



class GiftcardRepository

{



    protected $model;

    protected $cache;

    protected $contactmodel;

    protected $NotificationModel;



    public function __construct(

        Giftcard $model,

    ) {

        $this->model = $model;

    }



    //its a create function used insert data 



    public function create($allData)

    {

        try {

            return $this->model->create($allData);

        } catch (\Exception $e) {

            Log::error("Error in GiftcardRepository.create(): " . $e->getMessage());

            throw $e;

            // return response()->json(['status' => '0', 'message' => __('message.statusZero')]);

        }

    }



    public function update($byWhere, $update)

    {

        try {

            return $this->model->where($byWhere)->update($update);

        } catch (\Exception $e) {

            Log::error("Error in GiftcardRepository.create(): " . $e->getMessage());

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

            Log::error("Error in GiftcardRepository.getUser(): " . $e->getMessage());

            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);

        }

    }





    public function getAll($orderBy = ['id', 'desc'])

    {

        try {

            $res = $this->model->orderBy($orderBy[0], $orderBy[1])->get();
            return $res;

        } catch (\Exception $e) {

            Log::error("Error in GiftcardRepository.getAll(): " . $e->getMessage());

            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);

        }

    }



    public function delete($byWhere)

    {

        try {

            if(empty($byWhere)) {

                return 0;

            }
            $model = $this->model->where($byWhere)->first();
            if ($model) {
                return $model->delete(); // This will perform a soft delete
            }
            return false;

        } catch (\Exception $e) {

            Log::error("Error in GiftcardRepository.deleteData(): " . $e->getMessage());

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

            Log::error("Error in GiftcardRepository.getUsersByWhere(): " . $e->getMessage());

            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);

        }

    }



}