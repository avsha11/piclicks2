<?php

namespace App\Services\Admin;

use App\Repository\Admin\UserRepository;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Models\User;


class UserServices
{
    protected $UserRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->UserRepository = $userRepository;
    }

    public function getAllService()
    {
        try {

            return $this->UserRepository->getAll();
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to fetch products.']);
        }
    }

    public function getSingleService($id)
    {
        try {

            $where = [
                'id' => $id,
            ];

            $data = $this->UserRepository->getSingle($where);

            if ($data) {
                return response()->json(['status' => 1, 'message' => 'Successed to user data update.', 'data' => $data]);
            }
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to fetch products.']);
        }
    }

    public function updateService($id)
    {
        try {
            $user = User::where('id', $id)->first();



            if (!$user) {
                return response()->json(['status' => 0, 'message' => 'User not found.']);
            }

            $status = ($user->user_status === 1) ? 0 : 1;

           $where = [
             'id' => $id,
           ];

            $update = [
                'user_status' => $status,
            ];

            $data = $this->UserRepository->update($where, $update);

            return response()->json(['status' => 1, 'message' => 'Successfully update user.', 'data' => $data, 'user_status' => $status]);

        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Failed to update user status.']);
        }
    }
}
