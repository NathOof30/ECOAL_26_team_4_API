<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\User::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'name' => trim((string) $this->input('name')),
            'avatar_url' => $this->filled('avatar_url') ? trim((string) $this->input('avatar_url')) : null,
            'avatar_hash' => $this->filled('avatar_hash') ? trim((string) $this->input('avatar_hash')) : null,
            'nationality' => $this->filled('nationality') ? trim((string) $this->input('nationality')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
            'avatar_url' => 'nullable|string|max:255',
            'avatar_hash' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'user_type' => 'sometimes|string|in:admin,editor,user',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
