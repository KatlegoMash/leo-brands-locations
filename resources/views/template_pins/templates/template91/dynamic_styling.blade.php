<style>
    body {
  @if(in_array($loc_type,['geoip','wifi','wifi_micro','wifi_small','wifi_public']))
    background-color: {!!$template->settings->backgroundColorGeoPanel!!} !important;
  @else
    background-color: {!!$template->settings->backgroundColorFinePanel!!} !important;
  @endif
}
.marker-text{
  color: {!!$template->settings->font_color!!} !important;
  font-family:{!!str_replace('+',' ',$template->settings->fonts_all)!!} !important;
  @if($banner->height == 120)
    font-size: {!!$template->settings->font_size+3!!}px !important;
    margin-right: 7px;
  @else
    font-size: {!!$template->settings->font_size!!}px !important;
    margin-right: 5px;
  @endif
  font-weight: {!!$template->settings->font_bold ? 'bold' : 'normal'!!};
  text-decoration: {!!$template->settings->font_underline ? 'underline' : 'none'!!};
  font-style: {!!$template->settings->font_italics ? 'italic' : 'none'!!};
}
.marker-text2{
  color: {!!$template->settings->font_color!!} !important;
  font-family:{!!str_replace('+',' ',$template->settings->fonts_all)!!} !important;
  @if($banner->height == 120)
  font-size: {!!$template->settings->font_size+3!!}px !important;
  @else
  font-size: {!!$template->settings->font_size!!}px !important;
  @endif
  font-weight: {!!$template->settings->font_bold ? 'bold' : 'normal'!!};
  text-decoration: {!!$template->settings->font_underline ? 'underline' : 'none'!!};
  font-style: {!!$template->settings->font_italics ? 'italic' : 'none'!!};
  margin-left: calc({!!$template->settings->pin_size!!}px - 10px);
}

.owl-carousel .iconSize.icon-80px{
  width: 80px !important;
}
.owl-carousel .iconSize.fullWidth{
  width: {{$banner->width}}px  !important;
}
    .owl-carousel .item {
        width: {{$banner->width}}px;
        height: {{$silder}}px;
        display: block;
   @if(in_array($loc_type,['geoip','wifi','wifi_micro','wifi_small','wifi_public']))
    margin-bottom: -15px !important;
  @endif
    }
    .owl-carousel .owl-item img{
        width: auto;
    }
    @if($isPreview)
  #container{
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
  }
  #container .bannerWidth{
    width: {{$banner->width}}px;
  }
@else
  #container{
    width: {{$banner->width}}px;
    height: {{$silder}}px;
    display: block;
  }
@endif

    .owl-carousel {
        position: relative;
    }

    .owl-carousel .owl-nav button.owl-prev,
    .owl-carousel .owl-nav button.owl-next{
        outline: 0 !important;
  @if($banner->height == 120)
  margin: 0px;
  @endif

    }

    .owl-prev,
    .owl-next {
        position: absolute;
        @if($banner->height == 120)
            top: -3px;
        @else
            top: -5px;
        @endif
        height: {!!$silder!!}px;
        margin: 0;
    }

.owl-prev {
        left: 0;
  display: none !important;
    }

    .owl-next {
        right: 0;
    }

    .owl-dots{
        position: absolute;
        bottom: 0;
        width: {!!$banner->width!!}px;
        display: none;
    }
.icon-font{
  font-family:{!!str_replace('+',' ',$template->settings->fonts_all)!!} !important;
  font-size: {!!$template->settings->iconDistanceSize!!}px !important;
  color:  {!!$template->settings->iconDistanceColour!!} !important;
  display: block;
  text-align: center;
  width: 60px;

}
.weather-text-font{
  font-family:{!!str_replace('+',' ',$template->settings->fonts_all)!!} !important;
  font-size: {!!$template->settings->iconDistanceSize!!}px !important;
  fill:  {!!$template->settings->iconDistanceColour!!} !important;
  color:  {!!$template->settings->iconDistanceColour!!} !important;
  position: absolute;
  top: 60px;
  display: block;
  width: 100%;
  margin-left: -10px;
  text-align: center;
}


