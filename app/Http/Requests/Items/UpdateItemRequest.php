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

        if ($this->has('title')) {
            $data['title'] = $this->filled('title') ? trim((string) $this->input('title')) : null;
        }

        if ($this->has('description')) {
            $data['description'] = $this->filled('description') ? trim((string) $this->input('description')) : null;
        }

        if ($this->has('image_url')) {
            $data['image_url'] = $this->filled('image_url') ? trim((string) $this->input('image_url')) : null;
        }

        if ($this->has('image_base64')) {
            $data['image_base64'] = $this->filled('image_base64') ? trim((string) $this->input('image_base64')) : null;
        }

        if ($this->has('status')) {
            $data['status'] = filter_var($this->input('status'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        $this->merge($data);
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string|max:255',
            'image' => 'nullable|file|image|max:5120',
            'image_base64' => 'nullable|string',
            'status' => 'sometimes|boolean',
            'category1_id' => 'sometimes|exists:category,id',
            'category2_id' => 'nullable|exists:category,id',
        ];
    }
}
