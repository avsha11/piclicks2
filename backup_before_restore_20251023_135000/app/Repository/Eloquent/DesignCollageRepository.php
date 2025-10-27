<?php

namespace App\Repository\Eloquent;

use App\Models\{DesignCollageModel, DesignCollageMaster, DesignCollageAdmin};
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Facades\Log;
use DB;

class DesignCollageRepository extends BaseRepository
{

    protected $model, $master, $designCollageAdminmodel;
    protected $cache;
    protected $contactmodel;
    protected $NotificationModel;

    public function __construct(
        DesignCollageModel $model,
        DesignCollageAdmin $designCollageAdminmodel,
        DesignCollageMaster $master,
        Cache $cache,
    ) {
        $this->model = $model;
        $this->master = $master;
        $this->designCollageAdminmodel = $designCollageAdminmodel;
        parent::__construct($model, $cache);
    }

    //its a create function used insert data 

    public function create($allData)
    {
        try {
            return $this->model->create($allData);
        } catch (\Exception $e) {
            Log::error("Error in DesignCollageRepository.create(): " . $e->getMessage());
            throw $e;
            // return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }

    public function update($byWhere, $update)
    {
        try {
            return $this->model->where($byWhere)->update($update);
        } catch (\Exception $e) {
            Log::error("Error in DesignCollageRepository.create(): " . $e->getMessage());
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
            Log::error("Error in DesignCollageRepository.getUser(): " . $e->getMessage());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }


    public function getAll()
    {
        try {
            return $this->model->with('game')->orderBy('id', 'desc')->get();
        } catch (\Exception $e) {
            Log::error("Error in DesignCollageRepository.userList(): " . $e->getMessage());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }

    public function delete($byWhere)
    {
        try {
            if (empty($byWhere)) {
                return 0;
            }
            return $this->model->where($byWhere)->delete();
        } catch (\Exception $e) {
            Log::error("Error in DesignCollageRepository.deleteData(): " . $e->getMessage());
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
            Log::error("Error in DesignCollageRepository.getUsersByWhere(): " . $e->getMessage());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }






    /////////////// design_collage_mster functions //////////////////////

    public function createMaster($allData)
    {
        try {
            return $this->master->create($allData);
        } catch (\Exception $e) {
            Log::error("Error in DesignCollageRepository.createMaster(): " . $e->getMessage());
            throw $e;
            // return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }

    public function updateMaster($byWhere, $update)
    {
        try {
            return $this->master->where($byWhere)->update($update);
        } catch (\Exception $e) {
            Log::error("Error in DesignCollageRepository.updateMaster(): " . $e->getMessage());
            throw $e;
            // return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }

    public function getOneMaster($byWhere)
    {
        try {
            $data = $this->master->where($byWhere)->first();
            return $data;
        } catch (\Exception $e) {
            Log::error("Error in DesignCollageRepository.getOneMaster(): " . $e->getMessage());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }


    public function getAllMaster()
    {
        try {
            return $this->master->orderBy('id', 'desc')->get();
        } catch (\Exception $e) {
            Log::error("Error in DesignCollageRepository.getAllMaster(): " . $e->getMessage());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }

    public function deleteMaster($byWhere)
    {
        try {
            return $this->master->where($byWhere)->delete();
        } catch (\Exception $e) {
            Log::error("Error in DesignCollageRepository.deleteMaster(): " . $e->getMessage());
            throw $e;
            // return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }


    public function getByWhereMaster($byWhere, $orderBy = ['id' => 'desc'])
    {
        try {
            $query = $this->master->where(function ($query) use ($byWhere) {

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

            $userId = auth()->id();
            $guestId = getUserId();

            return $query->with(['favorites_data' => function ($query) use ($userId, $guestId) {
                $query->when($userId, fn($q) => $q->where('user_id', $userId))
                    ->when(!$userId, fn($q) => $q->where('guest_id', $guestId));
            }])
                ->orderByRaw($orderByString)->get();
        } catch (\Exception $e) {
            Log::error("Error in DesignCollageRepository.getByWhereMaster(): " . $e->getMessage());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }

    public function createdesignCollageAdmin($allData)
    {
        try {
            return $this->designCollageAdminmodel->create($allData);
        } catch (\Exception $e) {
            Log::error("Error in DesignCollageRepository.createdesignCollageAdmin(): " . $e->getMessage());
            throw $e;
            // return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }

    public function  getOneCollageAdmin($byWhere)
    {
        try {
            return $this->designCollageAdminmodel->where($byWhere)->first();
        } catch (\Exception $e) {
            Log::error("Error in DesignCollageRepository.getOneCollageAdmin(): " . $e->getMessage());
            throw $e;
            // return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }
    public function updateCollageAdmin($byWhere, $update)
    {
        try {
            return $this->designCollageAdminmodel->where($byWhere)->update($update);
        } catch (\Exception $e) {
            Log::error("Error in DesignCollageRepository.updateCollageAdmin(): " . $e->getMessage());
            throw $e;
            // return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }
}
