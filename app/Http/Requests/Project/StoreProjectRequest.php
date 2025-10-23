<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'min:3'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', 'string', Rule::in(['planning', 'in_progress', 'on_hold', 'completed', 'cancelled'])],
            'priority' => ['nullable', 'string', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'start_date' => ['nullable', 'date', 'before_or_equal:end_date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The project name is required.',
            'name.min' => 'The project name must be at least 3 characters.',
            'name.max' => 'The project name may not be greater than 255 characters.',
            'description.max' => 'The description may not be greater than 2000 characters.',
            'status.in' => 'The selected status is invalid.',
            'priority.in' => 'The selected priority is invalid.',
            'start_date.before_or_equal' => 'The start date must be before or equal to the end date.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'budget.min' => 'The budget must be at least 0.',
            'budget.max' => 'The budget may not be greater than 99,999,999.99.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Set default values if not provided
        $this->merge([
            'status' => $this->status ?? 'planning',
            'priority' => $this->priority ?? 'medium',
        ]);
    }
}
