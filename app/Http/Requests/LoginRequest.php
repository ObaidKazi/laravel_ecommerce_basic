<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class LoginRequest extends FormRequest
{

    public function rules()
    {
        return [
            'email' => 'required|email|exists:users,email',
            'password'=> 'required|min:8',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Validation errors',
            'data'      => $validator->errors()
        ], 422));
    }
    public function messages()
    {
        return [
            'email.exists' => 'You are not registered with us.',
        ];
    }
}
