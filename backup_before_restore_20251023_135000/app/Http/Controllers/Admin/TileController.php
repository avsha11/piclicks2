<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\{TileRequest, UpdateTileRequest};
use App\Services\Admin\TileServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use Yajra\DataTables\DataTables;

class TileController extends Controller
{
    protected $TileServices;

    public function __construct(TileServices $tileServices)
    {
        $this->TileServices = $tileServices;
    }

    public function tilesPage(){
        return view('admin.tilespage');
    }

    public function getAll(Request $request){
        try {
            if ($request->ajax()) {
                $data = $this->TileServices->getAllService();


                return DataTables::of($data)
                    ->addColumn('action', function ($row) {


                        return '<a href="javascript:void(0);" data-url="'.route('admin.getSingleTile',$row->id).'"  data-toggle="modal" data-target="#tileUpdateModal" class="btn btn-warning btn-sm edit-tile-btn">Edit</a> ' .
                        '<a href="javascript:void(0);" data-url="'.route('admin.deleteTile',$row->id).'" class="btn btn-danger btn-sm delete-tile-btn">Delete</a> ';

                    })->make(true);

            }
            return redirect()->back()->withErrors(['error' => 'Invalid request.']);
        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An unexpected error occurred.']);
        }
    }

    public function getSingle($id){
        try {

            return $this->TileServices->getSingleService($id);

        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An unexpected error occurred.']);
        }
    }


    public function submitTile(TileRequest $request){
        try {

            return $this->TileServices->createService($request);

        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An unexpected error occurred.']);
        }
    }

    public function updateTile(UpdateTileRequest $request){
        try {

            return $this->TileServices->updateService($request);

        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An unexpected error occurred.']);
        }
    }

    public function deleteTile($id){
        try {

            return $this->TileServices->deleteService($id);

        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An unexpected error occurred.']);
        }
    }
}
