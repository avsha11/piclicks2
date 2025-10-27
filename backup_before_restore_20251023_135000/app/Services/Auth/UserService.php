<?php

namespace App\Services\Auth;

use App\Repository\Eloquent\{UserRepository, OrderRepository};
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use App\Traits\UploadImageTrait;
use Exception;
use Illuminate\Support\Facades\View;

class UserService
{
    protected $userRepository, $OrderRepository;
    use UploadImageTrait;
    public function __construct(UserRepository $userRepository, OrderRepository $OrderRepository)
    {
        $this->userRepository = $userRepository;
        $this->OrderRepository = $OrderRepository;
    }

    public function signUp(array $data)
    {
        // Validate data
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        $data['user_status'] = 1;
        // Create the user
        $user = $this->userRepository->create($data);

        // Send the email verification link
        $this->sendVerificationLink($user);

        return $user;
    }

    public function sendVerificationLink(User $user)
    {
        $user->sendEmailVerificationNotification();
    }

    public function login(array $credentials)
    {
        // Use the repository to authenticate the user
        $user = $this->userRepository->login($credentials);
        $html = View::make('front.partials.home-left-offset')->render();
        $htmlTop = View::make('front.partials.top-offset')->render();

        if ($user) {
            // Check if the email is verified
            if (!$user->hasVerifiedEmail()) {
                return [
                    'status' => 'error',
                    'new_csrf_token' => csrf_token(), // Send new CSRF token to frontend
                    'top_tray' => $htmlTop, // Injecting Blade content here
                    'sub_offmenu' => $html, // Injecting Blade content here
                    'message' => Lang::get('message.email_verification_required'),
                ];
            }

            // check user is not blocked
            if ($user->user_status == 0) {
                return [
                    'status' => 'error',
                    'new_csrf_token' => csrf_token(), // Send new CSRF token to frontend
                    'top_tray' => $htmlTop, // Injecting Blade content here
                    'sub_offmenu' => $html, // Injecting Blade content here
                    'message' => "Account is blocked by admin please contact to admin",
                ];
            }

            $giftcard_message = '';
            $giftcard_status = 'info';
            if (session('redeemGiftcard')) {
                $code = session('redeemGiftcard');
                $giftCard = $this->OrderRepository->getOneGiftcard(['code' => $code]);
                if (!$giftCard) {
                    $giftcard_message = __('message.statusFour', ['parameter' => 'Gift Card redeem link']);
                    $giftcard_status = 'error';
                }

                if ($giftCard->status === 1 || $giftCard->status === 2) {
                    $giftcard_message = 'Gift Card already redeemed, to check visit your Received Gift cards.';
                    $giftcard_status = 'error';
                } else {

                    $update['status'] = 2;
                    $update['redeem_by'] = auth()->user()->id;
                    $run_gc = $this->OrderRepository->updateGiftcard(['code' => $code], $update);

                    session()->forget('redeemGiftcard');
                    if ($run_gc) {
                        $giftcard_message = 'You have successfully redeemed the gift card.';
                        $giftcard_status = 'success';
                    } else {
                        $giftcard_message = 'Something went wrong in giftcard, try redeeming again.';
                        $giftcard_status = 'error';
                    }
                }
            }

            $guest_id = getUserId();
            updateGuestCartToUser();
            updateGuestFavoritesToUser($guest_id);
            return [
                'status' => 'success',
                'message' => Lang::get('message.login_success'),
                'giftcard' => [
                    'message' => $giftcard_message,
                    'status' => $giftcard_status,
                ],
                'new_csrf_token' => csrf_token(), // Send new CSRF token to frontend
                'top_tray' => $htmlTop, // Injecting Blade content here
                'sub_offmenu' => $html, // Injecting Blade content here
                'user' => [
                    'name' => $user->name,
                    'profile_photo_url' => asset('storage/' . Auth::user()->profile_picture), // Assuming you have this attribute
                ],
                'cartSummary' => calculateCart()
            ];
        }

        return [
            'status' => 'error',
            'new_csrf_token' => csrf_token(), // Send new CSRF token to frontend
            'top_tray' => $htmlTop, // Injecting Blade content here
            'sub_offmenu' => $html, // Injecting Blade content here
            'message' => Lang::get('message.login_error'),
        ];
    }
    public function sendResetPassword($email)
    {
        try {
            $user = $this->userRepository->findByEmail($email);

            if (!$user) {
                return ['status' => 'error', 'message' => 'Email not found in our records.'];
            }

            // Generate temporary password
            $tempPassword = $this->userRepository->generateTemporaryPassword();

            // Store token in `password_resets` table
            $token = Str::random(60);
            DB::table('password_resets')->updateOrInsert(
                ['email' => $email],
                ['token' => $token, 'created_at' => now()]
            );
            $this->userRepository->updatePassword($email, $tempPassword);

            // Send email
            try {
                Mail::send('auth.password-reset', ['tempPassword' => $tempPassword], function ($message) use ($email) {
                    $message->to($email)->subject('Password Reset Request');
                });
            } catch (Exception $e) {
                Log::error("Error in mail sending " . __CLASS__ . "::" . __FUNCTION__ . ": line " . $e->getLine() . ".: " . $e->getMessage());
                return ['status' => 'error', 'message' => 'Email not sent, try again.'];
            }

            return ['status' => 'success', 'message' => 'Temporary password sent to your email.'];
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": line " . $e->getLine() . ".: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Something went wrong, unable to reset password, try again.'];
        }
    }

