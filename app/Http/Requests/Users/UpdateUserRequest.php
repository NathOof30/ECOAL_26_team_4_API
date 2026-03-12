<?php

namespace App\Http\Requests\Users;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $actor = $this->user();
        $target = $this->route('user');

        if (! $actor || ! $target) {
            return false;
        }

        $response = Gate::forUser($actor)->inspect('update', $target);

        if ($response->denied()) {
            throw new AuthorizationException($response->message() ?: 'Forbidden.');
        }

        return true;
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

        if ($this->has('avatar_hash')) {
            $data['avatar_hash'] = $this->filled('avatar_hash') ? trim((string) $this->input('avatar_hash')) : null;
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
            'avatar_hash' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
            'user_type' => 'sometimes|string|in:admin,editor,user',
        ];
    }
}
