<?php

namespace App\Http\Requests\ItemCriteria;

use App\Models\Item;
use Illuminate\Foundation\Http\FormRequest;

class StoreItemCriteriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $itemId = $this->input('id_item');

        if (! $this->user() || ! $itemId) {
            return false;
        }

        $item = Item::find($itemId);

        return $item ? $this->user()->can('score', $item) : false;
    }

    public function rules(): array
    {
        return [
            'id_item' => 'required|exists:items,id',
            'id_criteria' => 'required|exists:criteria,id_criteria',
            'value' => 'required|integer|in:0,1,2',
        ];
    }
}
