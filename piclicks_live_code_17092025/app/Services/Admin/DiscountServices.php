<?php

namespace App\Services\Admin;

use App\Repository\Eloquent\DiscountRepository;
use Illuminate\Support\Facades\Log;
use Exception;

class DiscountServices
{
    protected $DiscountRepository;
    private $dataObject;
    public function __construct(DiscountRepository $DiscountRepository)
    {
        $this->DiscountRepository = $DiscountRepository;
        $this->dataObject = new \stdClass();
    }

    public function create($request){
       
        try{
            $formdata = [
                'price_management_id' => $request->price_management_id,
                'description'         => $request->description,
                'discount_percent'    => $request->discount_percent,
                'tile_from'           => $request->tile_from,
                'tile_to'             => $request->tile_to,
            ];
       
           $data =  $this->DiscountRepository->create($formdata);
            if($data){
                return response()->json(['message' => __('message.statusOne', ['parameter' => __('message.Discount')]),'status' => 1, 'error' => $this->dataObject], 201);
            }

        }catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to update user status.']);
        }
     
    }
    public function update($request){
       
        try{
            $formdata = [
                'price_management_id' => $request->priceManagementId,
                'description'         => $request->description,
                'discount_percent'    => $request->discountPercent,
                'tile_from'           => $request->tileFrom,
                'tile_to'             => $request->tileFrom,
            ];
            // dd($formdata);
           $data =  $this->DiscountRepository->update(['id'=>$request->discountId],$formdata);
            if($data){
                return response()->json(['message' => __('message.statusTwo', ['parameter' => __('message.Discount')]),'status' => 1, 'error' => $this->dataObject], 201);
            }

        }catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to update user status.']);
        }
     
    }
    
}
