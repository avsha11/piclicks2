<?php

namespace App\Repository\Admin;

use App\Models\Admin;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;



class AdminRepository implements AdminRepositoryInterface
{
    protected $model;
    protected $auth;


    public function __construct(Admin $admin, Auth $auth)
    {
        $this->model = $admin;
        $this->auth = $auth::guard('admins');
    }

    public function checkLoginRepository()
    {
        try {
            return $this->auth->check();
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to check admin login from repository.']);
        }
    }

    public function loginRepository($request)
    {
        try {
            $credentials = $request->only('email', 'password');

            if ($this->auth->attempt($credentials)) {
                return true;
            }
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to admin login submit from repository.']);
        }
    }

    public function logoutRepository()
    {
        try {

            $this->auth->logout();
            return true;
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to admin logout from repository.']);
        }
    }

    public function profileUpdateRepository($request)
    {
        try {


            $profileData = $this->model::findOrFail($this->auth->user()->id);



            $fileName = null;

            if($request->adminProfile === null){
                $fileName = $profileData->admin_profile;
            } else{
                if ($request->hasFile('adminProfile')) {
                    if ($request->adminProfile) {
                        $oldFilePath = public_path('storage/adminprofile/' . $request->adminProfile);
                        // Check if the old file exists and delete it
                        if (file_exists($oldFilePath)) {
                            $fileName = null;
                            unlink($oldFilePath);
                        }
                        $filePath = $request->adminProfile->store('adminprofile', 'public');
                        $fileName = basename($filePath);
                    }
                }
            }

            $profileData->name = $request->adminName;
            $profileData->email = $request->adminEmail;
            $profileData->admin_profile = $fileName;
            $profileData->shipping = $request->shippingPercentage;
            $profileData->sale_tax = $request->taxPercentage;
            $profileData->save();
            return $profileData;

        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to admin profile update from repository.']);
        }
    }

    public function changePasswordRepository($request){
        try {

            $profileData = $this->model::findorfail($this->auth->user()->id);

            $profileData->password = Hash::make($request->password);
            $profileData->save();
            return $profileData;



        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to admin profile update from repository.']);
        }
    }

    // public function forgotPasswordRepository($password){
    //     try {



    //         // Ensure that you are getting the currently authenticated user
    //         $userId = $this->auth->user();

    //         if($userId === null){
    //             dd('null');
    //         }

    //         // Retrieve the user by ID or fail if not found
    //         $profileData = $this->model::findOrFail($userId);

    //         $profileData->update([
    //             'password' =>  Hash::make($password),
    //         ]);

    //         // Return the updated user data
    //         return $profileData;


    //     } catch (Exception $e) {
    //         Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
    //         return response()->json(['status' => 0, 'message' => 'Failed to admin profile update from repository.']);
    //     }
    // }


    public function update($byWhere, $data){
        try {

            return $this->model->where($byWhere)->update($data);

        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            // return response()->json(['status' => 0, 'message' => 'Failed to admin profile update from repository.']);
            throw $e;
        }
    }
}
