<?php

namespace App\Http\Requests\Bed;

use Illuminate\Foundation\Http\FormRequest;

class CompleteCleaningRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $bed = $this->route('bed');
        return $bed && $this->user()->can('completeCleaning', $bed);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
