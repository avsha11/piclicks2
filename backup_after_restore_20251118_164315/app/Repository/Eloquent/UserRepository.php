<?php

namespace App\Repository\Eloquent;

use App\Models\User;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserRepository extends BaseRepository
{

    protected $model;
    protected $cache;
  
   

    public function __construct(
        User $model,
        Cache $cache
    ) {
        $this->model = $model;
        
      
        parent::__construct($model, $cache);
    }

    //its a create function used insert data 

    public function create($allData)
    {
        try {
            return $this->model->create($allData);
        } catch (\Exception $e) {
            Log::error("Error in UserRepository.create(): " . $e->getMessage());
            throw $e;
            // return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }
    
    // Attempt to authenticate the user and check for email verification
    public function login(array $credentials)
    {
    //   $old = getUserId();
    // dd(Hash::make($credentials['password']));
        if (Auth::attempt($credentials)) {
            
            $user = Auth::user();
           
            return $user;
        }

        return null; // Return null if authentication fails
    }

    public function update($byWhere, $update)
    {
        try {
            return $this->model->where($byWhere)->update($update);
        } catch (\Exception $e) {
            Log::error("Error in UserRepository.create(): " . $e->getMessage());
            throw $e;
            // return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }

    public function getOne($byWhere)
    {
        try {
            $data = $this->model->select('*')->where($byWhere)->first();
            return $data;
        } catch (\Exception $e) {
            Log::error("Error in UserRepository.getUser(): " . $e->getMessage());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }


    public function getAll()
    {
        try {
            return $this->model->with('game')->orderBy('id', 'desc')->get();
        } catch (\Exception $e) {
            Log::error("Error in userRepository.userList(): " . $e->getMessage());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }

    public function delete($byWhere)
    {
        try {
            return $this->model->where($byWhere)->delete();
        } catch (\Exception $e) {
            Log::error("Error in UserRepository.deleteData(): " . $e->getMessage());
            throw $e;
            // return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }


    public function getByWhere($byWhere, $orderBy = ['id' => 'desc'])
    {
        try {
            $query = $this->model->where(function ($query) use ($byWhere) {

                foreach ($byWhere as $column => $condition) {
                    if (is_array($condition)) {

                        if ($condition[0] === "IN") {
                            unset($condition[0]);
                            $query->whereIn($column, $condition);
                        } else {
                            $query->where($column, $condition[0], $condition[1]);
                        }
                    } else {
                        $query->where($column, $condition);
                    }
                }
            });


            // Construct the order by string
            $orderByString = '';
            foreach ($orderBy as $column => $direction) {
                $orderByString .= "$column $direction, ";
            }
            $orderByString = rtrim($orderByString, ', ');

            return $query->with('game', 'sologame')->orderByRaw($orderByString)->get();
        } catch (\Exception $e) {
            Log::error("Error in UserRepository.getUsersByWhere(): " . $e->getMessage());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }

    public function createContact($allData)
    {
        try {
            return $this->contactmodel->create($allData);
        } catch (\Exception $e) {
            Log::error("Error in UserRepository.createContact(): " . $e->getMessage());
            throw $e;
            // return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }

    public function findByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    public function updatePassword($email, $password)
    {
        return User::where('email', $email)->update([
            'password' => Hash::make($password),
        ]);
    }

    public function generateTemporaryPassword()
    {
        return Str::random(10);
    }
public function changePasswordRepository($newPassword, $userId)
    {
        try {
            return User::where('id', $userId)->update([
                'password' => Hash::make($newPassword)
            ]);
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return false;
        }
    }
     public function updateProfileRepository($data, $userId)
    {
        try {
            return User::where('id', $userId)->update($data);
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return false;
        }
    }







  
   

}
