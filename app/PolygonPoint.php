<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PolygonPoint extends Model
{
    use SoftDeletes;
    protected $table = "brand_locations_polygon";
    protected $fillable = ['brandLocationId','latitude','longitude','name'];

    public function location()
    {
        return $this->belongsTo('App\BrandLocation', 'id', 'brandLocationId');
    }
}
