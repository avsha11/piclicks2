<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\{FrameRequest};
use App\Services\Admin\FrameServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;


class FrameController extends Controller
{
    protected $FrameServices;

    public function __construct(FrameServices $frameServices)
    {
        $this->FrameServices = $frameServices;
    }


    public function framesPage(){
        return view('admin.framespage');
    }

    public function getAll(Request $request){
        try {
            if ($request->ajax()) {

                return $this->FrameServices->getAllService();

            }
            return redirect()->back()->withErrors(['error' => 'Invalid request.']);
        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An unexpected error occurred.']);
        }
    }

    public function getSingle($id){
        try {

            return $this->FrameServices->getSingleService($id);

        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An unexpected error occurred.']);
        }
    }

    public function update(FrameRequest $request){
        try {

            return $this->FrameServices->updateService($request);

        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An unexpected error occurred.']);
        }
    }
}
