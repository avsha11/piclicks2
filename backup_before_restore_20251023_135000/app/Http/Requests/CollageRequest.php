<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CollageRequest extends FormRequest
{
    public function rules()
    {
        // $rules['files.*'] = 'required|image|mimes:jpeg,png,jpg,jfif|max:5120';

        // return $rules;
         return [
        'tag_id' => 'required|array|min:1',
        'tag_id.*' => 'exists:tags,id',
        'collection_id' => 'required|exists:collections,id',
        'title' => 'required|string|max:255',
        'short_description' => 'required|string',
        'designer_name' => 'required|string|max:255',
        'amount' => 'required|numeric|min:0.01',
         ];
    }

    public function messages()
    {
        return [
            // 'required' => 'The :attribute field is required.',
        ];
    }

    /**
     * Customize the attribute names for validation errors.
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [];
       

        return $attributes;
    }
}
