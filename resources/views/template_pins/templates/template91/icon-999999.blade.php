@if(isset($icon['widthClass']) && $icon['widthClass'] == 'fullWidth' && isset($weatherSettings['weatherForecast']))
    <div class="d-flex " style="height: inherit;">
        <div class="mainWeatherConditions d-flex flex-column justify-content-between">
            @if(!empty($weatherSettings['icon']))
            <img class="img-fluid img-responsive icon-img" src="{{$weatherSettings['icon']}}" />
            @endif
            @if(!empty($distance))
            <span class="icon-font" >{{$distance}}<span>°C</span>  </span>
            @endif
        </div>
        <div class="forecast flex-grow-1">
            @if(isset($loc_type) && in_array($loc_type,['geoip','wifi','wifi_micro','wifi_small','wifi_public']))
                <span class="geoMessage d-flex h-100 justify-content-center align-items-center text-center fw-bold">
                    Please share location to view weather updates
                </span>
            @else
                <span class="heading">{{ __('nearme.weather_forecast') }} - <span>{{ __('weather.'.$weatherSettings['weatherForecast']['current']['id']) }}</span></span>
                <div class="d-flex dailyForecastContainer">
                    @forelse($weatherSettings['weatherForecast'] as $key => $dailyForecast)
                        @if($key !== 'current') 
                        <span class="dailyForecast d-flex flex-column justify-content-center align-items-center">
                            <span class="fw-bold text-uppercase dayLabel">{{ $dailyForecast['day'] }}</span>
                            <span class="temps">{{ $dailyForecast['temp']['day'] }}<span>°C</span></span>
                            <img style="width: 60px;" class="img-fluid" src="{{ $dailyForecast['iconUrl'] }}"/>
                        </span>
                        @endif
                    @empty
                    @endforelse
                </div>
            @endif
        </div>
    </div>
@else
<div class="weather-icon-openweathermap">
    @if(!empty($weatherSettings['icon']))
        <img src="{{$weatherSettings['icon']}}" />
    @endif
    @if(!empty($distance))
        <text class="weather-text-font" >{{$distance}} ̊ C  </text>
    @endif
</div>
@endif
