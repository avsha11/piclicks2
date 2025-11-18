<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Http\Requests\Admin\CouponRequest;
use App\Services\Admin\CouponServices;
use App\Repository\Admin\CouponRepository;

class CouponController extends Controller
{
    protected $CouponRepository;
    protected $CouponServices;

    public function __construct(CouponServices $CouponServices,CouponRepository $CouponRepository)
    {
        $this->CouponServices = $CouponServices;
        $this->CouponRepository = $CouponRepository;
    }
    
    // coupon list page
    public function index(Request $request)
    {
        try{
            return view('admin.coupon-list');
        }catch(Exception $e){
            Log::error('Error in CouponController@index: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing your request.'], 500);
        }
    }

    // coupon list
    public function getCoupons(Request $request)
    {
        try{
            return $this->CouponServices->getCoupons();
        }catch(Exception $e){
            Log::error('Error in CouponController@getCoupons: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing your request.'], 500);
        }
    }

    // add coupons
    public function addCoupon(Request $request)
    {
        try{
            return view('admin.add-coupon');
        }catch(Exception $e){
            Log::error('Error in CouponController@addCoupon: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing your request.'], 500);
        }
    }

     // store coupons
    public function storeCoupon(CouponRequest $request)
    {
        try{
         return $this->CouponServices->storeCoupon($request);
        }catch(Exception $e){
            Log::error('Error in CouponController@storeCoupon: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing your request.'], 500);
        }
    }

    // edit coupons
    public function editCoupons(Request $request)
    {
        try{
            $couponsData = $this->CouponRepository->getSingle(['id'=>$request->id]);
            return view('admin.edit-coupon',compact('couponsData'));
        }catch(Exception $e){
            Log::error('Error in CouponController@editCoupons: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing your request.'], 500);
        }
    }

    // update coupons
    public function updateCoupon(Request $request)
    {
        try{
            return $this->CouponServices->updateCoupon($request);
        }catch(Exception $e){
            Log::error('Error in CouponController@updateCoupon: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing your request.'], 500);
        }
    }

     // delete coupons
    public function deleteCoupon(Request $request)
    {
        try{

            $run = $this->CouponRepository->delete(['id'=>$request->id]);
            if($run){
                return response()->json([
                    'status' => 1,
                    'message' => 'The coupon has been deleted successfully.',
                ]);
            }else{
                return response()->json([
                    'status' => 0,
                    'message' => 'Failed to delete the coupon.'
                ]);
            }
        }catch(Exception $e){
            Log::error('Error in CouponController@deleteCoupon: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing your request.'], 500);
        }
    }

     // inactive past coupons
     public function inactivePastCoupons(Request $request)
     {
        try{
            return $this->CouponServices->inactivePastCoupons($request);
        }catch(Exception $e){
            Log::error('Error in CouponController@inactivePastCoupons: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing your request.'], 500);
        }
     }
   

}
