<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LocationBankBrandsSearchId extends Model
{
    protected $table = "brands_lb_search_client_ids";
    protected $fillable = ['brandId', 'lbSearchId'];

    public function brand()
    {
        return $this->hasOne(Brand::class, 'id', 'brandId');
    }
}
