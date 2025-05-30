<?php

namespace App\Http\Requests\Dashboard\Admin\Faq;

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
        // Assuming any authenticated admin user can update FAQs.
        // Add specific permission checks if necessary, e.g., $this->user()->can('update_faq');
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $faqId = $this->route('faq')->id; // 'faq' should be the route parameter name

        return [
            'question' => [
                'sometimes',
                'required',
                'string',
                'max:65535',
                Rule::unique('faqs')->where(function ($query) {
                    return $query->where('category', $this->category ?? $this->route('faq')->category); // Use current category if not being changed
                })->ignore($faqId),
            ],
            'answer' => ['sometimes', 'required', 'string', 'max:65535'],
            'category' => ['sometimes', 'required', 'string', Rule::in(['wifi', 'general'])],
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
            'question.unique' => __('This :attribute already exists for the selected :category.', ['attribute' => __('question'), 'category' => $this->category ?? $this->route('faq')->category]),
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
        // If category is not present in the request, but question is,
        // we need to ensure the unique rule for question uses the existing category of the FAQ.
        if ($this->filled('question') && !$this->filled('category')) {
            $this->merge([
                'category' => $this->route('faq')->category,
            ]);
        }
    }
}
