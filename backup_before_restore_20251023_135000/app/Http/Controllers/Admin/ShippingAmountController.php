<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AddShippingAmountRequest;
use App\Services\Admin\ShippingAmountServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use Yajra\DataTables\DataTables;
use App\Repository\Admin\ShippingAmountRepository;

class ShippingAmountController extends Controller
{
    protected $shippingAmountServices;
    protected $shippingAmountRepository;

    public function __construct(ShippingAmountServices $shippingAmountServices,ShippingAmountRepository $shippingAmountRepository)
    {
        $this->shippingAmountServices = $shippingAmountServices;
        $this->shippingAmountRepository = $shippingAmountRepository;
    }

    public function countryShippingAmountList(){
        $countries = $this->shippingAmountRepository->getAll(['shipping_amount' => [ 'IS', null]]);
        $updatedCountries = $this->shippingAmountRepository->getAll();
        return view('admin.country-shipping-amount-list',compact(['countries','updatedCountries']));
    }

    public function getCountryShippingAmount(Request $request){
        try {
            if ($request->ajax()) {
                $data = $this->shippingAmountServices->getCountryShippingAmount();


                return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {

                    return '<a href="javascript:void(0);" data-url="'.route('admin.getSingleShippingAmount',$row->id).'"  data-toggle="modal" data-target="#updateShippingModal" class="btn btn-warning btn-sm edit-shipping-amount-btn">Edit</a> ' .
                    '<a href="javascript:void(0);" data-url="'.route('admin.deleteShippingAmount',$row->id).'" class="btn btn-danger btn-sm delete-shipping-amount-btn">Delete</a> ';

                })->make(true);

            }
            return redirect()->back()->withErrors(['error' => 'Invalid request.']);
        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An unexpected error occurred.']);
        }
    }

    public function getSingleShippingAmount($id){
        try {
            
            return $this->shippingAmountServices->getSingleShippingAmount($id);

        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An unexpected error occurred.']);
        }
    }


    public function addShippingAmount(AddShippingAmountRequest $request){
        try {

            return $this->shippingAmountServices->addShippingAmount($request);

        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An unexpected error occurred.']);
        }
    }

  

    public function deleteShippingAmount($id){
        try {

            return $this->shippingAmountServices->deleteShippingAmount($id);

        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An unexpected error occurred.']);
        }
    }
}
