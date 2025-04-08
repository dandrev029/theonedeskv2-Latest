<?php

namespace App\Http\Requests\Dashboard\Admin\TicketConcern;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'unique:ticket_concerns'],
            'status' => ['boolean'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'department_id' => ['nullable', 'exists:departments,id']
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
            'status.boolean' => __('The :attribute field must be true or false', ['attribute' => __('status')]),
            'assigned_to.exists' => __('The selected :attribute is invalid', ['attribute' => __('user')]),
            'department_id.exists' => __('The selected :attribute is invalid', ['attribute' => __('department')])
        ];
    }
}
