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
        'collection_id',
        'category1_id',
        'category2_id',
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
    public function collection()
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * An item belongs to a primary category (e.g. Mécanisme).
     */
    public function category1()
    {
        return $this->belongsTo(Category::class, 'category1_id');
    }

    /**
     * An item optionally belongs to a secondary category (e.g. Période).
     */
    public function category2()
    {
        return $this->belongsTo(Category::class, 'category2_id');
    }

    /**
     * An item has many criteria scores through the pivot table.
     */
    public function criteria()
    {
        return $this->belongsToMany(Criteria::class, 'item_criteria', 'id_item', 'id_criteria')
                    ->withPivot('value');
    }
}