    public function resetPassword($email, $token, $newPassword)
    {
        $resetData = DB::table('password_resets')->where('email', $email)->first();

        if (!$resetData || !hash_equals($resetData->token, $token)) {
            return ['status' => 'error', 'message' => 'Invalid or expired reset token.'];
        }

        $this->userRepository->updatePassword($email, $newPassword);
        DB::table('password_resets')->where('email', $email)->delete();

        return ['status' => 'success', 'message' => 'Password successfully updated.'];
    }
    public function changePasswordService($data, $authId)
    {
        try {
            $user = Auth::user();

            if (!Hash::check($data['currentPassword'], $user->password)) {
                return [
                    'status' => 0,
                    'message' => "Your old password doesn't match.",
                    'errors' => []
                ];
            }

            // Call repository to update password
            $updated = $this->userRepository->changePasswordRepository($data['password'], $authId);

            if ($updated) {
                return [
                    'status' => 1,
                    'message' => __('message.password_updated'),
                    'errors' => []
                ];
            }

            return [
                'status' => 0,
                'message' => 'Password could not be updated.',
                'errors' => []
            ];
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return [
                'status' => 0,
                'message' => __('message.unexpected_error'),
                'errors' => [$e->getMessage()]
            ];
        }
    }
    public function updateProfileService($data, $authId)
    {
        try {
            $updateData = [
                'name' => $data['name'],
                'email' => $data['email']
            ];

            // Handle Profile Picture Upload
            if (isset($data['profile_picture'])) {
                $file = $data['profile_picture'];
                // Add image URL to update data
                $updateData['profile_picture'] = $this->uploadImage($file, 'profileImage');
            }

            // Call repository to update user data
            $updated = $this->userRepository->updateProfileRepository($updateData, $authId);

            if ($updated) {
                return [
                    'status' => 1,

                    'message' => __('message.profile_updated'),
                    'data' => ['profile_picture' => $updateData['profile_picture'] ?? Auth::user()->profile_picture]
                ];
            }

            return [
                'status' => 0,
                'message' => __('message.profile_update_failed'),
                'errors' => []
            ];
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return [
                'status' => 0,
                'message' => __('message.unexpected_error'),
                'errors' => [$e->getMessage()]
            ];
        }
    }
}
