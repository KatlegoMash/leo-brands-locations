@php
//Use this setting so on local when using test publisher, locations can be loaded from localhost when a category is clicked
$engamentRecordingDomain = Config::get('app.debug') ? "http://127.0.0.1:8000": "https://leo.vic-m.co";

switch ($template->settings->navigation_arrows_icon) {
    case 'none':
        $arrowStyleLeft = "";
        $arrowStyleRight = "";
        break;
    case 1:
        $arrowStyleLeft = "chevron-left";
        $arrowStyleRight = "chevron-right";
        break;
    case 2:
        $arrowStyleLeft = "caret-left";
        $arrowStyleRight = "caret-right";
        break;
    case 3:
        $arrowStyleLeft = "angle-left";
        $arrowStyleRight = "angle-right";
        break;
    case 4:
        $arrowStyleLeft = "arrow-left";
        $arrowStyleRight = "arrow-right";
        break;
    case 5:
        $arrowStyleLeft = "chevron-circle-left";
        $arrowStyleRight = "chevron-circle-right";
        break;
    case 6:
        $arrowStyleLeft = "caret-square-left";
        $arrowStyleRight = "caret-square-right";
        break;
    case 7:
        $arrowStyleLeft = "long-arrow-alt-left";
        $arrowStyleRight = "long-arrow-alt-right";
        break;
    case 8:
        $arrowStyleLeft = "arrow-alt-circle-left";
        $arrowStyleRight = "arrow-alt-circle-right";
        break;
    default:
        $arrowStyleLeft = "chevron-left";
        $arrowStyleRight = "chevron-right";
        break;
}
app()->setLocale('en');

if(!empty($template->settings->widgetLanguage)){
  if($template->settings->widgetLanguage == 'Afrikaans'){
    app()->setLocale('af');
  }
}

if($zoneId == 2343){
  app()->setLocale('af');
}

$template->settings->auto_play_icon;

if (empty($template->settings->auto_play_icon)) {
    $template->settings->auto_play_icon = 'true';
}

$delimeter = "~";
foreach ($template->settings->carousel_images as $key => $img) {
    if (!isset($img->path) || $img->path == "/templates/pin/template42/Svg.svg") {
        $path = "/templates/default-pins/".$banner->width."x".$banner->height.".png";
        $template->settings->carousel_images[$key]->path = $path;
    } else {
        $path = $img->path;
    }

    $pin = false;
    $text = "";
    $id = "default";
    if (substr($path, strlen($path) - 3) == "svg") {
        $pin = true;
        $id = $img->id;
        $text = $template->settings->{"distance_value_text$delimeter" . $img->id} ?? $template->settings->distance_value_text;
    }

    $template->settings->carousel_images[$key]->pin = $pin;
    $template->settings->carousel_images[$key]->text = $text;
}

if ($banner->height == 100) {
  $silder = 60;
}

if($banner->height == 120){
  $silder = 80;
}

$weatherSettings = collect($template->settings->widget_strip_settings)->where('category_id',999999)->first();
$wazeSettings = collect($template->settings->widget_strip_settings)->where('category_id', 118)->first();
$dailyDealsSettings = collect($template->settings->widget_strip_settings)->where('category_id', 121)->first();
$trendingZamtoSettings = $template->settings->zamato_trending;

$icons = [];
$loc_type = $template->settings->locsType;

//@devs leave this here since we need both in the location.blade and this index file, you can change it here else
$listingAds = App\TemplatePinSettings::listingAds();
@endphp

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="authoring-tool" content="Adobe_Animate_CC">
	<title>Vicinity Carousel</title>
  <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="stylesheet" href="https://static.vic-m.co/templates/pin/template91/owl.carousel.css">
  <link rel="stylesheet" href="https://static.vic-m.co/templates/pin/template91/owl.theme.default.min.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" rel="stylesheet" type="text/css">
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Material+Icons|{!!$template->settings->fonts_all!!}">
  @if(Config::get('app.debug'))
    <link rel="stylesheet" href="/templates/template91/nearme.css">
  @else
    <link rel="stylesheet" href="https://static.vic-m.co/templates/template91/nearme.css?v=4">
  @endif
  @include('template_pins.custom_fonts')
  @include('template_pins.templates.template91.dynamic_styling')
