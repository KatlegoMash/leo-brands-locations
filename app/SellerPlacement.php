<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class SellerPlacement extends Pivot
{
    protected $guarded = [];

    protected $table  = "seller_placements";
    protected $fillable = ['seller_id','placement_id','created_at', 'updated_at',];

    /**
     * @return BelongsToMany
     */
    public function sellers()
    {
        return $this->belongsToMany(Seller::class);
    }

    /**
     * @return BelongsToMany
     */
    public function placements()
    {
        return $this->belongsToMany(Placement::class);
    }
}
