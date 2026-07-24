<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'email' => [
                'required',
                'email',
                'max:150',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
            ],

            'role' => [
                'sometimes',
                'string',
                Rule::in([
                    'admin',
                    'user',
                ]),
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}