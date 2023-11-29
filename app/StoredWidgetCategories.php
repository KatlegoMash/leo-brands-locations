<?php

  namespace App;

  use Illuminate\Database\Eloquent\Model;
  use Illuminate\Database\Eloquent\SoftDeletes;

  class StoredWidgetCategories extends Model
  {
    protected $table = "widget_categories_brand_location";

    public function category()
    {
      return $this->hasMany('App\WidgetCategories');
    }

    public function brandedCat()
    {
      return $this->belongsTo('App\BrandLocation');
    }

  }

?>
