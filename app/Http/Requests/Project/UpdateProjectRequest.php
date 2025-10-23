<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255', 'min:3'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['planning', 'in_progress', 'on_hold', 'completed', 'cancelled'])],
            'priority' => ['sometimes', 'required', 'string', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'start_date' => ['nullable', 'date', 'before_or_equal:end_date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The project name is required.',
            'name.min' => 'The project name must be at least 3 characters.',
            'name.max' => 'The project name may not be greater than 255 characters.',
            'description.max' => 'The description may not be greater than 2000 characters.',
            'status.required' => 'The status field is required.',
            'status.in' => 'The selected status is invalid.',
            'priority.required' => 'The priority field is required.',
            'priority.in' => 'The selected priority is invalid.',
            'start_date.before_or_equal' => 'The start date must be before or equal to the end date.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'budget.min' => 'The budget must be at least 0.',
            'budget.max' => 'The budget may not be greater than 99,999,999.99.',
        ];
    }
}