.bozza-icon-font{
  font-family:{!!str_replace('+',' ',$template->settings->fonts_all)!!} !important;
  font-size: {!!$template->settings->iconDistanceSize!!}px !important;
  color:  {!!$template->settings->iconDistanceColour!!} !important;
  display: block;
  text-align: center;
  width: 60px;
}



    @foreach($template->settings->carousel_images as $key=>$img)
        @if(!$img->pin)
            continue;
        @endif


    @endforeach

    img[src=""] {
       display: none;
    }

    .arrows{
        font-size: {!!$template->settings->nav_size_icon!!}px;
        color: {!!$template->settings->nav_color_icon!!};
    }

    .icon-36-font{
      font-family:{!!str_replace('+',' ',$template->settings->fonts_all)!!} !important;
      font-size: {!!$template->settings->iconDistanceSize!!}px !important;
      color:  {!!$template->settings->iconDistanceColour!!} !important;
      display: block;
      text-align: center;
      width: 60px;
      margin-right: 10px;
      margin-left: 10px;
      margin-top: -3px;
    }


    .drop-down-container{
       @if(in_array($loc_type,['geoip','wifi','wifi_micro','wifi_small','wifi_public']))
        background-color: {!!$template->settings->backgroundColorGeoPanel!!} !important;
      @else
        background-color: {!!$template->settings->backgroundColorFinePanel!!} !important;
      @endif
    }

    @if($isPreview)
    .preview-text{
      font-family: {!!str_replace('+',' ',$template->settings->fonts_all)!!} !important;
       @if(in_array($loc_type,['geoip','wifi','wifi_micro','wifi_small','wifi_public']))
        font-size: {!!$template->settings->panelGeoTextSize!!}px !important;
        color: {!!$template->settings->panelGeoTextColor!!};
        font-weight: {!!$template->settings->font_bold_geo ? 'bold' : 'normal'!!};
        text-decoration: {!!$template->settings->font_underline_geo ? 'underline' : 'none'!!};
        font-style: {!!$template->settings->font_italics_geo ? 'italic' : 'none'!!};
      @else
        font-size: {!!$template->settings->panelFineTextSize!!}px !important;
        color: {!!$template->settings->panelFineTextColor!!};
        font-weight: {!!$template->settings->font_bold_fine ? 'bold' : 'normal'!!};
        text-decoration: {!!$template->settings->font_underline_fine ? 'underline' : 'none'!!};
        font-style: {!!$template->settings->font_italics_fine ? 'italic' : 'none'!!};
      @endif

      padding-top: 0px;

      display: block !important;
      left: 4px;
      position: relative;
    }

    @else
    .distance-text{
        font-family: {!!str_replace('+',' ',$template->settings->fonts_all)!!} !important;
         @if(in_array($loc_type,['geoip','wifi','wifi_micro','wifi_small','wifi_public']))
          font-size: {!!$template->settings->panelGeoTextSize!!}px !important;
          color: {!!$template->settings->panelGeoTextColor!!};
          font-weight: {!!$template->settings->font_bold_geo ? 'bold' : 'normal'!!};
          text-decoration: {!!$template->settings->font_underline_geo ? 'underline' : 'none'!!};
          font-style: {!!$template->settings->font_italics_geo ? 'italic' : 'none'!!};
        @else
          font-size: {!!$template->settings->panelFineTextSize!!}px !important;
          color: {!!$template->settings->panelFineTextColor!!};
          font-weight: {!!$template->settings->font_bold_fine ? 'bold' : 'normal'!!};
          text-decoration: {!!$template->settings->font_underline_fine ? 'underline' : 'none'!!};
          font-style: {!!$template->settings->font_italics_fine ? 'italic' : 'none'!!};
        @endif
        @if($banner->height == 120)
        padding-top: 6px;
        @else
        padding-top: 0px;
        @endif
        display: block !important;
        left: 4px;
        position: relative;
    }

    @endif

    #near-me-button{
        display: block;
        text-align: center;
        color:#ffffff;
        background:{!!$template->settings->backgroundColorNear!!};
        @if(!empty($template->settings->nearmeBorderRadius))
          border-radius: {!!$template->settings->nearmeBorderRadius!!}px;
        @else
          border-radius:15px;
        @endif
        margin: 0 auto;
        margin-bottom: 5px;
        @if($template->settings->font_size >= 17 )
          padding: 3px 0px;
        @else
          padding: 6px 0px;
        @endif
    }

    #vicinity-pin{
        background: transparent;
        margin-left: -10px;
        @if($banner->height == 120)
          @if(($template->settings->font_size <= 40 && $template->settings->font_size >= 35))
            width: {!!$template->settings->pin_size!!}px;
            height: {!!$template->settings->pin_size+25!!}px;
            margin-left: -9px !important;
            margin-top: 10px;
          @elseif(($template->settings->font_size <= 34 && $template->settings->font_size >= 30))
            width: {!!$template->settings->pin_size!!}px;
            height: {!!$template->settings->pin_size+25!!}px;
            margin-top: 3px;
          @elseif(($template->settings->font_size <= 29 && $template->settings->font_size >= 25))
            width: {!!$template->settings->pin_size!!}px;
            height: {!!$template->settings->pin_size+20!!}px;
            margin-top: 2px;
          @elseif(($template->settings->font_size <= 24 && $template->settings->font_size >= 21))
            width: {!!$template->settings->pin_size!!}px;
            height: {!!$template->settings->pin_size+15!!}px;
            margin-top: 1px;
          @elseif(($template->settings->font_size <= 19 && $template->settings->font_size >= 15))
            width: {!!$template->settings->pin_size!!}px;
            height: {!!$template->settings->pin_size+10!!}px;
            margin-top: -2px;
          @elseif(($template->settings->font_size <= 14 && $template->settings->font_size >= 10))
            width: {!!$template->settings->pin_size!!}px;
            height: {!!$template->settings->pin_size!!}px;
            margin-top: -2px;
          @else
            width: {!!$template->settings->pin_size!!}px;
            height: {!!$template->settings->pin_size+10!!}px;
            margin-top: -2px;
          @endif
        @else
          width: {!!$template->settings->pin_size!!}px;
          height: {!!$template->settings->pin_size!!}px;
        @endif

        position: absolute;
    }



  #locationNearMePanel,#more-location,#geoipPanel{
    overflow: hidden;
    transition: max-height 0.2s ease-out;
    font-size: {!!$template->settings->font_size!!}px !important;
    @if($loc_type  == "geoip" || $loc_type  == "wifi")
      background-color: {!!$template->settings->backgroundColorGeoPanel!!} !important;
    @else
      background-color: {!!$template->settings->backgroundColorFinePanel!!} !important;
    @endif
    font-weight: 700;
    @if($banner->height == 120)
       @if(in_array($loc_type,['geoip','wifi','wifi_micro','wifi_small','wifi_public']))
        padding: 0px 15px 0px 15px;
      @else
        padding: 5px 0px 0px 5px;
      @endif
    @else
    padding: 5px 30px 0px 30px;
    @endif
    list-style-type: none;
  }

  #more-location .spacer,#locationNearMePanel .spacer,#geoipPanel .spacer{
    width: 80%;
    @if($banner->height == 120)
      margin: 0px auto 0px;
    @else
      margin: 0px auto 0px;
    @endif
    background: {!!$template->settings->lineColor!!} !important;
    height: 1px;
    clear: both;
    /* margin-bottom: 5px; */
  }

  .distance_from, .locate{
    font-family: {!!str_replace('+',' ',$template->settings->fonts_all)!!} !important;
    font-size: {!!$template->settings->panelGeoTextSize!!}px !important;
    color: {!!$template->settings->panelGeoTextColor!!};
    font-weight: {!!$template->settings->font_bold_geo ? 'bold' : 'normal'!!};
    text-decoration: {!!$template->settings->font_underline_geo ? 'underline' : 'none'!!};
    font-style: {!!$template->settings->font_italics_geo ? 'italic' : 'none'!!};
  }


  #refresh-location{
    color: {!!$template->settings->refreshColor!!} !important;
    border: 0px solid #ccc;
     @if(in_array($loc_type,['geoip','wifi','wifi_micro','wifi_small','wifi_public']))
      background-color: {!!$template->settings->backgroundColorGeoPanel!!} !important;
    @else
      background-color: {!!$template->settings->backgroundColorFinePanel!!} !important;
    @endif
  }

  .line{
    padding: 5px;
  }


