<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar_url' => $this->avatar_url,
            'nationality' => $this->nationality,
            'is_active' => $this->is_active,
            'user_type' => $this->user_type,
            'collection' => new CollectionResource($this->whenLoaded('collection')),
        ];
    }
}
