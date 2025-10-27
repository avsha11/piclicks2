<?php
namespace App\Services\Admin;

use App\Repository\Admin\FrameRepository;
use Illuminate\Support\Facades\Log;
use Exception;


class FrameServices{
    protected $FrameRepository;

    public function __construct(FrameRepository $frameRepository)
    {
        $this->FrameRepository = $frameRepository;
    }

    public function getAllService(){
        try {
            $data = $this->FrameRepository->getAll();

            if($data){
                 return response()->json(['status' => 1, 'message' => 'Successed to fetch frames.','data' => $data]);
            }

        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to fetch products.']);
        }
    }

    public function getSingleService($id){
        try {

            $where = [
                'id' => $id,
            ];

            $data = $this->FrameRepository->getSingle($where);

            if($data){
                 return response()->json(['status' => 1, 'message' => 'Successed to fetch single frames.','data' => $data]);
            }

        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to fetch products.']);
        }
    }

    public function updateService($request){
        try {

            $where = [
                'id' => $request->frameId,
            ];

            $update = [
                'name' => $request->frameName,
                'cost' => $request->frameCost,
                'status' => $request->frameStatus
            ];

            $data = $this->FrameRepository->update($where,$update);

            if($data){
                 return response()->json(['status' => 1, 'message' => 'Successfully update frame.','data' => $data]);
            }

        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to fetch products.']);
        }
    }
}
