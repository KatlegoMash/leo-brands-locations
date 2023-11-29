<div id="geoIpPanelContainer" class="geoipRedesign {{ $isPreview ? 'isPreview ms-4' : '' }}">
    <div class="pin-message-container d-flex">
        <div class="pin justify-content-center">
            <svg version="1.1"
                xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink" width="60px" x="0px" y="0px" viewBox="0 0 40 75" enable-background="new 0 0 40 75" xml:space="preserve"  >

                <g id="Layer_2">
                <g>
                    <path fill="{{ $template->settings->distanceTopColorGeo }}" d="M6.5,18.7c0-3.7,1.5-7.1,4-9.5c2.4-2.4,5.8-4,9.5-4c3.7,0,7.1,1.5,9.5,4c2.4,2.4,4,5.8,4,9.5			c0,2.1-0.5,4.1-1.4,5.9c0.1,0,0.2,0,0.3,0c1.2,0,2,0.7,2.3,1.6c1.2-2.3,1.8-4.8,1.8-7.5c0-9.2-7.4-16.6-16.6-16.6			c-9.2,0-16.6,7.4-16.6,16.6c0,2.7,0.6,5.2,1.8,7.4c0.3-0.9,1.1-1.5,2.2-1.5c0.2,0,0.3,0,0.4,0C7,22.8,6.5,20.8,6.5,18.7z"/>
                </g>
                <circle fill="{!!$template->settings->distanceInnerCircleColorGeo!!}" cx="19.8" cy="17.7" r="5.4"/>
                </g>
                <g id="Layer_3">
                <path fill="{!!$template->settings->distanceBottomColorGeo!!}" d="M20.8,49.5L20.8,49.5c-0.8,0.5-1.8,0.2-2.2-0.5L6.2,28c-0.4-0.7-0.2-1.7,0.5-2.1l0.1,0		c0.7-0.4,1.7-0.2,2.1,0.5l12.4,20.9C21.8,48.1,21.6,49,20.8,49.5z"/>
                <path fill="{!!$template->settings->distanceBottomColorGeo!!}" d="M19.2,49.4L19.2,49.4c0.8,0.5,1.8,0.2,2.2-0.5L33.8,28c0.4-0.7,0.2-1.7-0.5-2.1l-0.1,0		c-0.7-0.4-1.7-0.2-2.1,0.5L18.6,47.3C18.2,48,18.4,49,19.2,49.4z"/>
                </g>

            </svg>
            <div class="buttonsContainer d-flex justify-content-center">
                <span class="refreshBtn mx-2  {{ $isPreview ? '' : 'getLocation' }}">
                    <svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 160 160">
                        <path class="refreshBtnArrow"
                            d="M83.8792,128.3116c-22.655,0-41.0868-18.4315-41.0868-41.0871s18.4318-41.0871,41.0868-41.0871c1.5666,0,2.8363,1.2699,2.8363,2.8363s-1.2697,2.8363-2.8363,2.8363c-19.5273,0-35.4142,15.8869-35.4142,35.4145s15.8869,35.4145,35.4142,35.4145,35.4148-15.8869,35.4148-35.4145c0-1.5664,1.2697-2.8363,2.8363-2.8363s2.8363,1.2699,2.8363,2.8363c0,22.6556-18.4318,41.0871-41.0874,41.0871Z" />
                        <path class="refreshBtnArrow"
                            d="M112.3404,47.2977c1.2901,.7448,1.2901,2.6069,0,3.3517l-14.9952,8.6575-14.9952,8.6575c-1.2901,.7448-2.9026-.1862-2.9026-1.6758V31.6586c0-1.4896,1.6126-2.4206,2.9026-1.6758l14.9952,8.6575,14.9952,8.6575Z" />
                    </svg>
                </span>
            </div>       
        </div>
        <div class="message flex-grow-1 ms-2">
            <h6 class="fw-bold">Please activate your location and click refresh below <i class="fas fa-smile"></i> </h6>
                If you don't get a pop-up to share location you may have blocked this site!<p>
                Open browser settings > Open - Site Settings > then Location > the Blocked list > then Click on the site
                name and Allow.<br>
            Exit settings and come back > click the refresh icon below and get relevant Local info.
        </div>
    </div>
    
    <div class="advert">
        @if ($isPreview || Config::get('app.debug'))
            <div class="bannerWidth mt-2 pt-0 mb-2">
                <div class="inlistingAd">
                    <img src="https://static.vic-m.co/banners/300.50.9233.1836b78b7598e05de4e2e0fd8f15b42e.png"/>
                    {{-- <img src="https://static.vic-m.co/banners/320.50.9244.41b41b0758cd1d055f73986b24f80069.gif" /> --}}
                    {{-- <img src="https://static.vic-m.co/banners/320.50.9195.c78325d1abc76a7a78fad3eecb4d5941.gif"/> --}}
                    {{-- <h1 class="text-center text-white">Ad Placeholder</h1> --}}
                </div>
            </div>
        @else
            <div class="bannerWidth mt-2 pt-0 mb-2">
                <div id="banner_1-no-location" class="inlistingAd"></div>
            </div>
        @endif
    </div>
    <div class="buttonsContainer d-flex justify-content-center align-items-center">
        <span class="collapseBtn mx-2">{{ __('nearme.collapse') }}</span>
    </div>
</div>
