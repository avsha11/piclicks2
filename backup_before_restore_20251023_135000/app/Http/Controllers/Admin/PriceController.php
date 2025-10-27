<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use App\Repository\Eloquent\PriceRepository;
use App\Models\Countries;
class PriceController extends Controller
{
    protected $PriceRepository;
    public function __construct(PriceRepository $PriceRepository)
    {
        $this->PriceRepository = $PriceRepository;
    }
    public function index()
    {
        try {
            $data = $this->PriceRepository->getAll();
            // dd($data);
            $product = $this->PriceRepository->getAll();
            $countries = Countries::get();
            return view('admin.price', compact('data','countries','product'));
        } catch (\Exception $e) {
            Log::error('Error in PriceController/index :' . $e->getMessage() . 'in line' . $e->getLine());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }

    public function storePrice(Request $request)
    {
        
        try {
            foreach ($request->prices as $item) {
                $this->PriceRepository->update(
                    ['id' => $item['id']],
                    ['price' => $item['price']]
                );
            }
    
            return response()->json(['status' => 1, 'message' => 'Prices updated successfully.']);
        } catch (\Exception $e) {
            Log::error("Error in PriceController.storePrice(): " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Something went wrong.']);
        }
    }
    public function calculateFinalPrice(Request $request)
    {
        $product_id = $request->product_id;
        $tile_count = $request->tile_count;
        $country_id = $request->country_id;

        $finalPrice = getFinalPrice($product_id, $tile_count, $country_id);

        return response()->json(['final_price' => $finalPrice]);
    }

}
