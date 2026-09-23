<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => [
                'required', 
                'email', 
                'unique:users,email',
                function ($attribute, $value, $fail) {
                    $parts = explode('@', $value);
                    if (count($parts) === 2 && strlen($parts[0]) < 3) {
                        $fail('The email username must be at least 3 characters.');
                    }
                }
            ],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->symbols()]
        ];
    }
}
