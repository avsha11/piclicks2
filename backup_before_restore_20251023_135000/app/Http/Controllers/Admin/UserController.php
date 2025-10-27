<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\UserServices;
use App\Http\Requests\Admin\{UserBlockRequest};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use Yajra\DataTables\DataTables;


class UserController extends Controller
{
    protected $UserServices;

    public function __construct(UserServices $userServices)
    {
        $this->UserServices = $userServices;
    }


    public function allUsersPage()
    {
        return view('admin.userspage');
    }

    public function getAll(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = $this->UserServices->getAllService();

                return DataTables::of($data)
                    ->addColumn('action', function ($row) {

                        $userStatus = $row->user_status == 1 ? 'Block' : 'Active';

                      return '<a href="' . route('admin.userPage',$row->id) . '" class="btn btn-warning btn-sm">View</a> ' .
                        '<button type="button"  data-url="' . route('admin.updateUser', $row->id) . '" class="btn btn-danger btn-sm block-user-btn">' . $userStatus . '</button>';

                    })->make(true);
            }
            return redirect()->back()->withErrors(['error' => 'Invalid request.']);
        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An unexpected error occurred.']);
        }
    }
 
    public function getSingle($id)
    {
        try {

            return $this->UserServices->getSingleService($id);

        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An unexpected error occurred.']);
        }
    }



    public function update($id)
    {
        try {

            return  $this->UserServices->updateService($id);

        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An unexpected error occurred.']);
        }
    }
    public function usersPage($id)
    {
        $data = $this->UserServices->getSingleService($id);
        if ($data instanceof \Illuminate\Http\JsonResponse) {
            // Decode the JSON response
            $data = $data->getData();
        }
        if ($data->status === 1) {
            return view('admin.singleuserpage', ['user' => $data->data]);
        }
    }
}
