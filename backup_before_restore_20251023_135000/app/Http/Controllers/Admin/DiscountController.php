<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use App\Repository\Eloquent\DiscountRepository;
use App\Repository\Eloquent\PriceRepository;
use App\Services\Admin\DiscountServices;

class DiscountController extends Controller
{
    protected $DiscountServices;

    protected $DiscountRepository, $PriceRepository;
    public function __construct(DiscountRepository $DiscountRepository, PriceRepository $PriceRepository, DiscountServices $DiscountServices)
    {
        $this->DiscountRepository = $DiscountRepository;
        $this->PriceRepository = $PriceRepository;
        $this->DiscountServices = $DiscountServices;
    }
    public function index()
    {
        try {
            $data = $this->DiscountRepository->getAll();
            $products = $this->PriceRepository->getAll();
            return view('admin.discount', compact('data', 'products'));
        } catch (\Exception $e) {
            Log::error('Error in DiscountController/index :' . $e->getMessage() . 'in line' . $e->getLine());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }

    public function storeDiscount(Request $request)
    {

        try {
            return $this->DiscountServices->create($request);
        } catch (\Exception $e) {
            Log::error("Error in DiscountController.storeDiscount(): " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Something went wrong.']);
        }
    }
    public function getAll(Request $request)
    {
        try {
            if ($request->ajax()) {
                // Get discounts with related product info using Eloquent relationships or join
                $data = $this->DiscountRepository->getAll();
                // Note: implement getAllWithProduct() to join price_management table

                return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('product_name', function ($row) {
                        // Return product name if exists
                        return $row->priceManagement ? $row->priceManagement->name : '-';
                    })
                    ->addColumn('action', function ($row) {
                        return '<a href="javascript:void(0);" data-url="' . route('admin.getSingleDiscount', $row->id) . '"  class="btn btn-warning btn-sm edit-tile-btn">Edit</a> ' .
                            '<a href="javascript:void(0);" data-url="' . route('admin.deleteDiscount', $row->id) . '" class="btn btn-danger btn-sm delete-tile-btn">Delete</a> ';
                    })
                    ->editColumn('discount_percent', function ($row) {
                        return $row->discount_percent . '%'; // format with %
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }
            return redirect()->back()->withErrors(['error' => 'Invalid request.']);
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An unexpected error occurred.']);
        }
    }
    public function deleteDiscount($id)
    {
        try {
            // dd($id); // REMOVE THIS LINE WHEN DONE DEBUGGING
    
            $success = $this->DiscountRepository->delete(['id' => $id]);
    
            if ($success) {
                return response()->json([
                    'status' => 1,
                    'message' => __('message.statusThree', ['parameter' => __('message.Discount')])
                ]);
            } else {
                return response()->json(['status' => false], 500);
            }
        } catch (Exception $e) {
            Log::error("DiscountController : deleteDiscount() at line " . $e->getLine() . " - " . $e->getMessage());
            return redirect()->route('admin.dashboard')->withErrors([
                'error' => __('message.something_went_wrong')
            ]);
        }
    }
    
    public function getDiscount($id)
    {
        try {
            
            $success = $this->DiscountRepository->getOne(['id' => $id]);
            if ($success) {
                return response()->json(['status' => true, 'data' => $success, 'message' => __('message.statusFour', ['parameter' => __('message.Discount')])]);
            } else {
                return response()->json(['status' => false], 500);
            }
        } catch (Exception $e) {
            Log::error("DiscountController : getDiscount()" . $e->getLine() . " " . $e->getMessage());
            return redirect()->route('admin.dashboard')->withErrors(['error' => __('message.something_went_wrong')]);
        }
    }
    public function updateDiscount(Request $request)
    {
       
        try {
            return $this->DiscountServices->update($request);
        } catch (Exception $e) {
            Log::error("DiscountController : update()" . $e->getLine() . " " . $e->getMessage());
            return redirect()->route('admin.dashboard')->withErrors(['error' => __('message.something_went_wrong')]);
        }
    }

}
