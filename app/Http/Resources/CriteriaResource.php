<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CriteriaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $pivotValue = $this->pivot?->value;

        return [
            'id_criteria' => $this->id_criteria,
            'name' => $this->name,
            // Compatibility keys for frontend mappers that expect score in either location.
            'score' => $pivotValue,
            'pivot' => $pivotValue !== null ? ['score' => $pivotValue] : null,
        ];
    }
}
