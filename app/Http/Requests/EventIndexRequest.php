<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_time' => 'nullable|date', // Optional, valid date, after or equal today
            'end_time' => 'nullable|date', // Optional, valid date, after start_time
            'location' => 'nullable|string|max:255', // Optional, valid string with max length of 255
            'keyword' => 'nullable|string|max:255', // Optional, valid string with max length of 255
            'page' => 'nullable|integer|min:1', // Optional, integer, greater than or equal to 1
            'per_page' => 'nullable|integer|min:1|max:100', // Optional, integer between 1 and 100
        ];
    }

    /**
     * Get custom error messages for validator failures.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'start_time.after_or_equal' => 'The start time must be today or a future date.',
            'end_time.after' => 'The end time must be after the start time.',
            'per_page.min' => 'The per page value must be at least 1.',
            'per_page.max' => 'The per page value may not be greater than 100.',
        ];
    }
}
