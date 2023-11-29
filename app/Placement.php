<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Placement extends Model
{
    protected $guarded = [];

    protected $table  = "placements";
    protected $fillable = ['zoneId','placements_publisher_id','zoneId_token','name','description','zoneType','allowedBanners','isSticky','useFineLocation','width','height','comments','channelId','refreshRate','placementRevenueType','revenuePerc','refreshRateUpdatedAt','_token','deleted_at','fallback','click_limit','monthlyImpressions','wentLiveAt','dfp_id','visit_brands'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function sellers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Seller::class, 'seller_placements')->using(SellerPlacement::class);
    }
}
