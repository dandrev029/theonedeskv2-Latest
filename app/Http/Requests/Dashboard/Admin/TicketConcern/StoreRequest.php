<?php

namespace App\Http\Requests\Dashboard\Admin\TicketConcern;

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
                Rule::unique('ticket_concerns')->where(function ($query) {
                    // $this->validation_department_id is set by prepareForValidation()
                    // Assuming condo_location_id is not part of the unique constraint on ticket_concerns table directly,
                    // or the column does not exist as per the SQL error.
                    return $query->where('department_id', $this->validation_department_id);
                }),
            ],
            'status' => ['boolean'],
            'assigned_to' => [
                'nullable',
                'integer',
                'exists:users,id',
                'required_without:department_id',
                'prohibits:department_id',
                function ($attribute, $value, $fail) {
                    $user = \App\Models\User::find($value);
                    if (!$user) {
                        $fail(__('The selected user does not exist.'));
                    } elseif ($user->departments()->count() === 0) {
                        $fail(__('The selected user must be assigned to at least one department.'));
                    }
                }
            ],
            'department_id' => [ // This is now for department queue selection
                'nullable',
                'integer',
                'exists:departments,id',
                'required_without:assigned_to',
                'prohibits:assigned_to',
            ],
            // If condo_location_id is not on ticket_concerns table, validation for it might not be needed here,
            // or it's validated if it's used for other purposes.
            // For now, keeping it as it was, assuming it might be used elsewhere or intended for future.
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
            'status.boolean' => __('The :attribute field must be true or false', ['attribute' => __('status')]),
            'assigned_to.exists' => __('The selected :attribute is invalid', ['attribute' => __('user')]),
            'assigned_to.required_without' => __('Either a user or a department queue must be assigned.'),
            'assigned_to.prohibits' => __('Cannot assign both a user and a department queue.'),
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
        $assignedTo = $this->input('assigned_to');
        $departmentId = $this->input('department_id');

        // Ensure only one of assigned_to or department_id is passed to validation
        if ($this->filled('assigned_to')) {
            // If user is selected, remove department_id from request to avoid conflict with 'prohibits' rule
            // and to ensure 'required_without' works correctly.
            $this->request->remove('department_id');
            $departmentId = null; // Nullify for local logic too
        } elseif ($this->filled('department_id')) {
            // If department is selected, remove assigned_to
            $this->request->remove('assigned_to');
            $assignedTo = null; // Nullify for local logic too
        }

        if ($assignedTo) {
            $user = \App\Models\User::find($assignedTo);
            // Ensure user exists and has a department_id.
            if ($user && $user->departments()->exists()) {
                // If user is assigned to multiple departments, this will take the first one.
                // This might need refinement if a specific department context is required for uniqueness.
                $validationDepartmentId = $user->departments()->first()->id;
            }
        } elseif ($departmentId) {
            $validationDepartmentId = $departmentId;
        }

        $this->merge([
            'validation_department_id' => $validationDepartmentId,
        ]);

        // If department_id was sent (from department queue), and assigned_to_user_id was also sent (error),
        // prefer department_id for validation_department_id to avoid issues if assigned_to_user_id was invalid.
        // The 'prohibits' rule will catch this anyway.
        // The logic above is fine: if assigned_to_user_id is filled, it takes precedence for finding dept.
        // If not, then department_id (queue) is used.
    }
}
