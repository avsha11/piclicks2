<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use App\Repository\Admin\TagRepository;
use App\Repository\Eloquent\PriceRepository;
use Illuminate\Support\Facades\File;

class TagController extends Controller
{
private $dataObject;
    protected $TagRepository, $PriceRepository;
    public function __construct(TagRepository $TagRepository, PriceRepository $PriceRepository)
    {
        $this->TagRepository = $TagRepository;
        $this->PriceRepository = $PriceRepository;
    }
    public function index()
    {
        try {
            $data = $this->TagRepository->getAll();
            // dd($data);
            return view('admin.tag', compact('data'));
        } catch (\Exception $e) {
            Log::error('Error in TagController/index :' . $e->getMessage() . 'in line' . $e->getLine());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }

    public function storeTag(Request $request)
    {

        try {
            $formdata['name'] = $request->name;
            $data =  $this->TagRepository->create($formdata);
            if($data){
                return response()->json(['message' => __('message.statusOne', ['parameter' => __('message.Tag')]),'status' => 1, 'error' => $this->dataObject], 201);
            }
        } catch (\Exception $e) {
            Log::error("Error in TagController.storeTag(): " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Something went wrong.']);
        }
    }
   
    public function deleteTag($id)
    {
        try {
            // dd($id); // REMOVE THIS LINE WHEN DONE DEBUGGING
           
            $success = $this->TagRepository->delete(['id' => $id]);
    
            if ($success) {
                return response()->json([
                    'status' => 1,
                    'message' => __('message.statusThree', ['parameter' => __('message.Tag')])
                ]);
            } else {
                return response()->json(['status' => false], 500);
            }
        } catch (Exception $e) {
            Log::error("TagController : deleteTag() at line " . $e->getLine() . " - " . $e->getMessage());
            return redirect()->route('admin.dashboard')->withErrors([
                'error' => __('message.something_went_wrong')
            ]);
        }
    }
    
    public function getTag($id)
    {
        try {
            
            $success = $this->TagRepository->getOne(['id' => $id]);
            if ($success) {
                return response()->json(['status' => true, 'data' => $success, 'message' => __('message.statusFour', ['parameter' => __('message.Tag')])]);
            } else {
                return response()->json(['status' => false], 500);
            }
        } catch (Exception $e) {
            Log::error("TagController : getTag()" . $e->getLine() . " " . $e->getMessage());
            return redirect()->route('admin.dashboard')->withErrors(['error' => __('message.something_went_wrong')]);
        }
    }
    public function updateTag(Request $request)
    {
       
        try {
             $formdata = [
                'name' => $request->name,
              
            ];
            $data =  $this->TagRepository->update(['id'=>$request->id],$formdata);
            if($data){
                return response()->json(['message' => __('message.statusTwo', ['parameter' => __('message.Tag')]),'status' => 1, 'error' => $this->dataObject], 201);
            }
        } catch (Exception $e) {
            Log::error("TagController : updateTag()" . $e->getLine() . " " . $e->getMessage());
            return redirect()->route('admin.dashboard')->withErrors(['error' => __('message.something_went_wrong')]);
        }
    }

}
