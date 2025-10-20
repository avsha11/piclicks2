<?php
namespace App\Repository\Eloquent;
use App\Models\Order;
use App\Models\{OrderDetail,OrderGiftcard};
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Facades\Log;
use DB;
class OrderRepository
{
    protected $model, $detail, $giftcard;
    protected $cache;
    protected $contactmodel;
    protected $NotificationModel;
    public function __construct(
        Order $model,
        OrderDetail $detail,
        OrderGiftcard $giftcard,
    ) {
        $this->model = $model;
        $this->detail = $detail;
        $this->giftcard = $giftcard;
    }
    //its a create function used insert data 
    public function create($allData)
    {
        try {
            return $this->model->create($allData);
        } catch (\Exception $e) {
            Log::error("Error in OrderRepository.create(): " . $e->getMessage());
            throw $e;
            // return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }
    public function update($byWhere, $update)
    {
        try {
            return $this->model->where($byWhere)->update($update);
        } catch (\Exception $e) {
            Log::error("Error in OrderRepository.update(): " . $e->getMessage());
            throw $e;
            // return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }
    public function getOne($byWhere)
    {
        try {
            $data = $this->model->select('*')->with('orderdetail_data')->where($byWhere)->first();
            return $data;
        } catch (\Exception $e) {
            // dd($e->getMessage());
            Log::error("Error in OrderRepository.getUser(): " . $e->getMessage());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }
    public function getAll()
    {
        try {
            return $this->model->orderBy('id', 'desc')->with(['orderdetail_data','shipping_country_data'])->get();
        } catch (\Exception $e) {
            Log::error("Error in OrderRepository.userList(): " . $e->getMessage());
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
            Log::error("Error in OrderRepository.deleteData(): " . $e->getMessage());
            throw $e;
            // return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }
    public function getByWhere($byWhere, $orderBy = ['id' => 'asc'])
    {
        try {
            $query = $this->model->with('orderdetail_data')->where(function ($query) use ($byWhere) {
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
            Log::error("Error in OrderRepository.getUsersByWhere(): " . $e->getMessage());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }













    /////////////// order_detail functions //////////////////////
    public function createDetail($allData)
    {
        try {
            return $this->detail->create($allData);
        } catch (\Exception $e) {
            Log::error("Error in OrderRepository.createDetail(): " . $e->getMessage());
            throw $e;
            // return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }
    public function updateDetail($byWhere, $update)
    {
        try {
            return $this->detail->where($byWhere)->update($update);
        } catch (\Exception $e) {
            Log::error("Error in OrderRepository.updateDetail(): " . $e->getMessage());
            throw $e;
            // return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }
    public function getOneDetail($byWhere)
    {
        try {
            $data = $this->detail->where($byWhere)->first();
            return $data;
        } catch (\Exception $e) {
            Log::error("Error in OrderRepository.getOneDetail(): " . $e->getMessage());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }
    public function getAllDetail()
    {
        try {
            return $this->detail->orderBy('id', 'desc')->get();
        } catch (\Exception $e) {
            Log::error("Error in OrderRepository.getAllDetail(): " . $e->getMessage());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }
    public function deleteDetail($byWhere)
    {
        try {
            return $this->detail->where($byWhere)->delete();
        } catch (\Exception $e) {
            Log::error("Error in OrderRepository.deleteDetail(): " . $e->getMessage());
            throw $e;
            // return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }
    public function getByWhereDetail($byWhere, $orderBy = ['id' => 'desc'])
    {
        try {
            $query = $this->detail->where(function ($query) use ($byWhere) {
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
            Log::error("Error in OrderRepository.getByWhereDetail(): " . $e->getMessage());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }
    // public function getOrdersByUserId($userId)
    // {
    //     return Order::where('user_id', $userId)->get();
    // }










    
    /////////////// order_giftcard functions //////////////////////
    public function createGiftcard($allData)
    {
        try {
            return $this->giftcard->create($allData);
        } catch (\Exception $e) {
            Log::error("Error in OrderRepository.createGiftcard(): " . $e->getMessage());
            throw $e;
            // return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }
    public function updateGiftcard($byWhere, $update)
    {
        try {
            return $this->giftcard->where($byWhere)->update($update);
        } catch (\Exception $e) {
            Log::error("Error in OrderRepository.updateGiftcard(): " . $e->getMessage());
            throw $e;
            // return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }
    public function getOneGiftcard($byWhere)
    {
        try {
            $data = $this->giftcard->select('*')->with('order_data')->where($byWhere)->first();
            return $data;
        } catch (\Exception $e) {
            Log::error("Error in OrderRepository.getOneGiftcard(): " . $e->getMessage());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }
    public function getAllGiftcard()
    {
        try {
            return $this->giftcard->orderBy('id', 'desc')->with('order_data','order_giftcard_used','giftcard_data','redeem_by_data')->get();
        } catch (\Exception $e) {
            Log::error("Error in OrderRepository.getAllGiftcard(): " . $e->getMessage());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }
    public function deleteGiftcard($byWhere)
    {
        try {
            if(empty($byWhere)) {
                return 0;
            }
            return $this->giftcard->where($byWhere)->delete();
        } catch (\Exception $e) {
            Log::error("Error in OrderRepository.deleteGiftcard(): " . $e->getMessage());
            throw $e;
            // return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }
    public function getByWhereGiftcard($byWhere, $orderBy = ['id' => 'asc'])
    {
        try {
            $query = $this->giftcard->with(['order_data', 'order_data.user_data', 'order_giftcard_used', 'giftcard_data'])->where(function ($query) use ($byWhere) {
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
            Log::error("Error in OrderRepository.getByWhereGiftcard(): " . $e->getMessage());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }



}
