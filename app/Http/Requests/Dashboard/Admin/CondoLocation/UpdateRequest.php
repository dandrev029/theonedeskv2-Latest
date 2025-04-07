<?php

namespace App\Http\Requests\Dashboard\Admin\CondoLocation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
            'name' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('condo_locations')->ignore($this->route('condo_location'))
            ],
            'status' => ['required', 'boolean']
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
            'name.unique' => __('The :attribute has already been taken', ['attribute' => __('name')]),
            'status.required' => __('The :attribute field is required', ['attribute' => __('status')]),
            'status.boolean' => __('The :attribute field must be true or false', ['attribute' => __('status')])
        ];
    }
}
