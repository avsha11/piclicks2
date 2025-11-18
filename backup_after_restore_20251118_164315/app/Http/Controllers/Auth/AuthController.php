<?php

namespace App\Http\Controllers\Auth;



use App\Services\Auth\UserService;

use Illuminate\Http\Request;

use App\Http\Requests\Auth\SignUpRequest;

use App\Http\Requests\Auth\LoginRequest;

use App\Http\Requests\Auth\UpdateProfileRequest;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\View;



use App\Http\Requests\Auth\{ChangePasswordRequest};





use Illuminate\Support\Facades\Lang;

use App\Models\User;

use App\Http\Controllers\Controller; // <-- This line should be here!



class AuthController extends Controller

{

    protected $userService;



    public function __construct(UserService $userService)

    {

        $this->userService = $userService;
    }



    // Sign up a new user

    public function signUp(SignUpRequest $request)

    {

        $validated = $request->validated(); // This will contain only validated data

        // Sign up the user

        $user = $this->userService->signUp($validated);

        // If the user is created successfully, return a success message with AJAX

        return response()->json([

            'status' => 'success',

            'message' => trans('message.signup_success'), // Using language file message

            'new_csrf_token' => csrf_token(),

        ]);
    }





    // Login an existing user

    public function login(LoginRequest $request)

    {

        $credentials = $request->only('email', 'password');



        // Call the userService to handle the login logic

        $response = $this->userService->login($credentials);



        // Return the response from the userService

        return response()->json($response);
    }



    // Email verification

    public function verifyEmail($id, $hash)

    {

        $user = User::findOrFail($id);



        if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {

            return view('auth.verify-email'); // Show the email verification required page directly

        }



        $user->markEmailAsVerified();



        return view('auth.email-verified', ['user' => $user]);
    }





    // Resend verification link

    public function resendVerification(Request $request)

    {

        $user = $request->user();



        if ($user->hasVerifiedEmail()) {

            return redirect()->route('home');
        }



        $this->userService->sendVerificationLink($user);



        return back()->with('resent', true);
    }

    public function logout(Request $request)

    {

        Auth::logout();

        $request->session()->invalidate(); // Invalidate session

        $request->session()->regenerateToken(); // Regenerate CSRF token

        $html = View::make('front.partials.home-left-offset')->render();

        $htmlTop = View::make('front.partials.top-offset')->render();

        return response()->json([

            'status' => 'success',

            'new_csrf_token' => csrf_token(), // Send new CSRF token to frontend

            'sub_offmenu' => $html, // Injecting Blade content here

            'top_tray' => $htmlTop, // Injecting Blade content here

            'message' => 'Logged out successfully.',

        ]);
    }

    public function logout1(Request $request)

    {

        Auth::logout(); // Logout user



        $request->session()->invalidate(); // Invalidate session

        $request->session()->regenerateToken(); // Regenerate CSRF token



        return redirect()->route('front.index')->with('success', __('messages.logout_success'));
    }

    public function forgotPassword(Request $request)

    {

        $request->validate(['email' => 'required|email']);



        $response = $this->userService->sendResetPassword($request->email);

        return response()->json($response);
    }



    public function resetPassword(Request $request)

    {

        $request->validate([

            'email' => 'required|email',

            'token' => 'required',

            'password' => 'required|min:6|confirmed',

        ]);



        $response = $this->userService->resetPassword($request->email, $request->token, $request->password);

        return response()->json($response);
    }

    public function changePassword()
    {
        $title = 'Change Password';
        return view('front.profile.change-password', compact('title'));
    }

    // User  change Password
    public function changePasswordSubmit(ChangePasswordRequest $request)
    {

        try {
            $authId = $user = Auth::user()->id;
            $response = $this->userService->changePasswordService($request, $authId);

            if ($response['status'] === 1) {
                return $response;
            } else {
                return $response;
            }
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An unexpected error occurred.']);
        }
    }


    public function profilePage()
    {
        $title = 'Edit Profile';
        return view('front.profile.edit-profile', compact('title'));
    }

    public function updateProfile(UpdateProfileRequest $request)

    {

        $response = $this->userService->updateProfileService($request->validated(), Auth::id());



        return response()->json($response);
    }

    public function checkAuth()

    {

        return response()->json(['authenticated' => Auth::check(), 'user' => Auth::user()]);
    }
}
