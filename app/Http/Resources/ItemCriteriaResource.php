<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemCriteriaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_item' => $this->id_item,
            'id_criteria' => $this->id_criteria,
            'value' => $this->value,
            'item' => new ItemResource($this->whenLoaded('item')),
            'criteria' => new CriteriaResource($this->whenLoaded('criteria')),
        ];
    }
}