</head>
<body>
  <div id="container">
    <div class=" {{$isPreview ? 'slideContainer' : 'pinSlideContainer'}}">
      <a id="near-me-button">
          <span class="marker-text">{!!$template->settings->distance_value_text!!}</span>
          <svg id="vicinity-pin"  version="1.1"
            xmlns="http://www.w3.org/2000/svg"
            xmlns:xlink="http://www.w3.org/1999/xlink" width="300" height="250" x="0px" y="0px" viewBox="0 0 40 75" enable-background="new 0 0 40 75" xml:space="preserve"  >

            <g id="Layer_2">
              <g>
                <path fill="{!!$template->settings->nearTopPin!!}" d="M6.5,18.7c0-3.7,1.5-7.1,4-9.5c2.4-2.4,5.8-4,9.5-4c3.7,0,7.1,1.5,9.5,4c2.4,2.4,4,5.8,4,9.5			c0,2.1-0.5,4.1-1.4,5.9c0.1,0,0.2,0,0.3,0c1.2,0,2,0.7,2.3,1.6c1.2-2.3,1.8-4.8,1.8-7.5c0-9.2-7.4-16.6-16.6-16.6			c-9.2,0-16.6,7.4-16.6,16.6c0,2.7,0.6,5.2,1.8,7.4c0.3-0.9,1.1-1.5,2.2-1.5c0.2,0,0.3,0,0.4,0C7,22.8,6.5,20.8,6.5,18.7z"/>
              </g>
              <circle fill="{!!$template->settings->nearCirclePin!!}" cx="19.8" cy="17.7" r="5.4"/>
            </g>
            <g id="Layer_3">
              <path fill="{!!$template->settings->nearBottomPin!!}" d="M20.8,49.5L20.8,49.5c-0.8,0.5-1.8,0.2-2.2-0.5L6.2,28c-0.4-0.7-0.2-1.7,0.5-2.1l0.1,0		c0.7-0.4,1.7-0.2,2.1,0.5l12.4,20.9C21.8,48.1,21.6,49,20.8,49.5z"/>
              <path fill="{!!$template->settings->nearBottomPin!!}" d="M19.2,49.4L19.2,49.4c0.8,0.5,1.8,0.2,2.2-0.5L33.8,28c0.4-0.7,0.2-1.7-0.5-2.1l-0.1,0		c-0.7-0.4-1.7-0.2-2.1,0.5L18.6,47.3C18.2,48,18.4,49,19.2,49.4z"/>
            </g>

          </svg>
          <span class="marker-text2">{!!$template->settings->from_location!!}</span>
      </a>

      {{-- //Close nearme button Near <VICPIN /> ME --}}
      <div class="owl-carousel owl-theme">
          @foreach($template->settings->widget_strip_settings as $key=>$icon)
          <div class="iconSize {{ isset($icon['widthClass']) ? $icon['widthClass'] : 'icon-80px' }}">
              @if(View::exists('/template_pins/templates/template91/icon-'.$icon['category_id']))
                  <?php $campaignId = isset($icon['campaignId']) ? $icon['campaignId'] : $banner->campaignId; ?>
                      @if($isPreview)
                        <div class="item icon-container">
                      @else
                        <div class="item icon-container" onclick="showPanel('{{$icon['category_id']}}',{{$icon['brandLocationId']}},{{$key + 1}},{{$campaignId}});">
                      @endif
                      @include('/template_pins/templates/template91/icon-'.$icon['category_id'],array($icon=$icon,$distance=$icon['distance']))
                  </div>
              @endif
          </div>
          @endforeach
      </div>

      @if($isPreview) </div> @endif
      {{-- //End preview if statement to set coraousel height --}}

      @if($isPreview)
        @if(in_array($loc_type,['geoip','wifi','wifi_micro','wifi_small','wifi_public']))
          <div class="bannerWidth">
            @include('template_pins.templates.template91.partials.geoip')
          </div>
        @else
          <div id="locationNearMePanel" style="flex-grow:1;">
            @include('template_pins/templates/template91/location',array('nearby_locations'=>$nearby_locations,'category_id'=>1,'isPreview'=>true, 'lat' => 0, 'lon' =>0))
            <div class="specialPanels">
              @include('template_pins/templates/template91/v-calculators',array('calcHeight' => '400px','isPreview'=>true, 'lat' => 0, 'lon' =>0))
              @if($dailyDealsSettings)
              <div class="bannerWidth">
                @include('template_pins/templates/template91/partials/dailydeals-main', ['isPreview' => true])
              </div>
              @endif
            </div>
          </div>
        @endif

          <div class="bannerWidth">
            <div id="loader" class="fa-5x" style="text-align: center;font-size: 3em; margin: 30px 0;color:{!!$template->settings->loaderColor!!};">
                <i class="fa fa-refresh fa-spin"></i>
            </div>
          </div>
      {{-- //NOT Preview == live --}}
      @else
          @if(count($template->settings->widget_strip_settings))
              @if(in_array($loc_type,['geoip','wifi','wifi_micro','wifi_small','wifi_public']))
              @include('template_pins.templates.template91.partials.geoip')
              {{-- //Location is enabled/provided --}}
              @else
                <div id="more-location" >
                  <div id="loader" class="fa-5x" style="text-align: center; margin: 50px 0;color:{!!$template->settings->loaderColor!!};">
                        <i class="fa fa-refresh fa-spin"></i>
                  </div>
                  {{-- //This will contain from the ajax and will render location.blade.file contents --}}
                  <div id="container-more" onscroll="storeListingScroll()"></div>
                  <span class="collapseBtn">{{__('nearme.collapse')}}</span>
                </div>
              @endif
          @endif
      @endif
        </div>


  <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
	<script type="text/javascript" src="//static.vic-m.co/templates/pin/template59/OwlCarousel2-2.3.4/dist/owl.carousel.min.js"></script>
	<script type="text/javascript">
    //Caroasel Section - NearMe Categories slider
    var caroasel;
		$(document).ready(function(){
      caroasel = $('.owl-carousel').owlCarousel({
        loop: true,
        autoWidth: true,
        lazyLoad: true,
        items: 4,
        autoplay: {{ $template->settings->auto_play_icon }},
        autoplayTimeout: {{ $template->settings->loop_speed * 200 }},
        nav: true,
        autoplayHoverPause: true,
        navText: ["<i class='arrows fa fa-{!!$arrowStyleLeft!!}'></i>","<i class='arrows fa fa-{!!$arrowStyleRight!!}'></i>"],
      });

    $('.owl-next').click(function() {
      storeCarouselSwipe("right arrow click");
    });

    var owl = $('.owl-carousel');
    owl.on("dragged.owl.carousel", function (event) { storeCarouselSwipe(event.relatedTarget['_drag']['direction']); });

    /*FN that sends the swipes to the back-end for tracking and storage */
    function storeCarouselSwipe(action) {
      $.ajax({
        url: '{!! $engamentRecordingDomain !!}/banners/near-me-swipe/swipe',
        type: 'GET',
        data: {
          'swipe-direction': action,
          'location_type': "{{$_GET['_loctype'] ?? 'geoip'}}",
          'campaignId': {{$banner->campaignId}},
          'zoneId': {{$zoneId}},
          'lat_lon': "{{$_GET['_ul'] ?? '0,0'}}",
          'vicinity_id': "{{$_GET['_vicmid'] ?? ''}}",
        }
      })
      return false;
    }

    $(".collapseBtn").click(function(){
      $("#more-location").hide();
      $('#geoIpPanelContainer').hide();
      caroasel.trigger('play.owl.autoplay');
      var data = [
        {"key":'#{{$_GET['build'] ?? "test"}}','value':'120px', 'name':'vicinity'},
        {"key":'#{{$_GET['build'] ?? "test"}} iframe','value':'120px', 'name':'vicinity'},
      ]

      @if(!Config::get('app.debug'))
        window.parent.postMessage(data,"*");
      @else
        console.log('In Production we will fire the postMessage fb with data')
        console.dir(data)
      @endif

      let category_id = $("#near-me-button").attr("data-category_id");
      let brandLocationId = $("#near-me-button").attr("data-brandLocationId");
      let pos = $("#near-me-button").attr("data-pos");

      //TODO: Confirm if this is storing on local as expected
      $.ajax({
        url: '{!! $engamentRecordingDomain !!}/banners/near-me/close',
        type: 'GET',
        data: {
          'categoryIds': category_id,
          'brand_location_id': brandLocationId,
          'position_icon': pos,
          'location_type': '{{$_GET['_loctype'] ?? 'geoip'}}',
          'campaignId': {{ $banner->campaignId }},
          'zoneId': {{$zoneId}},
          'lat_lon':'{{$_GET['_ul'] ?? '0,0'}}',
          'vicinity_id':'{{$_GET['_vicmid'] ?? ''}}',
        }
      })
      return false;
    });

    caroasel.on('dragged.owl.carousel', function(event) { caroasel.trigger('stop.owl.autoplay'); })
  });

    /*REVIEW: This function is never triggered, it is not assosicated with element, has no listeners as well */
    /* Same function that was in the location file and was giving an error. even though it was not triggered */

  function storeBannerListingClick() {
    let brandLocationId = $("#near-me-button").attr("data-brandLocationId");
    let pos = $("#near-me-button").attr("data-pos");
    let cid = $("#bannerInListing").attr("data-cid");
    let category_id = $("#near-me-button").attr("data-category_id");

    $.ajax({
      url: '{!! $engamentRecordingDomain !!}/banners/near-me-banner-in-listing',
      type: 'GET',
      data: {
          'categoryIds':category_id,
          'brand_location_id':brandLocationId,
          'position_icon':pos,
          'campaignId':cid,
          'location_type': '{{$_GET['_loctype'] ?? 'geoip'}}',
          'zoneId':'{{$_GET['_zid'] ?? 0 }}',
          'lat_lon':"{{$_GET['_ul'] ?? '0,0'}}",
          'vicinity_id':'{{$_GET['_vicmid'] ?? ''}}',
      }
    }).done(function() {
      @if(!Config::get('app.debug'))
        window.open("https://ad.doubleclick.net/ddm/trackclk/N1251480.2703200VICINITY/B26226326.310217012;dc_trk_aid=502803467;dc_trk_cid=155581068;dc_lat=;dc_rdid=;tag_for_child_directed_treatment=;tfua=;ltd=");
      @else
        console.log("We will hit the ad.doubleclick.net url")
      @endif
    });
    return true;
  }

  var scroll = [];
  function storeListingScroll() {
    var category_id = $("#near-me-button").attr("data-category_id");
    if(scroll.indexOf(category_id) == -1){
      let pos = $("#near-me-button").attr("data-pos");
      scroll.push(category_id);
      $.ajax({
        url: '{!! $engamentRecordingDomain !!}/banners/near-me-scroll',
        type: 'GET',
        data: {
          'categoryIds': category_id,
          'position_icon': pos,
          'campaignId': {{$banner->campaignId}},
          'zoneId': {{$zoneId}},
          'lat_lon':"{{$_GET['_ul'] ?? '0,0'}}",
          'vicinity_id':'{{$_GET['_vicmid'] ?? ''}}',
        }
      }).done(function(){
        @if(Config::get('app.debug'))
          console.log("storeListingScroll fired successfully to backed")
        @endif
      });
      return false;
    }
  }

  /* REVIEW: this was not getting recorded in the db, and brand_location_id was static, changed it to dynamic */
  $('.getLocation').on('click', () => {
    var data = [
      {"key":'#{{$_GET['build'] ?? "test"}}','value':'reload', 'name':'vicinity'}
    ];

    //These values were undefined since the more-locations div isn't part of the panel - screenshot attached to the task, so this query was failing on local
    let category_id = $("#near-me-button").attr("data-category_id");
    let pos = $("#near-me-button").attr("data-pos");
    let brandLocationId = $("#near-me-button").attr("data-brandLocationId");

    $.ajax({
      url: '{!! $engamentRecordingDomain !!}/banners/near-me/refresh',
      type: 'GET',
      data: {
        'categoryIds': category_id,
        'brand_location_id': brandLocationId,
        'position_icon': pos,
        'location_type': "{{$_GET['_loctype'] ?? 'geoip'}}",
        'campaignId': {{$banner->campaignId}},
        'zoneId': {{$zoneId}},
        'lat_lon': "{{$_GET['_ul'] ?? '0,0'}}",
        'vicinity_id': "{{$_GET['_vicmid'] ?? ''}}",
      }
    }).done(function(){
      @if(!Config::get('app.debug'))
        window.parent.postMessage(data,"*");
      @else
        console.log("Call postMessage on live", data)
      @endif

      return false;
    });
  })

  function showPanel(category_id,brandLocationId,pos,campaign){
    $("#container-more").html('');
    $("#loader").show();
    $("#more-location").show();
    $('#geoIpPanelContainer').show();
    caroasel.trigger('stop.owl.autoplay');

    var data = [
      {"key":'#{{$_GET['build'] ?? "test"}}','value':'480px', 'name':'vicinity'},
    ];

    @if(!Config::get('app.debug'))
      window.parent.postMessage(data,"*");
    @else
      console.log("Fire PostMessage on showPanel when live")
    @endif

    $.ajax({
      url: "{!! $engamentRecordingDomain !!}/widget-icon-clicks/store-click",
      type: 'GET',
      crossDomain: true,
      data: {
        'categoryIds': category_id,
        'brand_location_id': brandLocationId,
        'position_icon': pos,
        'location_type': "{{$_GET['_loctype'] ?? 'geoip'}}",
        'city_id': "{{$_GET['city_id'] ?? ''}}",
        'campaignId': campaign,
        'zoneId': {!! $zoneId !!},
        'lat_lon': "{{$_GET['_ul'] ?? '0,0'}}",
        'vicinity_id': "{{$_GET['_vicmid'] ?? ''}}",
        'clickUrl': "{{$clickURL}}",
      }
    }).done(function(res){
      $("#loader").hide();
      $("#container-more").html(res);

      $("#near-me-button").attr("data-category_id", category_id);
      $("#near-me-button").attr("data-brandLocationId", brandLocationId);
      $("#near-me-button").attr("data-pos", pos);

      //Store the attributes on the nearmebtn because container-more isn't present when location is disabled
    });

    //GeoIP set Iframe
    @if(!\Config::get('app.debug'))
     showGeoIPAdvert(category_id);
    @endif
  }

  function showGeoIPAdvert(category_id){
    let adsMappingArray = @json($listingAds);
    if(adsMappingArray[category_id]){
      let srcString = "https://leo.vic-m.co/ooh-ad/"+adsMappingArray[category_id];
      let iFrameHtml = `<iframe width='300' height='50' scrolling='no' frameborder='0' style='margin: 0px auto !important;width: 300px; height: 50px;' src='${srcString}?lat=0&lon=0'></iframe>`
      $('#banner_1-no-location').html(iFrameHtml)
    }
  }

  function trackDailyDealsIconClicks(category_id){
    let campaignId = "{{ $banner->campaignId }}";
    let userLocation = "{{implode(',', $userLocation)}}";
    let vicinity_id = '{{$_GET['_vicmid'] ?? ''}}';
    let zoneId = "{!! $zoneId !!}"

    let itemsInView = [];

    $(".card-title").each((index, object) => {
      itemsInView.push(object.innerHTML)
    })

    $.ajax({
        url: "{!! $engamentRecordingDomain !!}/widget-icon-clicks/daily-deals-tracker",
        type: 'GET',
        data: {
          "itemsInView": itemsInView.join(", "),
          category_id, 
          campaignId,
          userLocation, 
          vicinity_id,
          zoneId
        }
    })

  }

	</script>
</body>
