<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'status' => $this->status,
            'collection_id' => $this->collection_id,
            'category1_id' => $this->category1_id,
            'category2_id' => $this->category2_id,
            'collection' => new CollectionResource($this->whenLoaded('collection')),
            'category1' => new CategoryResource($this->whenLoaded('category1')),
            'category2' => new CategoryResource($this->whenLoaded('category2')),
            'criteria' => CriteriaResource::collection($this->whenLoaded('criteria')),
        ];
    }
}
