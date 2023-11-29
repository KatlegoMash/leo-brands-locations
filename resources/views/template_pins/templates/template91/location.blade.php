@php
    $isPreview = $isPreview ?? false;
	if($isPreview){ $icon = []; }

    app()->setLocale('en');

    if(!empty($template->settings->widgetLanguage)){
        if($template->settings->widgetLanguage == 'Afrikaans'){
            app()->setLocale('af');
        }
    }

    if($zoneId == 2343){
        app()->setLocale('af');
    }
    $listingAds = App\TemplatePinSettings::listingAds();
@endphp

@if(Config::get('app.debug'))
    <link rel="stylesheet" href="/templates/template91/nearme.css">
@else
    <link rel="stylesheet" href="https://leo.vic-m.co/templates/template91/nearme.css?v=3">
@endif
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<div class="drop-down-container">

    {{-- //Start Weather Panel--}}
    @if(($category_id == 999999 || $isPreview) && $weatherSettings)
    @php
        $current_tempreture = $weatherSettings['weatherSettings']['current']->temp;
        $current_tempreture = round($current_tempreture - 273);

        $current_min = round(collect($weatherSettings['weatherSettings']['hourly'])->min('temp') - 273);
        $current_max = round(collect($weatherSettings['weatherSettings']['hourly'])->max('temp') - 273);

        $current_feels_like = $weatherSettings['weatherSettings']['current']->feels_like;
        $current_feels_like = round($current_feels_like - 273);
        $icons_maps = \App\WidgetCategories::WEATHER_MAPPING;

        $current_wind = $weatherSettings['weatherSettings']['current']->wind_speed;
        $current_humidity = $weatherSettings['weatherSettings']['current']->humidity;
        $current_icon = $weatherSettings['iconCode'];

        $day_sections = [
          'morning'=>null,
          'afternoon'=>null,
          'evening'=>null
        ];

        foreach($weatherSettings['weatherSettings']['hourly'] as $value){
          $thisdate = date('Y-m-d', $value->dt);
          $huur = date('H',$value->dt);
          if($thisdate != date("Y-m-d")) continue;

           if($huur > 1 && $huur <= 12){

             if(!isset($day_sections['morning'])){
               $day_sections['morning']['min'] = $value;
               $day_sections['morning']['max'] = $value;
             }
             if($value->temp > $day_sections['morning']['max']->temp )
              $day_sections['morning']['max'] = $value;

             if($value->temp < $day_sections['morning']['min']->temp )
              $day_sections['morning']['min'] = $value;
           }

           if($huur > 12 && $huur <= 18 ){
             if(!isset($day_sections['afternoon'])){
               $day_sections['afternoon']['min'] = $value;
               $day_sections['afternoon']['max'] = $value;
             }
             if($value->temp > $day_sections['afternoon']['max']->temp )
              $day_sections['afternoon']['max'] = $value;

             if($value->temp < $day_sections['afternoon']['min']->temp )
              $day_sections['afternoon']['min'] = $value;
           }

           if($huur > 18  ){
             if(!isset($day_sections['evening'])){
               $day_sections['evening']['min'] = $value;
               $day_sections['evening']['max'] = $value;
             }
             if($value->temp > $day_sections['evening']['max']->temp )
              $day_sections['evening']['max'] = $value;

             if($value->temp < $day_sections['evening']['min']->temp )
              $day_sections['evening']['min'] = $value;
           }


        }

        if($isPreview){
            $day_sections['morning'] = $day_sections['evening'];
            $day_sections['afternoon'] = $day_sections['evening'];
        }
        $weatherSettings['weatherSettings']['day_tempreturs'] = $day_sections;

        if($weatherSettings['weatherSettings']['day_tempreturs']['morning']){
            $morning_min = round($weatherSettings['weatherSettings']['day_tempreturs']['morning']['min']->temp - 273);
            $morning_max = round($weatherSettings['weatherSettings']['day_tempreturs']['morning']['max']->temp - 273);
            $morning_iconCode = $weatherSettings['weatherSettings']['day_tempreturs']['morning']['max']->weather[0]->icon;
            $morning_iconCode = $icons_maps[$morning_iconCode]['vicinity_icon'];
        }

        if($weatherSettings['weatherSettings']['day_tempreturs']['afternoon']){
            $afternoon_min = round($weatherSettings['weatherSettings']['day_tempreturs']['afternoon']['min']->temp - 273);
            $afternoon_max = round($weatherSettings['weatherSettings']['day_tempreturs']['afternoon']['max']->temp - 273);
            $afternoon_iconCode = $weatherSettings['weatherSettings']['day_tempreturs']['afternoon']['max']->weather[0]->icon;
            $afternoon_iconCode = $icons_maps[$afternoon_iconCode]['vicinity_icon'];

        }
        if($weatherSettings['weatherSettings']['day_tempreturs']['evening']){
            $evening_min = round($weatherSettings['weatherSettings']['day_tempreturs']['evening']['min']->temp - 273);
            $evening_max = round($weatherSettings['weatherSettings']['day_tempreturs']['evening']['max']->temp - 273);
            $evening_iconCode = $weatherSettings['weatherSettings']['day_tempreturs']['evening']['max']->weather[0]->icon;
            $evening_iconCode = $icons_maps[$evening_iconCode]['vicinity_icon'];
        }

        $currenthour = date("H");

        if($currenthour < 18){
            $iconUrl = "/templates/pin/template91/dropdown/";
        }else{
          $iconUrl = "/templates/pin/template91/dropdown/night/";
        }

        foreach ($weatherSettings['weatherSettings']['current']->weather as $value) {
            $weatherSettings['weatherSettings']['id'] = $value->id;
        }
    @endphp

    <div id="weather-widget-container" class="">
        @if($isPreview) <div class='bannerWidth' style="height:150px; width:320px;">  @endif
        <link href="/templates/pin/template91/openweathermap-widget-right.min.css" rel="stylesheet">
        <div class="weather-right--brown">
            <div class="widget-right__layout">
                <h2 class="weather-main-heading">{{$weatherSettings['city']}} -  {{__('weather.'.$weatherSettings['weatherSettings']['id'])}}</h2>
            </div>
            <div class="weather-right__layout">
                <div id="weather-left-icon">
                    <img src="{{$iconUrl}}{{$current_icon}}.svg" width="140" height="140" alt="Weather in {{$weatherSettings['city']}}, ZA" class="weather-left-icon weather-left-icon-type1">
                </div>

                <div class="weather-right__weather">
                    <div class="weather-right-card">
                        <div class="weather-right__temperature">{{$current_tempreture}}<span>°C</span></div>
                            <table class="weather-right__table">
                              <tbody>
                                <tr class="weather-right__items">
                                  <td class="weather-right__item weather">{{__('nearme.min') }}</td>
                                  <td class="weather-right__item weather-right__feels weather">{{$current_min}}<span>°C</span></td>
                                </tr>
                                <tr class="weather-right__items">
                                  <td class="weather-right__item weather">{{__('nearme.max')}}</td>
                                  <td class="weather-right__item weather-right__feels weather">{{$current_max}}<span>°C</span></td>
                                </tr>
                              </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="weather-details-container pt-1 d-flex justify-content-between">
                    <div class="details">
                        <span class="heading">{{__('nearme.feels_like')}}</span>
                        <span class="value">{{$current_feels_like}}<span>°C</span></span>
                    </div>
                    <div class="details">
                        <span class="heading">{{__('nearme.wind')}}</span>
                        <span class="value">{{$current_wind}}m/s</span>
                    </div>
                    <div class="details">
                        <span class="heading">{{__('nearme.humidity')}}</span>
                        <span class="value">{{$current_humidity}}<span>%</span></span>
                    </div>
                </div>

                <div class="widget-right__layout pt-1">
                    <h2 class="weather-main-heading text-center flex-grow-1 m-0 p-0">{{__('nearme.today_forecast')}}</h2>
                </div>

                <div class="wrapper row">
                    @if($isPreview == true)
                        <div class="weather-row py-1 d-flex flex-row justify-content-between align-items-center">
                            <span class="weather-heading">{{__('nearme.morning_min')}} 15<span>°C</span> / {{__('nearme.max')}} 15<span>°C</span></span>
                            <img class="weather-img" src="/templates/pin/template91/dropdown/sun.svg" alt="Weather, ZA"/>
                        </div>

                        <div class="weather-row py-1 d-flex flex-row justify-content-between align-items-center">
                            <span class="weather-heading">{{__('nearme.afternoon_min')}} 15<span>°C</span> / {{__('nearme.max')}} 15<span>°C</span></span>
                            <img class="weather-img" src="/templates/pin/template91/dropdown/sun.svg" alt="Weather, ZA"/>
                        </div>

                        <div class="weather-row py-1 d-flex flex-row justify-content-between align-items-center">
                            <span class="weather-heading">{{__('nearme.evening_min')}} 15<span>°C</span> / {{__('nearme.max')}} 15<span>°C</span></span>
                            <img class="weather-img" src="/templates/pin/template91/dropdown/sun.svg" alt="Weather, ZA"/>
                        </div>
                    @else
                        @if(isset($morning_min))
                            <div class="weather-row py-1 d-flex flex-row justify-content-between align-items-center">
                                <span class="weather-heading">{{__('nearme.morning_min')}} {{$morning_min}}<span>°C</span> / {{__('nearme.max')}} {{$morning_max}}<span>°C</span></span>
                                <img class="weather-img" src="/templates/pin/template91/dropdown/{{$morning_iconCode}}.svg" alt="Weather in {{$weatherSettings['city']}}, ZA"/>
                            </div>
                        @endif
                        @if(isset($afternoon_min))
                        <div class="weather-row py-1 d-flex flex-row justify-content-between align-items-center">
                            <span class="weather-heading">{{__('nearme.afternoon_min')}} {{$afternoon_min}}<span>°C</span> / {{__('nearme.max')}} {{$afternoon_max}}<span>°C</span></span>
                            <img class="weather-img" src="/templates/pin/template91/dropdown/{{$afternoon_iconCode}}.svg" alt="Weather in {{$weatherSettings['city']}}, ZA"/>
                        </div>
                        @endif
                        @if(isset($evening_min))
                        <div class="weather-row py-1 d-flex flex-row justify-content-between align-items-center">
                            <span class="weather-heading">{{__('nearme.evening_min')}} {{$evening_min}}<span>°C</span> / {{__('nearme.max')}} {{$evening_max}}<span>°C</span></span>
                            <img class="weather-img" src="/templates/pin/template91/dropdown/night/{{$evening_iconCode}}.svg" alt="Weather in {{$weatherSettings['city']}}, ZA"/>
                        </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
        @if($isPreview == true)
            <br/><br/>
        @endif
    @endif {{-- //End Weather Panel section --}}

    {{-- //Start Zamato display settings --}}
    @if(($category_id == 42 || $isPreview) && !empty($trendingZamtoSettings))
        <div id="trending-restaurants" class="zamato res_search_widget">
            <div class="res_search_header no_search clearfix" style="">
                <img src="https://b.zmtcdn.com/images/widgets/locationicon-red.png?output-format=webp" width="32px" height="auto" class="left icon-img-zamato">
                <div class="no_search_txt left">
                    <span>Trending Restaurants <span class="res_type">{{$trendingZamtoSettings->data->restaurants[0]->restaurant->location->city ?? ''}}</span></span>
                </div>
            </div>
            <br/>
            <div id="res_results">
                @if(!empty($trendingZamtoSettings))
                    @foreach($trendingZamtoSettings->data->restaurants as $value)
                        <a target="_blank" href="{{$value->restaurant->vicinity_link}}">
                            <div class="clearfix res">
                                <div class="left">
                                    <div class="left res_img">
                                        <!-- <img src="https://b.zmtcdn.com/data/pictures/7/18126477/01c8669344151593853a6f627f3bab21_featured_v2.jpg"> -->
                                        <img src="{{$value->restaurant->photos[0]->photo->thumbUrl ?? 'https://b.zmtcdn.com/images/res_avatar_476_320_1x_new.png'}} ">
                                    </div>
                                    <div class="left res_info" style="width: 165px;">
                                        <a target="_blank" href="{{$value->restaurant->vicinity_link}}" class="res_name theme-red-foreground">
                                            <span>{{$value->restaurant->name}}</span>
                                        </a>
                                        <div class="res_loc">{{$value->restaurant->location->locality}}</div>
                                        <div class="res_type_wrap">
                                            <span class="res_type">{{$value->restaurant->cuisines}} </span>
                                            <!-- <span class="res_cuisines">{{$value->restaurant->cuisines}}</span> -->
                                        </div>
                                    </div>
                                </div>
                                <div class="rating right" style="background-color:#{{$value->restaurant->userRating->rating_color}}"> {{$value->restaurant->userRating->aggregate_rating}} </div>
                                <div class="clear"></div>
                            </div>
                        </a>
                    @endforeach
                @endif
				<br/><br/>
            </div>
        </div>
    @endif {{-- //END Zamato Settings TODO: Check zamato api on local --}}

    {{-- //START Waze/Traffic section --}}
    @if($wazeSettings && $isPreview)
        <div id="waze-widget-container">
            <iframe style="margin: 0px auto !important; height: 400px;" frameborder="0" src="https://embed.waze.com/iframe?zoom=12&lat=-34.0074053&lon=18.4600842&pin=1&desc=1" width="300" height="400"></iframe>
        </div>
        <br/><br/>
    @endif

    @if($wazeSettings && !$isPreview && $lat && $lon)
        <div id="waze-widget-container">
            <iframe class="waze-widget" style="margin: 0px auto !important; height: 400px;" frameborder="0" src="https://embed.waze.com/iframe?zoom=12&lat={{$lat}}&lon={{$lon}}&pin=1&desc=1" width="300" height="400"></iframe>
        </div>
    @endif
    {{-- //END Waze/Traffic Section, TODO: merge the above to one file and set lat and long if not set, check all possible params that can be used to send lat and long on live and local --}}

    @if($category_id == 121)
        @include('template_pins.templates.template91.partials.dailydeals-main')
    @endif

    @if( $category_id == 96  && !empty(\App\NearMeCalculator::$calculatorCategories[$category_id]))
        @include('template_pins.templates.template91.v-calculators')
    @endif
    {{-- //REVIEW: why do we need $isPreview bellow --}}
    @if($isPreview || !in_array($category_id, \App\WidgetCategories::$specialCategories))
        @if($isPreview) <div class="w-mobile-100">@endif
        @foreach($nearby_locations as $key=>$location)
        @php
            //This will allow devs on local env to preview the landing page, if approved can be sent to live as well maybe
            if(Config::get('app.debug') && !isset($location->url)){
                $catId = 4; //CategoryId will not be populated since the panel in preview is hardcoded
                $_userLoc = $_ul ?? json_encode($userLocation);
                $location->url = "http://127.0.0.1:8000/banners/landing/$banner->bannerName?_zid=$zoneId&_cid=$banner->campaignId&_loc=$location->id&_lpin=line_distance&_geoip_yn=1&_uid=".time()."&loctype=device&_d=0.5&_ul=$_userLoc&_w=$banner->width&_h=$banner->height&_ua=&_non_forwarding_click_macro=&_referer=&vicinity_id=test_5ce1ceb6-8d9f-402d-aecd-81a7d3acc541&_catId=$catId&position_icon=7";
            }

            if(!isset($location->url)) $location->url = "https://vicinity-media.com";
		@endphp
        {{-- //REVIEW if there is only one location this will be false --}}
        @if($key == 2 && isset($listingAds[$category_id]))


            @if($isPreview || Config::get('app.debug'))
                <div class="bannerWidth mt-0 pt-0 mb-2">
                    <div id="banner_1{!! $listingAds[$category_id]; !!}>" class="inlistingAd">
                        <img src="https://static.vic-m.co/banners/300.50.9233.1836b78b7598e05de4e2e0fd8f15b42e.png"/>
                        {{-- <img src="https://static.vic-m.co/banners/320.50.9244.41b41b0758cd1d055f73986b24f80069.gif"/> --}}
                        {{-- <img src="https://static.vic-m.co/banners/320.50.9195.c78325d1abc76a7a78fad3eecb4d5941.gif"/> --}}
                        {{-- <h1 class="text-center text-white">Ad Placeholder</h1> --}}
                    </div>
                </div>
            @else
                <div class="bannerWidth mt-0 pt-0 mb-2">
                    <div id="banner_1{!! $listingAds[$category_id]; !!}>" class="inlistingAd">
                        <iframe width="300" height="50" scrolling="no" frameborder="0" style="margin: 0px auto !important;width: 300px; height: 50px;" src="https://leo.vic-m.co/ooh-ad/{!! $listingAds[$category_id]; !!}?lat={{$lat}}&lon={{$lon}}"></iframe>
                    </div>
                </div>
            @endif
		@endif

        @if($key == 2 && Config::get('app.debug') && (!in_array($category_id, \App\WidgetCategories::$specialCategories)) && !empty(\App\NearMeCalculator::$calculatorCategories[$category_id]))
            @include('template_pins.templates.template91.v-calculators')
        @endif

        <div  class="bannerWidth">
            <a href="{{$location->url}}" target="_blank" style="clear:both;display: block;">
                <span class="previewIcon">
                    @include('/template_pins/templates/template91/icon-'.$category_id,array($icon=$icon,$distance="",$type="dropdown"))
                </span>

                <span class="preview-text" style="display:none;">
                    <span class="heading">{!!$location->distance ?? '500m'!!} </span>
                    <i class="fas arrows fa fa-chevron-right" style="float:right; padding-right:35px;"></i>
                    <span class="sub-text">{{ $location->locationName ?? __('This is a name') }} </span>
                </span>

                <span class="distance-text" style="display:none;">
                    <span class="heading">{!!$location->distance ?? '500m'!!} </span>
                    <i class="fas arrows fa fa-chevron-right" style="float:right; padding-right:35px;"></i>
                    <span class="sub-text">{{$location->locationName}} </span>
                </span>
                <div class="ORContainer">
                    <?php
                        $operatingHour = null;
                        if (!$isPreview) {
                            if(isset($location->getOperatingHours)){
                                $keys = (array) array_keys($location->getOperatingHours);
                                if(count($keys) > 1 && isset($location->getOperatingHours[$keys[0]])){
                                    $keys1 = array_keys((array)$location->getOperatingHours[$keys[0]]) ;
                                    if(isset($location->getOperatingHours[$keys[0]][$keys1[0]])){
                                        $operatingHour = $location->getOperatingHours[$keys[0]][$keys1[0]][0] ?? $location->getOperatingHours[$keys[0]][$keys1[0]][1];
                                    }
                                }
                            }
                        }
                    ?>
                    @if($operatingHour)
                    <div class="brandLocationOperatingHours">
                        <span>
                            {{ $operatingHour["closed_yn"] ? __('nearme.closed') : __('nearme.OPEN_NOW') }}: {{ $operatingHour["formated_trading_hours"] }}
                        </span>
                    </div>
                    @else
                        @if($isPreview)
                            <div class="brandLocationOperatingHours">
                                <span>
                                    {{__('nearme.OPEN_NOW')}}
                                    24 {{__('nearme.hours')}}
                                </span>
                            </div>
                        @endif
                    @endif
                    @if(!empty($location->rating))
                        <div class="brandLocationRating">
                            {{ $isPreview ? ($location->rating->rating ?? number_format(4.5,2))  : $location->rating  }}
                            <i class="fa fa-star"></i>
                        </div>
                    @endif
                    <div style="clear: both;"></div>
                </div>
            </a>

            @if(!empty($location->engagementIcons))
            <div class="engagementsOnListingIcons">
                @forelse($location->engagementIcons as $icon)
                    @php
                        //URL Params
                        $params = "";

                        //These params should be the same on local or live
                        $iconLinkUrl = urlencode($icon["link"]);
                        $iconName = urlencode($icon["name"]);
                        $adserver = 0;
                        $locationType = isset($loctype) ? $loctype : 'device';;
                        $isNearby = isset($_GET['_nearby']) && !empty($_GET['_nearby']) ? '&_nearby=true' : '';;
                        $iconPosition = isset($position_icon) ? $position_icon : 0;
                        $brandLocationId= $location->brand_location_id;
                        $selectedCategoryId = $category_id;

                        if($isPreview || Config::get('app.debug')){
                            /**
                             * Affected tables:
                             * 1. click_actions,
                             * 2. raw_user_profile_data_click_action_buttons_current
                             * 3. click_actions_custom_buttons_current
                             */
                            $userLocation = "$lat,$lon";
                            $vicinityId = $vicinity_id ?? "test_".time(); //Do not mod this, test_ is needed to disable tracking

                            $params = "/$campaignId/$zoneId?action={$icon["id"]}&_ul={$userLocation}&location={$brandLocationId}&url={$iconLinkUrl}&s={$adserver}&loctype={$locationType}&categoryIds={$selectedCategoryId}&position_icon={$iconPosition}&vicinity_id={$vicinityId}&icon_name={$iconName}&$isNearby";
                            $tempUrl= action('ClickActionController@getClick')."$params";
                        }else{
                            $userLocation = $_ul ?? "$lat,$lon";
                            $vicinityId = $vicinity_id; //On Live this must exist

                            $tempUrl = '';
                            $params = "/$campaignId/$zoneId?action={$icon["id"]}&_ul={$userLocation}&location={$brandLocationId}&url={$iconLinkUrl}&s={$adserver}&loctype={$locationType}&categoryIds={$selectedCategoryId}&position_icon={$iconPosition}&vicinity_id={$vicinityId}&icon_name={$iconName}&$isNearby";
                            $tempUrl= action('ClickActionController@getClick')."$params";

                            $tempUrl = str_replace("://ad2.vic-m.co/landing/click/","://leo.vic-m.co/landing/click/",$tempUrl);
                        }
                    @endphp

                    <div class="img_container">
                        <a class="icon {!!$icon["id"]!!}" data-custom="{!!$icon["custom"] or '' !!}" data-icon="{!!$icon["name"]!!}" data-action="{!!$icon["id"]!!}" target="_blank" href="{{$tempUrl}}">
                            <span class="icon-container" style="color: {{ $template->settings->iconColourEngagementsOnListing ?? 'white'}};">
                                {!!$icon["icon"]!!}
                                <div class="svg-container">
                                    @include('/customicons/'.$icon['shape'],['value'=>$icon['colors']])
                                </div>
                            </span>
                        </a>
                    </div>
                @empty
                @endforelse
            </div>
            @endif
            <div class="spacer"></div>
        </div>
        @endforeach
        @if($isPreview)</div>@endif
    @endif

</div>
