<?php

namespace App\Http\Requests\Collections;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('collection')) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('title')) {
            $data['title'] = $this->filled('title') ? trim((string) $this->input('title')) : null;
        }

        if ($this->has('description')) {
            $data['description'] = $this->filled('description') ? trim((string) $this->input('description')) : null;
        }

        $this->merge($data);
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ];
    }
}
