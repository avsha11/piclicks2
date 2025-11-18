<?php
namespace App\Services\Admin;

use Illuminate\Support\Facades\Auth;

use App\Repository\Admin\AdminRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Exception;
use App\Mail\ForgotPasswordMail;
use Illuminate\Support\Facades\Mail;


class AdminServices{
    protected $AdminRepository;

    public function __construct(AdminRepository $adminRepository)
    {
        $this->AdminRepository = $adminRepository;
    }

    public function checkLoginService(){
        try {

           return $this->AdminRepository->checkLoginRepository();

        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to check admin login from service.']);
        }
    }

    public function loginService($request){
        try {

            return $this->AdminRepository->loginRepository($request);

        } catch (Exception $e) {
             Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
             return response()->json(['status' => 0, 'message' => 'Failed to admin login submit from service.']);
        }
    }

    public function logoutService(){
        try {

            return $this->AdminRepository->logoutRepository();

        } catch (Exception $e) {
             Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
             return response()->json(['status' => 0, 'message' => 'Failed to admin logout from service.']);
        }
    }

    public function profileUpdateService($request){
        try {
            $data =  $this->AdminRepository->profileUpdateRepository($request);

            if($data){
                return [
                    'status' => 1,
                    'message' => 'Profile Successfully Updated',
                    'errors' => [],
                    'data' => []
                ];
           }
            return [
                'status' => 0,
                'message' => 'profile could not update.',
                'errors' => [],
                'data' => []
            ];

        } catch (Exception $e) {
             Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
             return response()->json(['status' => 0, 'message' => 'Failed to admin update profile from service.']);
        }
    }

    public function changePasswordService($request){
        try {

            $data =  $this->AdminRepository->changePasswordRepository($request);
            // dd($data);

            if($data){
                return [
                    'status' => 1,
                    'message' => 'Password Changed Successfully.',
                    'errors' => [],
                    'data' => []
                ];
           }
            return [
                'status' => 0,
                'message' => 'Password could not changed.',
                'errors' => [],
                'data' => []
            ];

        } catch (Exception $e) {
             Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
             return response()->json(['status' => 0, 'message' => 'Failed to admin password changed from service.']);
        }
    }

    public function forgotPasswordService($request){
        try {

            $tempPassword = mt_rand(100000,999999);

            $mailData = [
                'tempPassword' => $tempPassword, // Pass the correct variable name
            ];

           Mail::to($request->email)->send(new ForgotPasswordMail($mailData));



            $byWhere = [
                'email' => $request->email,
            ];

            $data = [
                'password' => Hash::make($tempPassword),
            ];

            $data =  $this->AdminRepository->update($byWhere, $data);

            if($data){
                return response()->json([
                    'status' => 1,
                    'message' => 'Password sent Successfully.',
                    'errors' => [],
                    'data' => []
                ]);
           }
            return response()->json([
                'status' => 0,
                'message' => 'Unable to sent forget password mail, try again.',
                'errors' => [],
                'data' => []
            ]);

        } catch (Exception $e) {
             Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
             return response()->json(['status' => 0, 'message' => 'Failed to admin password changed from service.']);
        }
    }




}
