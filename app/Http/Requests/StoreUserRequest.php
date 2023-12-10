<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Exceptions\HttpResponseException;


class StoreUserRequest extends FormRequest
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
            'name' => 'required',
            'email' => 'required|email|unique:users,email,except,id',
            'password' => 'required|min:6',
        ];
    }

    /**
     * Overriding function to change the behaviour
     */
    public function failedValidation(Validator $validator)
    {
        $response = ResponseHelper::buildError(422, $validator->errors());
        throw new HttpResponseException(response()->json($response, 422));
    }
    
}
