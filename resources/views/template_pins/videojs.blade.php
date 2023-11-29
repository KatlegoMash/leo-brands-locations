@php
$videoData['videoHtml'] = $videoData['videoHtml'] ?? false;
$videoData['title'] = str_replace("'","",$videoData['title']);

$isOOHVideo = isset($_GET['_ooh']) ? $_GET['_ooh'] : 0;
if($isOOHVideo == "false") $isOOHVideo = false;
@endphp

<link rel="stylesheet" href="https://static.vic-m.co/js/videojs/7.10.2/video-js.css"/>
<link rel="stylesheet" href="https://static.vic-m.co/js/videojs/plugins/videojs-logo.css"/>
<script type="text/javascript" src="https://static.vic-m.co/js/videojs/7.10.2/video.min.js"></script>
@if(!$isOOHVideo)
<script type="text/javascript" src="https://static.vic-m.co/js/videojs/plugins/videojs-titleoverlay.js"></script>
@endif
<script type="text/javascript" src="https://static.vic-m.co/js/videojs/plugins/videojs-logo.min.js"></script>

@if($videoData['videoHtml'])
<link href="https://static.vic-m.co/js/videojs/plugins/videojs.vast.vpaid.min.css" rel="stylesheet">
<!-- <script src="https://static.vic-m.co/js/videojs/plugins/videojs_5.vast.vpaid.min.js"></script> -->
<script src="/js/videojs/plugins/videojs_5.vast.vpaid.min.js"></script>
@endif

<style>
    body {
        margin: 0;
        padding: 0;
    }

    .video-js {
        font-size: 10px;
        color: #3187A3;
    }

    .video-js .vjs-control,
    .video-js .vjs-button {
        font-size: 10px !important;
    }

    .video-js .vjs-big-play-button {
        border-color: #2B333F;
        border-color: rgba(43, 51, 63, 0.7);
        border-radius: 0.2em;
        left: 50%;
        top: 50%;
        margin-left: -1.5em;
        margin-top: -0.75em;
        font-size: 3em !important;
    }

    .video-js:hover .vjs-big-play-button,
    .video-js .vjs-big-play-button:focus{
        border-color: #2B333F;
        border-color: rgba(43, 51, 63, 0.7);
    }

    .video-js .vjs-control-bar,
    .video-js .vjs-big-play-button,
    .video-js .vjs-menu-button .vjs-menu-content {
        background-color: #2B333F;
        background-color: rgba(43, 51, 63, 0.7);
    }

    .video-js .vjs-slider {
        background-color: #82cbe2;
        background-color: rgba(130, 203, 226, 0.5);
    }

    .video-js .vjs-volume-level,
    .video-js .vjs-play-progress,
    .video-js .vjs-slider-bar {
        background: #3187A3;
    }

    .video-js .vjs-load-progress {
        background: #bfc7d3;
        background: rgba(115, 133, 159, 0.5);
    }

    .video-js .vjs-load-progress div {
        background: white;
        background: rgba(115, 133, 159, 0.75);
    }

    .vjs-loading-spinner {
        border: 6px solid rgba(43, 51, 63, 0.7);
        border: 0.06666em solid #3187A3;
    }

    .video-js button {
        color: #3187A3 !important;
    }

    .video-js .vjs-logo-content img{
        width: 50px !important;
        height: 50px !important;
        opacity: 0.7;
        cursor: pointer;
    }

    .video-js .title-overlay-container {
        cursor: pointer;
    }
</style>
@if(!$isPreview)
    @if(isset($tag)) {!!$tag!!} @endif
    <div id="track{!!$videoData['id']!!}"></div>
