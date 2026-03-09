<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /**
     * The table name (overridden because Laravel would expect 'categories').
     *
     * @var string
     */
    protected $table = 'category';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
    ];

    /**
     * Items that have this as their primary category.
     */
    public function itemsAsCategory1()
    {
        return $this->hasMany(Item::class, 'category1_id');
    }

    /**
     * Items that have this as their secondary category.
     */
    public function itemsAsCategory2()
    {
        return $this->hasMany(Item::class, 'category2_id');
    }
}
