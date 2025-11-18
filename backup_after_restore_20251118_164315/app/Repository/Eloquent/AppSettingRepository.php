<?php



namespace App\Repository\Eloquent;



use App\Models\AppSettingModel;

use Illuminate\Contracts\Cache\Repository as Cache;

use Illuminate\Support\Facades\Log;





class AppSettingRepository extends BaseRepository

{



    protected $model;

    protected $cache;



    public function __construct(AppSettingModel $model, Cache $cache)

    {

        $this->model = $model;

        // parent::__construct($model, $cache);

    }



    // public function create($data)

    // {

    //     try {

    //         return $this->model->create($data);

    //     } catch (\Exception $e) {

    //         Log::error("Error in AppSettingRepository.create(): " . $e->getMessage());

    //         throw $e;

    //         //return response()->json(['status' => '0', 'message' => __('message.statusZero')]);

    //     }

    // }



    public function getOne($byWhere)

    {

        try {

            return $this->model->where($byWhere)->value('value');

        } catch (\Exception $e) {

            Log::error("Error in AppSettingRepository.getOne(): " . $e->getMessage());

            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);

        }

    }

    public function getAll($orderBY = ['id' => 'desc'])

    {

        try {

            $orderByString = '';

            foreach ($orderBY as $column => $direction) {

                $orderByString .= "$column $direction, ";

            }

            $orderByString = rtrim($orderByString, ', ');



            return $this->model->orderByRaw($orderByString)->get();

        } catch (\Exception $e) {

            Log::error("Error in AppSettingRepository.getAll(): " . $e->getMessage());

            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);

        }

    }

    public function delete($byWhere)

    {

        try {

            return $this->model->where($byWhere)->delete();

        } catch (\Exception $e) {

            Log::error("Error in AppSettingRepository.delete(): " . $e->getMessage());

            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);

        }

    }

    public function update($byWhere, $Data)

    {

        try {

            return $this->model->where($byWhere)->update($Data);

        } catch (\Exception $e) {

            Log::error("Error in AppSettingRepository.update(): " . $e->getMessage());

            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);

        }

    }



    public function getByWhere(array $byWhere, $orderBy = ['id' => 'desc'])
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
            Log::error("Error in AppSettingRepository.getByWhere(): " . $e->getMessage());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }



}

