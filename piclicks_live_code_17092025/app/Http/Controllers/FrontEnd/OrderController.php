<?php

namespace App\Http\Controllers\FrontEnd;

use App\Services\FrontEnd\OrderService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\DesignCollageMaster;
use App\Services\CollageServices;
use App\Repository\Eloquent\{DesignCollageRepository, OrderRepository};
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected $orderService, $CollageServices, $DesignCollageRepository, $OrderRepository;

    public function __construct(OrderService $orderService, CollageServices $CollageServices, DesignCollageRepository $DesignCollageRepository, OrderRepository $OrderRepository)
    {
        $this->orderService = $orderService;
        $this->CollageServices = $CollageServices;
        $this->DesignCollageRepository = $DesignCollageRepository;
        $this->OrderRepository = $OrderRepository;
    }

    public function index(Request $request)
    {
        try {
            $orders = $this->OrderRepository->getByWhere(['user_id' => auth()->id()], ['created_at' => 'desc']);
            
            return view('front.orders.index', compact('orders'));
        } catch (\Exception $e) {
            Log::error('Error in OrderController/index :' . $e->getMessage() . 'in line' . $e->getLine());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }


    public function currentDraft()
    {
        $byWhere = ['user_id' => auth()->id(), 'status' => 0];
        $orderby = $orderBy = ['id' => 'desc'];
        $orders =  $this->DesignCollageRepository->getByWhereMaster($byWhere, $orderby);
        if (!$orders->isEmpty()) {
            foreach ($orders as $order) {
                $where = ['unique_id' => $order['unique_id'], 'seq' => 2];
                $orderby2 = ['id' => 'asc'];
                $collageImages = $this->DesignCollageRepository->getByWhere($where, $orderby2);
                $order['collage_images'] = $collageImages;
            }
        }
        // dd($orders);
        $title = 'My Draft';
        return view('front.orders.current-draft', compact('orders', 'title'));
    }

    public function orderDetail(Request $request)
    {
        return $this->orderService->orderDetail($request->all());
    }


    public function reorder($unique_id)
    {
        try {
            // dd($unique_id);
            $master = DB::table('design_collage_master')->where('unique_id', $unique_id)->first();
            if (!$master) {
                return redirect()->back()->with('reorder_error', 'Original collage not found.');
            }

            [$returnstatus, $message, $new_unique_id] = $this->CollageServices->artGalleryEdit($unique_id, $master, 0);

            if ($returnstatus === 1) {
                if (!empty($master->artgallery_unique_id)) {
                    $artgallery_unique_id = $master->artgallery_unique_id;
                    DesignCollageMaster::where('unique_id', $new_unique_id)->update(['artgallery_unique_id' => $artgallery_unique_id]);
                }

                return redirect()->route('front.design-collage', ['unique_id' => $new_unique_id]);
            } else {

                return redirect()->back()->with('reorder_error', $message);
            }
        } catch (\Exception $e) {
            Log::error('Error in OrderController/reorder :' . $e->getMessage() . 'in line' . $e->getLine());
            return redirect()->back()->with('reorder_error', 'Error reordering collage, try again.');
        }
    }
}