</style>

<style>

.weather-details-container .details .heading{
    font-weight: normal;
    font-family: {!!$template->settings->fonts_all!!} !important;
}
.weather-heading{
  font-size: 12px !important;
  font-family: {!!$template->settings->fonts_all!!} !important;
  font-weight: normal;
  text-align: left !important;
}
.weather-main-heading{
  font-size: {!!$template->settings->weatherMainHeadingSize!!}px !important;
  color:  {!!$template->settings->weatherMainHeadingColour!!} !important;
  font-family: {!!$template->settings->fonts_all!!} !important;
  font-weight: bold;
  font-type:"";
}
.weather-right--brown{
  background-color: {!!$template->settings->backgroundColorFinePanel!!} !important;
}
.weather {
  font-size: {!!$template->settings->weatherMainHeadingSize!!}px !important;
  font-family: {!!$template->settings->fonts_all!!} !important;
}

.weather-right__temperature {
	letter-spacing: 0 !important;
}
.weather-left-icon {
  margin-left: 0 !important;
}

.fullWidth .forecast{
  font-size: 12px; 
}

.fullWidth .forecast .dailyForecastContainer{
  margin-top: -3px;
}
.fullWidth .forecast .heading{
  font-size: {!!$template->settings->weatherFullWidthTextSize ?? '12' !!}px !important;
  color: {!!$template->settings->weatherFullWidthTextColor ?? '#000000'!!} !important;
}
.fullWidth .forecast .dailyForecast .dayLabel{
  font-size: {!!$template->settings->weatherFullWidthDayLabelTextSize ?? '12' !!}px !important;
}
.fullWidth .forecast .dailyForecast img{
  margin-top: -8px;
}
.brandLocationOperatingHours {
    background: {{ $template->settings->operating_hours_background }} !important;
    padding: 5px;
    color: #1a3d56;
    border-radius: 15px;
    float: left;
    font-size: 8px;
}
.brandLocationOperatingHours span {
    color: {{ $template->settings->operating_hours_text_color ?? '#333' }} !important;
}
.collapseBtn{
    background:{!!$template->settings->backgroundColorNear!!};
    font-weight: {!!$template->settings->font_bold ? 'bold' : 'normal'!!};
    text-decoration: {!!$template->settings->font_underline ? 'underline' : 'none'!!};
    font-style: {!!$template->settings->font_italics ? 'italic' : 'none'!!};
    font-family:{!!str_replace('+',' ',$template->settings->fonts_all)!!} !important;
    color: {!!$template->settings->font_color!!} !important;
    @if(!empty($template->settings->nearmeBorderRadius))
      border-radius: {!!$template->settings->nearmeBorderRadius!!}px;
    @else
      border-radius:20px;
    @endif
}
.geoipRedesign .buttonsContainer .refreshBtn {
	background:{!!$template->settings->backgroundColorNear!!};
}

.geoipRedesign .buttonsContainer .refreshBtn .refreshBtnArrow{
  fill: {!!$template->settings->font_color!!};
}

.geoipRedesign h6{
  color: {!!$template->settings->backgroundColorNear ?? 'black' !!}
}

.dailyDealsPanel .dailyDealsItem .saveAmount {
	color: {!! $template->settings->dailyDealsSaveTextColor ?? '#ffffff' !!};
	background-color: {!! $template->settings->dailyDealsSaveBgColor ?? '#E10613' !!};
	font-size: {!! $template->settings->dailyDealsSaveFontSize ?? '12' !!}px;
}

.dailyDealsPanel .dailyDealsItem .price .current {
  color: {!! $template->settings->dailyDealsSalePriceTextColor ?? '#E10613' !!};
}

.dailyDealsPanel .dailyDealsItem .details .name {
  color: {!! $template->settings->dailyDealsProductNameTextColor ?? '#000000' !!};
  font-size: {!! $template->settings->dailyDealsProductNameFontSize ?? '12' !!}px;
}

@if($isPreview)
  .specialPanels>.bannerWidth{
    max-width: 320px;
    float: left;
    margin: auto 1rem;
  }
@endif
</style>
