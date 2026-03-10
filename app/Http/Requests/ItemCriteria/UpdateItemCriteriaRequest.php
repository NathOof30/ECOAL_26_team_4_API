<?php

namespace App\Http\Requests\ItemCriteria;

use Illuminate\Foundation\Http\FormRequest;

class UpdateItemCriteriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('score', $this->route('item')) ?? false;
    }

    public function rules(): array
    {
        return [
            'value' => 'required|integer|in:0,1,2',
        ];
    }
}
