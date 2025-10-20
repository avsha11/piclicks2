<?php
namespace App\Services\Admin;

use App\Repository\Admin\ShippingAmountRepository;
use Illuminate\Support\Facades\Log;
use Exception;


class ShippingAmountServices{
    protected $shippingAmountRepository;

    public function __construct(ShippingAmountRepository $shippingAmountRepository)
    {
        $this->shippingAmountRepository = $shippingAmountRepository;
    }

    public function getCountryShippingAmount(){
        try {
           return $this->shippingAmountRepository->getAll(['shipping_amount' => [ 'IS NOT', null]]);
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to fetch products.']);
        }
    }

    public function getSingleShippingAmount($id){
        try {

            $where = [
                'id' => $id,
            ];

            $data = $this->shippingAmountRepository->getSingle($where);

            if($data){
                 return response()->json(['status' => 1, 'message' => 'Successed to fetch single frames.','data' => $data]);
            }

        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to fetch products.']);
        }
    }


    public function addShippingAmount($request){
        try {

            $where = [
                'id' => $request->country,
            ];

            $update = [
                'shipping_amount' => $request->cost,
                'vat' => $request->vat,
            ];

            $data = $this->shippingAmountRepository->update($where,$update);

            if($data){
                return response()->json(['status' => 1, 'message' => 'Successfully update shipping amount.','data' => $data]);
            }

        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to fetch products.']);
        }
    }

    public function deleteShippingAmount($id){
        try {

            $where = [
                'id' =>  $id,
            ];

            $update = [
                'shipping_amount' => null,
                'vat' =>0,
            ];

            $data = $this->shippingAmountRepository->update($where,$update);


            if($data){
                return response()->json(['status' => 1, 'message' => 'Successfully update shipping amount.','data' => $data]);
            }

        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to fetch products.']);
        }
    }
}
