<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContentCategory extends Model
{
    /**
     *| Name of the corresponding table
     */
    protected $table = "content_category";

    /**
     *| Mass assignable model attributes
     */
    protected $filable = [
        'name',
        'parent_id',
        'tracking',
    ];

    /**
     *| Whether this model uses timestamps
     */
    public $timestamps = false;

    /**
     *| =======================================================================
     *| LOCAL SCOPES
     *| =======================================================================
     */

    /**
     *| Scope for filtering In Market Categories with id values > 10000
     *| Usage: $cats = (new ContentCategory)->largeIds();
     *|
     *| @param \Illuminate\Database\Eloquent\Builder $query
     *| @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLargeIds($query, $above=10000)
    {
        return $query->where('id', '>=', $above);
    }

    /**
     *| =======================================================================
     *| MODEL RELATIONSHIPS
     *| =======================================================================
     */

    /**
     *| Simple single parent inheritance
     *|
     *| @return belongsTo \Illuminate\Database\Eloquent\Model
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id')
                    ->whereNotNull('parent_id')
                    ->withDefault();
    }

    /**
     *| Non-recursive children relationship
     *|
     *| @return hasMany \Illuminate\Database\Eloquent\Model
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     *| Recursive infinite-nesting children relationship
     *|
     *| @return hasMany \Illuminate\Database\Eloquent\Model
     */
    public function childrenCategories()
    {
        return $this->hasMany(self::class, 'parent_id')
                    ->with('childrenCategories');
    }

    //highlighted categories as per https://docs.google.com/spreadsheets/d/1UtSfln0sKMdFbS_jHdflDavbacqFR8OHxkpW5kOc-J8/edit#gid=908990874
    public static $alcoholContentIds = [10170, 10171, 10172, 10173, 10174, 10175, 10176, 10177, 10178, 10179, 10180, 10181, 10182, 10183];

    public function broadsign_creative_category()
    {
        return $this->belongsTo(BroadsignCreativeCategory::class, 'id', 'content_category_id');
    }
}
