<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CriteriaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_criteria' => $this->id_criteria,
            'name' => $this->name,
        ];
    }
}
