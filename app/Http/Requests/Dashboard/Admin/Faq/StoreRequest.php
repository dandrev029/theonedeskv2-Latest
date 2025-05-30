<?php

namespace App\Http\Requests\Dashboard\Admin\Faq;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Assuming any authenticated admin user can create FAQs.
        // Add specific permission checks if necessary, e.g., $this->user()->can('create_faq');
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
            'question' => [
                'required',
                'string',
                'max:65535', // Max length for TEXT type
                Rule::unique('faqs')->where(function ($query) {
                    return $query->where('category', $this->category);
                }),
            ],
            'answer' => ['required', 'string', 'max:65535'], // Max length for TEXT type
            'category' => ['required', 'string', Rule::in(['wifi', 'general'])],
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
            'question.required' => __('The :attribute field is required', ['attribute' => __('question')]),
            'question.string' => __('The :attribute must be a string', ['attribute' => __('question')]),
            'question.max' => __('The :attribute may not be greater than :max characters', ['attribute' => __('question'), 'max' => 65535]),
            'question.unique' => __('This :attribute already exists for the selected :category.', ['attribute' => __('question'), 'category' => $this->category]),
            'answer.required' => __('The :attribute field is required', ['attribute' => __('answer')]),
            'answer.string' => __('The :attribute must be a string', ['attribute' => __('answer')]),
            'answer.max' => __('The :attribute may not be greater than :max characters', ['attribute' => __('answer'), 'max' => 65535]),
            'category.required' => __('The :attribute field is required', ['attribute' => __('category')]),
            'category.string' => __('The :attribute must be a string', ['attribute' => __('category')]),
            'category.in' => __('The selected :attribute is invalid. Must be "wifi" or "general".', ['attribute' => __('category')]),
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // No specific preparation needed for FAQs based on current fields,
        // but can be used if complex logic arises.
    }
}
