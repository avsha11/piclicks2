<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CurrentAdminPassword implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
{
    // Check if the user is authenticated
    if (!Auth::check()) {
        return; // Optionally handle unauthenticated users
    }

    // Get the authenticated user
    $user = Auth::user();

    // Check if the provided password matches the stored password
    if (!Hash::check($value, $user->password)) {
        $fail('The current password is incorrect.');
    }
}
}
