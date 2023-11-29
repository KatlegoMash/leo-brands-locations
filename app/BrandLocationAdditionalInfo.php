<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BrandLocationAdditionalInfo extends Model
{
    use SoftDeletes;
    protected $table = "brand_locations_extra_info";
    //
}
