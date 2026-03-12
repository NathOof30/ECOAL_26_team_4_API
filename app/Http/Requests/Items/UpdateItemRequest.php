<?php

namespace App\Http\Requests\Items;

use Illuminate\Foundation\Http\FormRequest;

class UpdateItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('item')) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $data = [];
        $categoryIds = $this->input('category_ids');

        if ($this->has('title')) {
            $data['title'] = $this->filled('title') ? trim((string) $this->input('title')) : null;
        }

        if ($this->has('description')) {
            $data['description'] = $this->filled('description') ? trim((string) $this->input('description')) : null;
        }

        if ($this->has('image_url')) {
            $data['image_url'] = $this->filled('image_url') ? trim((string) $this->input('image_url')) : null;
        }

        if ($this->has('category_ids') && is_array($categoryIds)) {
            $data['category_ids'] = array_values(array_unique(array_map('intval', $categoryIds)));
        }

        $this->merge($data);
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string|max:255',
            'status' => 'sometimes|boolean',
            'category_ids' => 'sometimes|array|min:1',
            'category_ids.*' => 'integer|distinct|exists:category,id',
        ];
    }
}
