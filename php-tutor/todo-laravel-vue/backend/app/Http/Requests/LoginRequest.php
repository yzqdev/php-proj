<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:7|max:255|confirmed'
        ];

        if (strpos(request()->path(), 'login') !== false) {
            $rules['email'] = 'required|email';
            $rules['password'] = 'required';
        }

        return $rules;
    }
}
