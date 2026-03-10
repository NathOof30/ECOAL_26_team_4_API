<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Criteria extends Model
{
    /**
     * The table name.
     *
     * @var string
     */
    protected $table = 'criteria';

    /**
     * The primary key column name (non-standard).
     *
     * @var string
     */
    protected $primaryKey = 'id_criteria';

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
        'name',
    ];

    /**
     * Items that are evaluated by this criterion, with their score.
     */
    public function items()
    {
        return $this->belongsToMany(Item::class, 'item_criteria', 'id_criteria', 'id_item')
                    ->withPivot('value');
    }
}
