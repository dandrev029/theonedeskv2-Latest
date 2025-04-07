<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'phone_number' => ['required', 'string', 'max:20'],
            'unit_number' => ['required', 'string', 'max:50'],
            'condo_location_id' => ['required', 'exists:condo_locations,id'],
            'password' => ['required', 'min:6', 'confirmed']
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => __('The :attribute field is required', ['attribute' => __('name')]),
            'name.max' => __('The :attribute may not be greater than :max characters', ['attribute' => __('name'), 'max' => 255]),

            'email.required' => __('The :attribute field is required', ['attribute' => __('email')]),
            'email.email' => __('The :attribute must be a valid email address', ['attribute' => __('email')]),
            'email.max' => __('The :attribute may not be greater than :max characters', ['attribute' => __('email'), 'max' => 255]),
            'email.unique' => __('The :attribute has already been taken', ['attribute' => __('email')]),

            'password.required' => __('The :attribute field is required', ['attribute' => __('password')]),
            'password.min' => __('The :attribute must be at least :min characters', ['attribute' => __('password'), 'min' => 6]),
            'password.confirmed' => __('The :attribute confirmation does not match', ['attribute' => __('password')]),

            'phone_number.required' => __('The :attribute field is required', ['attribute' => __('phone number')]),
            'phone_number.max' => __('The :attribute may not be greater than :max characters', ['attribute' => __('phone number'), 'max' => 20]),

            'unit_number.required' => __('The :attribute field is required', ['attribute' => __('unit number')]),
            'unit_number.max' => __('The :attribute may not be greater than :max characters', ['attribute' => __('unit number'), 'max' => 50]),

            'condo_location_id.required' => __('The :attribute field is required', ['attribute' => __('condo location')]),
            'condo_location_id.exists' => __('The selected :attribute is invalid', ['attribute' => __('condo location')])
        ];
    }
}
