<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class MatchCurrentPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        Log::info('Validating current password for user ID: ' . Auth::id()); // Debugging

        if (!Auth::check()) {
            Log::error('User is not authenticated.');
            $fail('Authentication required.'); // Stop validation
            return;
        }

        $user = Auth::user();
        
        if (!Hash::check($value, $user->password)) {
            Log::error('Password does not match for user ID: ' . $user->id);
            $fail('The current password is incorrect.');
        }
    }
}
