<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    /**
     * Disable the updated_at timestamp (table only has created_at).
     */
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'image_url',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    /**
     * An item belongs to a collection.
     */
    public function collections()
    {
        return $this->belongsToMany(Collection::class, 'collections_items', 'id_item', 'id_collection');
    }

    /**
     * Categories linked to this item.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'items_categories', 'id_item', 'id_category');
    }

    public function collection(): ?Collection
    {
        if ($this->relationLoaded('collections')) {
            return $this->collections->first();
        }

        return $this->collections()->first();
    }

    /**
     * An item has many criteria scores through the pivot table.
     */
    public function criteria()
    {
        return $this->belongsToMany(Criteria::class, 'item_criteria', 'id_item', 'id_criteria')
                    ->withPivot('value');
    }

    public function getCollectionIdAttribute(): ?int
    {
        return $this->collection()?->id;
    }
}
