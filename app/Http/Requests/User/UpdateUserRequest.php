<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'string',
                'max:100',
            ],

            'email' => [
                'sometimes',
                'email',
                'max:150',
            ],

            'password' => [
                'sometimes',
                'nullable',
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