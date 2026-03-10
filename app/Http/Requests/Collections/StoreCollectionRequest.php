<?php

namespace App\Http\Requests\Collections;

use App\Models\Collection;
use Illuminate\Foundation\Http\FormRequest;

class StoreCollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Collection::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => trim((string) $this->input('title')),
            'description' => $this->filled('description') ? trim((string) $this->input('description')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ];
    }
}
