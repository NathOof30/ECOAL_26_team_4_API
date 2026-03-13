<?php

namespace App\Http\Requests\Items;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $categoryIds = $this->input('category_ids');

        if (is_array($categoryIds)) {
            $categoryIds = array_values(array_unique(array_map('intval', $categoryIds)));
        }

        $this->merge([
            'title' => trim((string) $this->input('title')),
            'description' => $this->filled('description') ? trim((string) $this->input('description')) : null,
            'image_url' => $this->filled('image_url') ? trim((string) $this->input('image_url')) : null,
            'category_ids' => $categoryIds,
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string|max:255',
            'status' => 'nullable|boolean',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'integer|distinct|exists:category,id',
        ];
    }
}
