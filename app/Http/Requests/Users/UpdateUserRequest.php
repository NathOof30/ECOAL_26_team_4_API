<?php

namespace App\Http\Requests\Users;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('user')) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('email')) {
            $data['email'] = mb_strtolower(trim((string) $this->input('email')));
        }

        if ($this->has('name')) {
            $data['name'] = trim((string) $this->input('name'));
        }

        if ($this->has('avatar_url')) {
            $data['avatar_url'] = $this->filled('avatar_url') ? trim((string) $this->input('avatar_url')) : null;
        }

        if ($this->has('nationality')) {
            $data['nationality'] = $this->filled('nationality') ? trim((string) $this->input('nationality')) : null;
        }

        $this->merge($data);
    }

    public function rules(): array
    {
        /** @var User $target */
        $target = $this->route('user');

        return [
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($target->id)],
            'password' => [
                'sometimes',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
            'avatar_url' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
            'user_type' => 'sometimes|string|in:admin,editor,user',
        ];
    }
}
