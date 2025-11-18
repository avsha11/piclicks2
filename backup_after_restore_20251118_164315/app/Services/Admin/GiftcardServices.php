<?php

namespace App\Services\Admin;

use App\Repository\Admin\GiftcardRepository;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Traits\UploadImageTrait;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GiftcardServices
{
    use UploadImageTrait;
    protected $GiftcardRepository;
    private $dataObject;
    public function __construct(GiftcardRepository $GiftcardRepository)
    {
        $this->GiftcardRepository = $GiftcardRepository;
        $this->dataObject = new \stdClass();
    }

    public function create($request){
       
        try{
              $formdata = [
                'title' => $request->title,
                'short_description' => $request->short_description,
                'amount' => $request->amount,
                'code'=>Str::random(20)
            ];
            if ($request->hasFile('image')) {
                $path = storage_path('app/public/');
                $image = $request->file('image');
                $imagePath = $this->uploadImage($image, 'image');  // Save to 'artists' folder in the public disk
                 $formdata['image'] = $imagePath;
            }
           $data =  $this->GiftcardRepository->create($formdata);
            if($data){
                return response()->json(['message' => __('message.statusOne', ['parameter' => __('message.Giftcard')]),'status' => 1, 'error' => $this->dataObject], 201);
            }

        }catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to add  Giftcard.']);
        }
     
    }
    public function update($request){
       
        try{
            // dd($request);
           $formdata = [
                'title' => $request->title,
                'short_description' => $request->short_description,
                'amount' => $request->amount
            ];
            if ($request->hasFile('image')) {

                
                $path = storage_path('app/public/');
                $dataold = $this->GiftcardRepository->getOne(['id'=>$request->id]);
                $oldImagePath = $path . ($dataold->image);
                if (File::exists($oldImagePath)) {
                    File::delete($oldImagePath);
                }
                $image = $request->file('image');
                // Generate a unique file name for the image and store it
                $imagePath = $this->uploadImage($image, 'image');  // Save to 'artists' folder in the public disk
                // Add the image path to the data
                 $formdata['image'] = $imagePath;
              
            }
            // dd($formdata);
           $data =  $this->GiftcardRepository->update(['id'=>$request->id],$formdata);
            if($data){
                return response()->json(['message' => __('message.statusTwo', ['parameter' => __('message.Giftcard')]),'status' => 1, 'error' => $this->dataObject], 201);
            }

        }catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to update user status.']);
        }
     
    }
    
}
