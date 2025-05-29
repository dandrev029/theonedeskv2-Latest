<?php

namespace App\Http\Requests\Dashboard\Admin\TicketConcern;

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
        $ticketConcernId = $this->route('ticket_concern')->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ticket_concerns')->where(function ($query) {
                    // $this->validation_department_id is set by prepareForValidation()
                    // Assuming condo_location_id is not part of the unique constraint on ticket_concerns table directly,
                    // or the column does not exist as per the SQL error.
                    return $query->where('department_id', $this->validation_department_id);
                })->ignore($ticketConcernId),
            ],
            'status' => ['required', 'boolean'], // Keep 'required' for updates
            'assigned_to_user_id' => [
                'nullable',
                'exists:users,id',
                'required_without:department_id',
                'prohibits:department_id',
                function ($attribute, $value, $fail) {
                    if ($this->filled($attribute)) {
                        $user = \App\Models\User::find($value);
                        if (!$user || !$user->department_id) {
                            $fail(__('The selected user is not valid or not associated with a department.'));
                        }
                    }
                },
            ],
            'department_id' => [ // This is now for department queue selection
                'nullable',
                'exists:departments,id',
                'required_without:assigned_to_user_id',
                'prohibits:assigned_to_user_id',
            ],
            // If condo_location_id is not on ticket_concerns table, validation for it might not be needed here.
            // Keeping it for now, consistent with StoreRequest.
            'condo_location_id' => ['nullable', 'exists:condo_locations,id']
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
            'name.unique' => __('The :attribute has already been taken for the selected department.', ['attribute' => __('name')]), // Updated message
            'status.required' => __('The :attribute field is required', ['attribute' => __('status')]),
            'status.boolean' => __('The :attribute field must be true or false', ['attribute' => __('status')]),
            'assigned_to_user_id.exists' => __('The selected :attribute is invalid', ['attribute' => __('user')]),
            'assigned_to_user_id.required_without' => __('Either a user or a department queue must be assigned.'),
            'assigned_to_user_id.prohibits' => __('Cannot assign both a user and a department queue.'),
            'department_id.exists' => __('The selected :attribute is invalid', ['attribute' => __('department')]),
            'department_id.required_without' => __('Either a department queue or a user must be assigned.'),
            'department_id.prohibits' => __('Cannot assign both a department queue and a user.'),
            'condo_location_id.exists' => __('The selected :attribute is invalid', ['attribute' => __('condominium location')])
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $validationDepartmentId = null;
        if ($this->filled('assigned_to_user_id')) {
            $user = \App\Models\User::find($this->assigned_to_user_id);
            if ($user && $user->department_id) {
                $validationDepartmentId = $user->department_id;
            }
        } elseif ($this->filled('department_id')) {
            $validationDepartmentId = $this->department_id;
        }

        $this->merge([
            'validation_department_id' => $validationDepartmentId,
        ]);
    }
}
