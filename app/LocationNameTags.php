<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\NameTags;

class LocationNameTags extends Model
{
    use SoftDeletes;

    protected $table = "location_name_tags";

}
