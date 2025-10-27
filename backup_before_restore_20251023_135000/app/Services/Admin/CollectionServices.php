<?php

namespace App\Services\Admin;

use App\Repository\Admin\CollectionRepository;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Traits\UploadImageTrait;
use Illuminate\Support\Facades\File;
class CollectionServices
{
    use UploadImageTrait;
    protected $CollectionRepository;
    private $dataObject;
    public function __construct(CollectionRepository $CollectionRepository)
    {
        $this->CollectionRepository = $CollectionRepository;
        $this->dataObject = new \stdClass();
    }

    public function create($request){
       
        try{
              $formdata = [
                'name' => $request->name,
            ];
            if ($request->hasFile('logo')) {
                $path = storage_path('app/public/');
                $image = $request->file('logo');
                $imagePath = $this->uploadImage($image, 'logo');  // Save to 'artists' folder in the public disk
                 $formdata['logo'] = $imagePath;
            }
           $data =  $this->CollectionRepository->create($formdata);
            if($data){
                return response()->json(['message' => __('message.statusOne', ['parameter' => __('message.Collection')]),'status' => 1, 'error' => $this->dataObject], 201);
            }

        }catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to add  Collection.']);
        }
     
    }
    public function update($request){
       
        try{
            // dd($request);
            $formdata = [
                'name' => $request->name,
              
            ];
            if ($request->hasFile('logo')) {

                
                $path = storage_path('app/public/');
                $dataold = $this->CollectionRepository->getOne(['id'=>$request->id]);
                $oldImagePath = $path . ($dataold->image);
                if (File::exists($oldImagePath)) {
                    File::delete($oldImagePath);
                }
                $image = $request->file('logo');
                // Generate a unique file name for the image and store it
                $imagePath = $this->uploadImage($image, 'logo');  // Save to 'artists' folder in the public disk
                // Add the image path to the data
                 $formdata['logo'] = $imagePath;
              
            }
            // dd($formdata);
           $data =  $this->CollectionRepository->update(['id'=>$request->id],$formdata);
            if($data){
                return response()->json(['message' => __('message.statusTwo', ['parameter' => __('message.Collection')]),'status' => 1, 'error' => $this->dataObject], 201);
            }

        }catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to update user status.']);
        }
     
    }
    
}
