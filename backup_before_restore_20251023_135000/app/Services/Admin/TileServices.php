<?php
namespace App\Services\Admin;

use App\Repository\Admin\TileRepository;
use Illuminate\Support\Facades\Log;
use Exception;


class TileServices{
    protected $TileRepository;

    public function __construct(TileRepository $tileRepository)
    {
        $this->TileRepository = $tileRepository;
    }

    public function getAllService(){
        try {

            return $this->TileRepository->getAll();

            // if($data){
            //      return response()->json(['status' => 1, 'message' => 'Successed to fetch frames.','data' => $data]);
            // }

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

            $data = $this->TileRepository->getSingle($where);

            if($data){
                 return response()->json(['status' => 1, 'message' => 'Successed to fetch single frames.','data' => $data]);
            }

        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to fetch products.']);
        }
    }

    public function createService($request){
        try {

            $create = [
                'tileNumber' => $request->tileNumber,
                'cost' => $request->cost,
            ];

            $data = $this->TileRepository->create($create);

            if($data){
                 return response()->json(['status' => 1, 'message' => 'Successed to create tile.','data' => $data]);
            }

        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to create tile.']);
        }
    }

    public function updateService($request){
        try {

            $where = [
                'id' => $request->tileId,
            ];

            $update = [
                'tileNumber' => $request->tileNumber,
                'cost' => $request->tileCost,
            ];

            $data = $this->TileRepository->update($where,$update);

            if($data){
                 return response()->json(['status' => 1, 'message' => 'Successfully update tile.','data' => $data]);
            }

        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to fetch products.']);
        }
    }

    public function deleteService($id){
        try {

            $where = [
                'id' => $id,
            ];

            $data = $this->TileRepository->delete($where);

            if($data){
                 return response()->json(['status' => 1, 'message' => 'Successfully update tile.','data' => $data]);
            }

        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to fetch products.']);
        }
    }
}
