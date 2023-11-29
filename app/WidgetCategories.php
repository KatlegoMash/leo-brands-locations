<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class WidgetCategories extends Model
{
  use SoftDeletes;
  
  //These are special categories that should not be linked to any locations as they have their own view/api
  public static $specialCategories = [
    999999, //Weather 
    118, //Waze/Traffic
    42, //Zamato
    96, // property
    121, //Daily Deals
  ];

  const REPORT_PREFIX = 'widget_';

  const WEATHER_MAPPING = [
      '01n'=>["description"=>"clear sky","icon"=>"01n",'vicinity_icon'=>'night'],
      '01d'=>["description"=>"clear sky","icon"=>"01n",'vicinity_icon'=>'sun'],

      '02n'=>["description"=>"few clouds","icon"=>"02n",'vicinity_icon'=>'partly-cloudy'],
      '02d'=>["description"=>"few clouds","icon"=>"02n",'vicinity_icon'=>'partly-cloudy'],

      '03n'=>["description"=>"scattered clouds","icon"=>"03n",'vicinity_icon'=>'cloudy'],
      '03d'=>["description"=>"scattered clouds","icon"=>"03n",'vicinity_icon'=>'cloudy'],

      '04n'=>["description"=>"broken clouds","icon"=>"04n",'vicinity_icon'=>'cloudy'],
      '04d'=>["description"=>"broken clouds","icon"=>"04n",'vicinity_icon'=>'cloudy'],

      '09n'=>["description"=>"shower rain","icon"=>"09n",'vicinity_icon'=>'rain'],
      '09d'=>["description"=>"shower rain","icon"=>"09n",'vicinity_icon'=>'rain'],

      '10n'=>["description"=>"rain","icon"=>"10n",'vicinity_icon'=>'rain'],
      '10d'=>["description"=>"rain","icon"=>"10n",'vicinity_icon'=>'rain'],

      '11n'=>["description"=>"thunderstorm","icon"=>"11n",'vicinity_icon'=>'lighting'],
      '11d'=>["description"=>"thunderstorm","icon"=>"11n",'vicinity_icon'=>'lighting'],

      '13n'=>["description"=>"snow","icon"=>"13n",'vicinity_icon'=>'snow'],
      '13d'=>["description"=>"snow","icon"=>"13n",'vicinity_icon'=>'snow'],

      '50n'=>["description"=>"mist","icon"=>"50n",'vicinity_icon'=>'fog-mist'],
      '50d'=>["description"=>"mist","icon"=>"50n",'vicinity_icon'=>'fog-mist'],
  ];


  protected $table = "widget_categories";
  
  public static $daysOfTheWeek=["Monday","Tuesday","Wednesday","Thursday","Friday", "Saturday", "Sunday"];

  public function locations()
  {
    return $this->belongsTo('App\BrandLocation');
  }

  public function linkedLocations()
  {
    return $this->belongsToMany('App\BrandLocation', 'widget_categories_brand_location', 'categoriesId', 'brand_location_id');
  }

  public function stored()
  {
    return $this->belongsTo('App\StoredWidgetCategories');
  }

  public function storedCategories()
  {
    return $this->belongsToMany('App\StoredWidgetCategories', 'widget_categories_brand_location', 'categoriesId', 'brand_location_id');
  }

  public static function getCampaignLinkedCategories($campaignId){
        return \DB::table('widget_categories_brand_location')
        ->select('brandLocationId','categoriesId')
        ->join('campaign_location','brandLocationId','=','brand_location_id')
        ->where("campaignId",$campaignId)
        ->groupBy('categoriesId')
        ->get();
  }

  private static function formatZamatoLink($string) {
     $string = preg_replace('/[^A-Za-z0-9\-]/', ' ', $string);

     $string = strtolower($string);

     return $string;
  }

  public static function getZomatoRestaurants($isPreview, $cityId){
      if(empty($cityId)){
          return false;
      }
   $trending = DB::table('widget_zamato_trending')
                           ->select('city_id','data')
                           ->where("city_id",$cityId)
                           ->first();
    $trending->data = json_decode($trending->data);
    $all = \DB::table('widget_zamato_restaurants')->get();

    foreach($trending->data->restaurants as &$value){
        $item = $all->where('restaurant_id',$value->restaurant->id)->first();
        $link = parse_url($item->url);
        $link = $link['scheme']."://".$link['host'].$link['path'];
        $value->restaurant->vicinity_link = $link;
    }

    return $trending;
  }

  public function google_place_types()
  {
    return $this->belongsToMany(GooglePlaceType::class, 'google_place_type_widget_category', 'widget_category_id', 'google_place_type_id')->withPivot('preferred')->withTimestamps();
  }

  public function serving_times()
  {
    return $this->hasMany(WidgetCategoryServingTimes::class, 'category_id', 'id');
  }

  public function servingTimes($savedTimes){
    $servingTimes = [];
    
    foreach($savedTimes as $times){
      $servingTimes[$times->day] = [
        'dayStartTime' => $times->dayStartTime, 
        'dayEndTime'=>$times->dayEndTime,
        'nightStartTime' => $times->nightStartTime, 
        'nightEndTime'=>$times->nightEndTime,
        'servingType' => $times->servingType
      ];
    }

    return $servingTimes;
  }

  /**
   * Read DB config times from `widget_categories_serving_times` table and respond with a true or false;
   * 
   * No Configs: there are no settings in the db, serve the category 24 hours
   * 24 Hours: category will be served everyday all the time
   * Day: category will be served between 06h00 and 18h00
   * Night: category will be served between 18h00 and 06h00
   * Custom Times: setting start time and end time for day and night (2 sets [dayStartTime, dayEndTime] and [nightStartTime, nightEndTime])
   *  Both dayStart and dayEnd must be set, otherwise the category will be served.
   *  if dayStart is set, dayEnd set and no night set, we will assume night is closed. 
   */

  public function shouldBeServed($savedTime){
    $today = date('l');
    $currentTime = (int) str_replace(':', '', date('H:i'));
    
    $savedDays = $savedTime->pluck('day')->toArray();

    // if($this->use_on_nearme == 0) return false; //TODO: Uncomment to enable this condition and also implement it on TemplatePinSettings line 1636 to show the correct categories on CDW

    if(sizeof($savedDays) == 0) return true;

    if(in_array("everyday", $savedDays)){
      $l = $savedTime->where('day', "everyday")->first();
      switch ($l->servingType) {
        case '24hours':
          return true;
        case 'day':
          return self::timeInRange($currentTime, '06:00', '18:00', "day");
          case 'night':
            return self::timeInRange($currentTime, '18:00', '06:00', "night");
        default:
        break;
      }
      return true;
    }
    
    if(in_array($today, $savedDays)){
      $l = $savedTime->where('day', $today)->first();

      if($currentTime >= 0600 && $currentTime < 1800){
        //Day Time
        if(empty($l->dayStartTime) && empty($l->dayEndTime)) return false;
        if(empty($l->dayStartTime) || empty($l->dayEndTime)) return true;
        return self::timeInRange($currentTime, $l->dayStartTime, $l->dayEndTime, "day");
      }else{
        //Night time
        if(empty($l->nightStartTime) && empty($l->nightEndTime)) return false;
        if(empty($l->nightStartTime) || empty($l->nightEndTime)) return true;
        return self::timeInRange($currentTime, $l->nightStartTime, $l->nightEndTime, "night");
      }

      return true;
    }

    return true;
  }

  private function timeInRange($currentTime, $startTime, $endTime, $range){
    $sTime = (int) str_replace(':', '', $startTime);
    $eTime = (int) str_replace(':', '', $endTime);
    
    
    if($range == "night"){
      $x = ($currentTime >= $sTime) && ($currentTime > $eTime);
      $y = ($currentTime < $sTime) && ($currentTime < $eTime);
      
      
      if($eTime >= 1800 && $eTime<2300){
        return ($currentTime >= $sTime) && ($currentTime < $eTime);
      }else{
        return $x || $y;
      }
    }elseif($range == "day"){
      return ($currentTime >= $sTime) && ($currentTime < $eTime);
    }

    return true;
  }

}
