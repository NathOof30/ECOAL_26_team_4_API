<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $collection = $this->resource->relationLoaded('collections')
            ? $this->resource->collections->first()
            : $this->resource->collection();

        $categories = $this->resource->relationLoaded('categories')
            ? $this->resource->categories->values()
            : $this->resource->categories()->orderBy('category.id')->get();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'status' => $this->status,
            'collection_id' => $collection?->id,
            'collection' => $collection ? new CollectionResource($collection) : null,
            'categories' => CategoryResource::collection($categories),
            'criteria' => CriteriaResource::collection($this->whenLoaded('criteria')),
        ];
    }
}
