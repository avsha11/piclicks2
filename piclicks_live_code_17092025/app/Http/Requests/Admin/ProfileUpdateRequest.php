<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "adminName"=> 'required',
            "adminEmail"=> 'required|email|unique:users,email,'.Auth::guard('admins')->user()->id,
            "adminProfile"=> 'nullable|image|mimes:png,jpg,jpeg,jfif|max:5120',
        ];
    }
}
