<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventShowRequest extends FormRequest
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
    public function rules()
    {
        return [
            'id' => ['integer', 'exists:events,id'], // ID must exist in events table
        ];
    }

    /**
     * Custom error messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'id.integer'  => 'The event ID must be a valid integer.',
            'id.exists'   => 'The selected event does not exist.',
        ];
    }
}
