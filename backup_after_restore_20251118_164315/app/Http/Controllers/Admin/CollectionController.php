<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use App\Repository\Admin\CollectionRepository;
use App\Repository\Eloquent\PriceRepository;
use App\Services\Admin\CollectionServices;
use Illuminate\Support\Facades\File;

class CollectionController extends Controller
{
    protected $CollectionServices;

    protected $CollectionRepository, $PriceRepository;
    public function __construct(CollectionRepository $CollectionRepository, PriceRepository $PriceRepository, CollectionServices $CollectionServices)
    {
        $this->CollectionRepository = $CollectionRepository;
        $this->PriceRepository = $PriceRepository;
        $this->CollectionServices = $CollectionServices;
    }
    public function index()
    {
        try {
            $data = $this->CollectionRepository->getAll();
            // dd($data);
            return view('admin.collection', compact('data'));
        } catch (\Exception $e) {
            Log::error('Error in CollectionController/index :' . $e->getMessage() . 'in line' . $e->getLine());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }

    public function storeCollection(Request $request)
    {

        try {
            return $this->CollectionServices->create($request);
        } catch (\Exception $e) {
            Log::error("Error in CollectionController.storeCollection(): " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Something went wrong.']);
        }
    }
   
    public function deleteCollection($id)
    {
        try {
            // dd($id); // REMOVE THIS LINE WHEN DONE DEBUGGING
           

                        
                $path = storage_path('app/public/');
                $dataold = $this->CollectionRepository->getOne(['id'=>$id]);
                $oldImagePath = $path . ($dataold->image);
                if (File::exists($oldImagePath)) {
                    File::delete($oldImagePath);
                }
                
              
            
            $success = $this->CollectionRepository->delete(['id' => $id]);
    
            if ($success) {
                return response()->json([
                    'status' => 1,
                    'message' => __('message.statusThree', ['parameter' => __('message.Collection')])
                ]);
            } else {
                return response()->json(['status' => false], 500);
            }
        } catch (Exception $e) {
            Log::error("CollectionController : deleteCollection() at line " . $e->getLine() . " - " . $e->getMessage());
            return redirect()->route('admin.dashboard')->withErrors([
                'error' => __('message.something_went_wrong')
            ]);
        }
    }
    
    public function getCollection($id)
    {
        try {
            
            $success = $this->CollectionRepository->getOne(['id' => $id]);
            if ($success) {
                return response()->json(['status' => true, 'data' => $success, 'message' => __('message.statusFour', ['parameter' => __('message.Collection')])]);
            } else {
                return response()->json(['status' => false], 500);
            }
        } catch (Exception $e) {
            Log::error("CollectionController : getCollection()" . $e->getLine() . " " . $e->getMessage());
            return redirect()->route('admin.dashboard')->withErrors(['error' => __('message.something_went_wrong')]);
        }
    }
    public function updateCollection(Request $request)
    {
       
        try {
            return $this->CollectionServices->update($request);
        } catch (Exception $e) {
            Log::error("CollectionController : update()" . $e->getLine() . " " . $e->getMessage());
            return redirect()->route('admin.dashboard')->withErrors(['error' => __('message.something_went_wrong')]);
        }
    }

}
