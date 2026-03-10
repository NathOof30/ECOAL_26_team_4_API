<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ItemCriteria extends Pivot
{
    /**
     * The table associated with the pivot model.
     *
     * @var string
     */
    protected $table = 'item_criteria';

    /**
     * Indicates that the model does not have auto-incrementing IDs.
     */
    public $incrementing = false;

    /**
     * Disable automatic timestamps since this table doesn't have them.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_item',
        'id_criteria',
        'value',
    ];

    /**
     * The item this score belongs to.
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'id_item');
    }

    /**
     * The criterion this score belongs to.
     */
    public function criteria()
    {
        return $this->belongsTo(Criteria::class, 'id_criteria', 'id_criteria');
    }
}
