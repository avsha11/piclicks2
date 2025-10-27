<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\{LoginRequest, ProfileUpdateRequest, ChangePasswordRequest, VerifyEmailRequest};
use App\Http\Requests\VerifyEmailRequest as RequestsVerifyEmailRequest;
use App\Services\Admin\AdminServices;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;


class AdminController extends Controller
{
    protected $AdminServices;

    public function __construct(AdminServices $adminServices)
    {
        $this->AdminServices = $adminServices;
    }

    public function loginPage()
    {
        try {

            if ($this->AdminServices->checkLoginService()) {
                return redirect()->route('admin.dashboard');
            }
            return view('admin.index');
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to check admin login from controller.']);
        }
    }

    public function loginSubmit(LoginRequest $request)
    {
        try {

            if ($this->AdminServices->loginService($request)) {
                return response()->json(['status' => 1, 'message' => 'Admin Logged In Successfully']);
            }

            return response()->json(['status' => 0, 'message' => 'Invalid Credentials']);
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to admin login submit from controller.']);
        }
    }

    // Admin Logout Method
    public function logout()
    {
        try {
            if ($this->AdminServices->logoutService()) {
                return redirect()->route('admin.loginPage')->with('Success', 'Admin Logout');
            }
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to admin logout from controller.']);
        }
    }

    public function dashboard()
    {
        try {

            $admin = Auth::guard('admins')->user();

            return view('admin.dashboard',['admin' => $admin]);

        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to admin login submit from controller.']);
        }
    }

    public function updateProfilePage()
    {
        try {
            $admin = Auth::guard('admins')->user();

            return view('admin.updateprofile',['admin'=>$admin]);
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to admin update profile page from controller.']);
        }
    }
    public function updateProfileSubmit(ProfileUpdateRequest $request)
    {
        try {

            $response = $this->AdminServices->profileUpdateService($request);

            if ($response['status'] === 1) {
                return response()->json(['status' => 1, 'message' => 'Admin Successfully updated']);
            } else {
                return response()->json(['status' => 0, 'message' => 'Admin profile failed to update.']);
            }


        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to admin update profile submit from controller.']);
        }
    }

    public function changePasswordPage()
    {
        try {
            $admin = Auth::guard('admins')->user();

            return view('admin.changepassword',['admin'=>$admin]);
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to admin change password from controller.']);
        }
    }

    public function changePasswordSubmit(ChangePasswordRequest $request)
    {
        try {
            $response = $this->AdminServices->changePasswordService($request);

            if ($response['status'] === 1) {
                return response()->json(['status' => 1, 'message' => 'Admin Successfully changed']);
            } else {
                return response()->json(['status' => 0, 'message' => 'Admin password does not changed.']);
            }
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to admin change password from controller.']);
        }
    }

    public function forgotPasswordPage(){
        try {
            return view('email.forgotpassword');
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to forgot password page.']);
        }
    }

    public function forgotPasswordSubmit(VerifyEmailRequest $request){
        try {

            return $this->AdminServices->forgotPasswordService($request);


        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to forgot password page.']);
        }
    }
}