@endif
<video-js
    id="{{$videoData['id']}}"
    class="video-js vjs-default-skin vjs-big-play-centered"
    controls
    @if($videoData['videoHtml']) preload="metadata" @else preload="auto" @endif
    muted="muted"
    playsinline="playsinline"
    width="{!!$banner->width.'px'!!}"
    height="{!!$banner->height.'px'!!}"
    poster="{!!$videoData['poster']!!}"
    @if($videoData['bannerType']=='landing_video' || $videoData['videoHtml']) style="width: 100% !important;"@endif
    >
    @if($videoData['videoHtml'])
    <source src="https://video.vic-m.co/vast/tiny.mp4" type="video/mp4">
    @else
    <source
        @if($isPreview) src="{!!$videoData['src']!!}?_={{time()}}" @else src="{!!$videoData['src']!!}" @endif
        type="video/{!!$videoData['format']!!}"
    />
    @endif
    <a href="{!!$videoData['clickUrl']!!}">
        @if(empty($videoData['poster']))
        <div style="
            width:{!!$videoData['width']!!}px;
            height:{!!$videoData['height']!!}px;
            background: #DBE8EE;
        ">
            <div class="error-msg info-overlay reset">
                <style>
                .error{
                    background: #DBE8EE;
                    overflow:hidden;
                    position:relative
                }
                .error-msg{
                    top:50%;left:50%;
                    position:absolute;
                    transform:translate(-50%,-50%);
                }
                .error-text{
                    text-align:start;
                    color:#3187A3;
                    background-color: #DBE8EE;
                    font:14px/1.35 'Century Gothic', CenturyGothic, AppleGothic, Arial, sans-serif;
                    padding: 8px;
                }
                .video-js {
                    background-color: #DBE8EE;
                }
                </style>
                <div class="icon reset">
                    <svg xmlns="http://www.w3.org/2000/svg" class="svg-icon svg-icon-error" viewBox="0 0 36 36" style="width:75%;height:100%;" focusable="false"><path d="M34.6 20.2L10 33.2 27.6 16l7 3.7a.4.4 0 0 1 .2.5.4.4 0 0 1-.2.2zM33.3 0L21 12.2 9 6c-.2-.3-.6 0-.6.5V25L0 33.6 2.5 36 36 2.7z"></path></svg>
                </div>
                <div class="info-container reset">
                    <div class="error-text reset-text" dir="auto">
                        <span class="break reset">Video is not supported by your browser. Visit this offer page to see more!</span>
                    </div>
                </div>
            </div>
        </div>
        @else
        <img
            src="{!!$videoData['poster']!!}"
            width="{!!$videoData['width']!!}"
            height="{!!$videoData['height']!!}"
        />
        @endif
    </a>
