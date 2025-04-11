<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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

            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->user), 
            ],
            'phone' => ['required', 'regex:/[0-9]*$/', 'min:2', 'max:20'],
            'position_id' => ['required', 'integer', 'exists:positions,id'],
            'photo' => [
                'nullable',
                'sometimes', // Если фото вообще передаётся
                'file', // Проверяем, что это файл
                'image', // Проверяем, что это картинка
                'dimensions:min_width=70,min_height=70',
                'max:5120'
            ],
        ];
    }
}