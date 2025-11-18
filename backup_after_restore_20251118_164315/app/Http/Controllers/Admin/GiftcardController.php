<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use App\Repository\Admin\GiftcardRepository;
use App\Repository\Eloquent\PriceRepository;
use App\Services\Admin\GiftcardServices;
use Illuminate\Support\Facades\File;

class GiftcardController extends Controller
{
    protected $GiftcardServices;

    protected $GiftcardRepository, $PriceRepository;
    public function __construct(GiftcardRepository $GiftcardRepository, PriceRepository $PriceRepository, GiftcardServices $GiftcardServices)
    {
        $this->GiftcardRepository = $GiftcardRepository;
        $this->PriceRepository = $PriceRepository;
        $this->GiftcardServices = $GiftcardServices;
    }
    public function index()
    {
        try {
            $data = $this->GiftcardRepository->getAll();
            // dd($data);
            return view('admin.giftcardlist', compact('data'));
        } catch (\Exception $e) {
            Log::error('Error in GiftcardController/index :' . $e->getMessage() . 'in line' . $e->getLine());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }

    public function storeGiftcard(Request $request)
    {

        try {
            return $this->GiftcardServices->create($request);
        } catch (\Exception $e) {
            Log::error("Error in GiftcardController.storeGiftcard(): " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Something went wrong.']);
        }
    }
   
    public function deleteGiftcard($id)
    {
        try {
            // dd($id); // REMOVE THIS LINE WHEN DONE DEBUGGING
           

                        
                $path = storage_path('app/public/');
                $dataold = $this->GiftcardRepository->getOne(['id'=>$id]);
                $oldImagePath = $path . ($dataold->image);
                if (File::exists($oldImagePath)) {
                    File::delete($oldImagePath);
                }
                
              
            
            $success = $this->GiftcardRepository->delete(['id' => $id]);
    
            if ($success) {
                return response()->json([
                    'status' => 1,
                    'message' => __('message.statusThree', ['parameter' => __('message.Giftcard')])
                ]);
            } else {
                return response()->json(['status' => false], 500);
            }
        } catch (Exception $e) {
            Log::error("GiftcardController : deleteGiftcard() at line " . $e->getLine() . " - " . $e->getMessage());
            return redirect()->route('admin.dashboard')->withErrors([
                'error' => __('message.something_went_wrong')
            ]);
        }
    }
    
    public function getGiftcard($id)
    {
        try {
            
            $success = $this->GiftcardRepository->getOne(['id' => $id]);
            if ($success) {
                return response()->json(['status' => true, 'data' => $success, 'message' => __('message.statusFour', ['parameter' => __('message.Giftcard')])]);
            } else {
                return response()->json(['status' => false], 500);
            }
        } catch (Exception $e) {
            Log::error("GiftcardController : getGiftcard()" . $e->getLine() . " " . $e->getMessage());
            return redirect()->route('admin.dashboard')->withErrors(['error' => __('message.something_went_wrong')]);
        }
    }
    public function updateGiftcard(Request $request)
    {
       
        try {
            return $this->GiftcardServices->update($request);
        } catch (Exception $e) {
            Log::error("GiftcardController : update()" . $e->getLine() . " " . $e->getMessage());
            return redirect()->route('admin.dashboard')->withErrors(['error' => __('message.something_went_wrong')]);
        }
    }

}