</video-js>
<script>
window.HELP_IMPROVE_VIDEOJS = false;
(function() {

        var videoBanner = {
            videoKey: "{!!$videoData['key']!!}",
            title: "<?php if(!$isOOHVideo) echo $videoData['title']?>",
            desciption: "<?php if(!$isOOHVideo) echo $videoData['desc']?>",
            clickUrl: "{!!$videoData['clickUrl']!!}",
            image:"{!!$videoData['poster']!!}",
            track: "audio track not yet supported",
            video: "{!!$videoData['src']!!}",
            logo: "<?php if(!$isOOHVideo) echo "https://static.vic-m.co/images/video_pin.gif"?>"
        };

    console.log(videoBanner);
    {{-- /*Video player setup*/ --}}
    var playerOptions = {
        @if($isPreview) autoplay: false, @else autoplay: true, @endif
    };

    @if($videoData['videoHtml'])
        playerOptions['preload'] = 'metadata';
    @else
        playerOptions['preload'] = 'auto';
    @endif

    var player = videojs("{!!$videoData['id']!!}", playerOptions);


    @if($isOOHVideo)
        player.controls(false);
        player.play();
    @endif

    {{-- /*Video player plugins*/ --}}
    @if($videoData['bannerType']=='banner_video')
        player.logo({
            image: videoBanner.logo,
            url: videoBanner.clickUrl,
            position: 'top-right',
            width: 50,
            height: 50,
            fadeDelay: null,
            hideOnReady: false,
            opacity: 0.7
        });

        player.titleoverlay({
            title: '<?php if(!$isOOHVideo) echo $videoData['title']?>',
            floatPosition: 'left',
            margin: '5px',
            fontSize: '1em',
            debug: false,
        });
    @elseif($videoData['videoHtml'])
        player.vastClient({
            adTagUrl: "{!!$videoData['videoHtml']->html!!}",  {{-- "https://video.vic-m.co/vast/simple-vast.xml" --}}
            playAdAlways: true,
            adCancelTimeout: 120000, {{-- Set this to a high value to allow the vast video to eventually load --}}
            adsEnabled: true,
            verbosity: 0
        });
    @endif

    {{-- /*Tracking for banner video and banner studio video*/ --}}
    @if($videoData['bannerType']=='banner_video' || $videoData['bannerType']=='banner_studio_video')
        var progress25 = 0;
        var progress50 = 0;
        var progress75 = 0;
        var eventsArray = [];

        @if($isPreview)
            var trackEvent = function(t, e={}, v=1){
                if(eventsArray.indexOf(t)===-1){
                    eventsArray.push(t);
                    sendEventRequest(t);
                }
                return;
            }

            var sendEventRequest = function(type){
                console.debug('track:',videoBanner.videoKey,'event:',type);
            }
        @else
            var trackEvent = function(t, e={}, v=1){
                if(eventsArray.indexOf(t)===-1){
                    eventsArray.push(t);
                    sendEventRequest(t);
                }
                var eventTracker = eventTracker || false;
                if(eventTracker) {
                    var e = e || {};
                    eventTracker.pushEvent({video:videoBanner.videoKey, t, e, v, created: new Date()});
                }
                return;
            }

            var sendEventRequest = function(type){
                var url = "{!!action('ClickActionController@getTrackVideoEvent')!!}/{!!$_GET['_zid']!!}/{!!$_GET['_cid']!!}/{!!$_GET['_loc']!!}";
                var d = document.getElementById("track{!!$videoData['id']!!}");
                var s = document.createElement("script");
                s.src = url+ "/" + type;
                d.appendChild(s);
            }
        @endif

        player.ready(function() {
            trackEvent('ready');
        });

        player.on('firstplay', function(e) {
            trackEvent('play');
        });

        player.on('play', function(e) {
            if(eventsArray.indexOf('complete')>-1){
                trackEvent('replay');
            } else {
                trackEvent('play');
            }
        });

        player.on('durationchange', function(e) {
            progress25 = .25 * player.duration(),
            progress50 = .5  * player.duration(),
            progress75 = .75 * player.duration();
        });

        player.on('timeupdate', function(e) {
            var progress = player.currentTime();
            !1 !== progress25 && progress > progress25 && -1 === eventsArray.indexOf("progress25")
            ? (trackEvent('progress25'))
            : !1 !== progress50 && progress > progress50 && -1 === eventsArray.indexOf("progress50")
            ? (trackEvent('progress50'))
            : !1 !== progress75 && progress > progress75 && -1 === eventsArray.indexOf("progress75") && (trackEvent('progress75'));
        });

        player.on('ended', function(e) {
            trackEvent('complete');
            console.log('done why I am replaying');
            if (player.titleoverlay.showOverlay && typeof player.titleoverlay.showOverlay === 'function'){
                player.titleoverlay.showOverlay();
            }
        });

        player.on('pause', function(e) {
            {{-- /* pause event is emmited before ended event, so this cancels that out */ --}}
            if(player.currentTime() < (player.duration() * 0.98)){
                trackEvent('pause');
            }
        });

        player.on('seeking', function(e) {
            trackEvent('seek');
        });

        player.on('volumechange', function(e) {
            trackEvent('volume', {volume: Math.round(player.volume() * 100,0)}, player.volume());
            if(player.muted()){
                trackEvent('mute');
            }
        });

        player.on('error', function(e) {
            trackEvent('error');
        });

        player.on('stalled', function(e) {
            trackEvent('error');
        });

        player.on('waiting', function(e) {
            //trackEvent('buffer');
        });

        player.on('canplay', function(e) {
            {{-- /* trackEvent('viewable'); */ --}}
        });

        player.on('fullscreenchange', function(e) {
            trackEvent('fullscreen');
        });

        player.on('resolutionchange', function(e) {
            trackEvent('resolutionchange');
        });

        player.on('resize', function(e) {
            //trackEvent('resize');
        });

        var onClick = function(){
            @if($isPreview)
            alert('Clicks are not processed or recorded in preview mode!');
            @else
            window.open(videoBanner.clickUrl);
            @endif
        };

        player.on('click', function(e) {
            e.target.tagName === 'VIDEO' ? onClick() : false;
        });

        var titleBar = document.querySelector("#{!!$videoData['id']!!}-title-overlay-container");
        if(titleBar){
            titleBar.addEventListener('click', onClick, false);
            titleBar.addEventListener('touch', onClick, false);
        }

    {{-- /*Tracking for html video (VAST tag) */ --}}
    @elseif($videoData['videoHtml'])
        var adEnded = function(){
            {{-- /* If Outstream, we can hide the player from the view */--}}
            player.on('play', function(){
                player.vast.disable();
                player.dispose();
            });
            {{-- /* else, play the main content */--}}
        };

        player.on('vast.reset', function(){
            console.log('vast.reset');
        });

        player.on('vast.firstPlay', function(){
            console.log('vast.firstPlay');
        });

        player.on('error', function(){
            console.log('error');
        });

        player.on('vast.adStart', function(){
            console.log('vast.adStart');
        });

        player.on('vast.adsCancel', function(){
            console.log('vast.adsCancel');
        });

        player.on('vast.adError', function(){
            console.log('vast.adError');
        });

        player.on('vast.adStart', function(){
            console.log('vast.adStart');
        });

        player.on('vast.adEnd', function(){
            console.log('vast.adEnd');
            adEnded();
        });

        player.on('vast.adsCancel', function(){
            console.log('vast.adsCancel');
        });

        player.on('vpaid.AdSkipped', function(){
            console.log('vpaid.AdSkipped');
        });

        player.on('vpaid.AdImpression', function(){
            console.log('vpaid.AdImpression');
        });

        player.on('vpaid.AdStarted', function(){
            console.log('vpaid.AdStarted');
        });

        player.on('vpaid.AdVideoStart', function(){
            console.log('vpaid.AdVideoStart');
        });

        player.on('vpaid.AdPlaying', function(){
            console.log('vpaid.AdPlaying');
        });

        player.on('vpaid.AdPaused', function(){
            console.log('vpaid.AdPaused');
        });

        player.on('vpaid.AdVideoFirstQuartile', function(){
            console.log('vpaid.AdVideoFirstQuartile');
        });

        player.on('vpaid.AdVideoMidpoint', function(){
            console.log('vpaid.AdVideoMidpoint');
        });

        player.on('vpaid.AdVideoThirdQuartile', function(){
            console.log('vpaid.AdVideoThirdQuartile');
        });

        player.on('vpaid.AdVideoComplete', function(){
            console.log('vpaid.AdVideoComplete');
        });

        player.on('vpaid.AdClickThru', function(){
            console.log('vpaid.AdClickThru');
        });

        player.on('vpaid.AdUserAcceptInvitation', function(){
            console.log('vpaid.AdUserAcceptInvitation');
        });

        player.on('vpaid.AdUserClose', function(){
            console.log('vpaid.AdUserClose');
        });

        player.on('vpaid.AdUserMinimize', function(){
            console.log('vpaid.AdUserMinimize');
        });

        player.on('vpaid.AdError', function(){
            console.log('vpaid.AdError');
        });

        player.on('vpaid.AdVolumeChange', function(){
            console.log('vpaid.AdVolumeChange');
        });

        player.on('vpaid.pauseAd', function(){
            console.log('vpaid.pauseAd');
        });

        player.on('vpaid.resumeAd', function(){
            console.log('vpaid.resumeAd');
        });

        player.on('vpaid.adEnd', function(){
            console.log('vpaid.adEnd');
        });

        player.on('vpaid.AdSkippableStateChange', function(){
            console.log('vpaid.AdSkippableStateChange');
        });

        player.on('vpaid.AdStopped', function(){
            console.log('vpaid.AdStopped');
        });
    @endif
})();
</script>
