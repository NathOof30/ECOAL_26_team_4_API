<?php

namespace App\Http\Requests\Categories;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('category')) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('title')) {
            $this->merge([
                'title' => $this->filled('title') ? trim((string) $this->input('title')) : null,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
        ];
    }
}
