<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use App\Helpers\ResponseHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool{
        // Compare current data with current logged user
        return auth()->user()->id == $this->route('user')->id;
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
            'email' => 'required|email|unique:users,email,'.auth()->user()->id,
            'password' => 'min:6',
        ];
    }
    /**
     * Overriding function to change the behaviour
     */
    public function failedValidation(Validator $validator)
    {
        $response = ResponseHelper::buildError($validator->errors());
        throw new HttpResponseException(response()->json($response, 422));
    }
}
