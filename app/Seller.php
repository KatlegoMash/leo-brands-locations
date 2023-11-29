<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seller extends Model
{
    protected $guarded = [];

    protected $table  = "sellers";
    protected $fillable = ['seller_id','name','domain','seller_type','is_passthrough','comment','is_confidential'];

    /**
     * @return BelongsToMany
     */
    public function placements(): BelongsToMany
    {
        return $this->belongsToMany(Placement::class, 'seller_placements')->using(SellerPlacement::class);
    }

}
