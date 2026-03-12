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
            'category1_id' => $categories->get(0)?->id,
            'category2_id' => $categories->get(1)?->id,
            'collection' => $collection ? new CollectionResource($collection) : null,
            'category1' => $categories->get(0) ? new CategoryResource($categories->get(0)) : null,
            'category2' => $categories->get(1) ? new CategoryResource($categories->get(1)) : null,
            'criteria' => CriteriaResource::collection($this->whenLoaded('criteria')),
        ];
    }
}
