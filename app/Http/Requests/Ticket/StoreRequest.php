<?php

namespace App\Http\Requests\Ticket;

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
            'subject' => ['required', 'max:255'],
            'concern_id' => ['required', 'exists:ticket_concerns,id'],
            'voucher_code' => ['nullable', 'string', 'max:50'],
            'department_id' => ['exclude_if:department_id,null', 'exists:departments,id'],
            'priority_id' => ['nullable', 'exists:priorities,id'],
            'body' => ['required'],
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
            'subject.required' => __('The :attribute field is required', ['attribute' => __('subject')]),
            'subject.max' => __('The :attribute may not be greater than :max characters', ['attribute' => __('subject'), 'max' => 255]),

            'concern_id.required' => __('The :attribute field is required', ['attribute' => __('concern')]),
            'concern_id.exists' => __('The selected :attribute is invalid', ['attribute' => __('concern')]),

            'voucher_code.max' => __('The :attribute may not be greater than :max characters', ['attribute' => __('voucher code'), 'max' => 50]),

            'department_id.exists' => __('The selected :attribute is invalid', ['attribute' => __('department')]),

            'priority_id.exists' => __('The selected :attribute is invalid', ['attribute' => __('priority')]),

            'body.required' => __('The :attribute field is required', ['attribute' => __('body')]),
        ];
    }
}
