<?php

namespace App\Services\FrontEnd;

use App\Repository\Eloquent\OrderRepository;
use Carbon\Carbon;

class OrderService
{
    protected $orderRepo;

    public function __construct(OrderRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }

    // public function getUserOrders($userId)
    // {
    //     return $this->orderRepo->getByWhere(['user_id' => $userId]);
    // }

    public function orderDetail($request)
    {
        $order =  $this->orderRepo->getOne(['id' => $request['orderId']]);
        $view = view('front.component.order-detail-component', ['order' => $order])->render();

        return response()->json([
            'status' => 1,
            'data' => $view,
        ]);
    }



    function updateOrderStatus($request)
    {
        try {

            $order = $this->orderRepo->getOne(['id' => $request->order_id]);
            if (!$order) {
                return response()->json(['status' => 0, 'message' => __('message.statusFour', ['parameter' => 'Order'])]);
            }

            $order_trackingarr = json_decode($order->order_tracking);
            $order_trackingarr[] = [
                'status' => $request->order_status,
                'date' => Carbon::now()->format('d-m-Y H:i:s'),
            ];
            $order_tracking = json_encode($order_trackingarr);


            $run = $this->orderRepo->update(['id' => $request->order_id], [
                'order_status' => $request->order_status,
                'delivery_date' => $request->delivery_date,
                'order_tracking' => $order_tracking
            ]);

            if ($run) {
                return response()->json(['status' => 1, 'message' => __('message.statusTwo', ['parameter' => 'Order'])]);
            } else {
                return response()->json(['status' => 0, 'message' => __('message.statusZero')]);
            }
        } catch (Exception $e) {
            Log::error('Error in OrderController/updateOrderStatus :' . $e->getMessage() . 'in line ' . $e->getLine());
            return response()->json(['status' => 0, 'message' => __('message.statusZero') . "in catch" . '' . $e->getMessage() . 'in line ' . $e->getLine()]);
        }
    }

}
