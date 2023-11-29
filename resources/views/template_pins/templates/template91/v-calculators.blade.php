@php
$enabledCalculators = [];

if (!empty($template->settings->enableCalculators)) {
    $enabledCalculators = explode(',', $template->settings->enableCalculators);
}

$calcConfigs = [];

if (!empty($enabledCalculators)) {
    if(!$isPreview){
        foreach ($enabledCalculators as $key=>$calcId) {
            $calcConfigs[$calcId] = \App\NearMeCalculator::getCalculators($category_id, $calcId);
            if(empty($calcConfigs[$calcId])) {
                unset($enabledCalculators[$key]);
                unset($calcConfigs[$calcId]);
            }
        }
    }else{
        $allCalculators = \App\NearMeCalculator::getCalculators();
        foreach($allCalculators as $calculator){
            foreach($calculator as $calcId => $config){
                if(in_array($calcId, $enabledCalculators)) $calcConfigs[$calcId] = $config;
            }
        }
    }
}

$vicId = "";
if(!empty($vicinityId)) $vicId = $vicinityId;
if(empty($vicId) && !empty($vicinity_id)) $vicId = $vicinity_id;
if(empty($vicId)) $vicId = "not-set-".time();

$vicinityData = [
    'vicinityId' => $vicId,
    'locationType' => $locationType ?? 'no-location-type',
    'campaignId' => $campaignId,
    'zoneId' => $zoneId,
    'brandLocationId' => $brand_location_id ?? 'no-brand-location-id', //This is not always present
    'category_id' => $category_id ?? 0,
    'lat' => $lat,
    'lon' => $lon,
    'position_icon' => $position_icon ?? null,
    'userAgent' => \Request::server('HTTP_USER_AGENT'),
    'ipAddress' => \Request::ip(),
];

$apiURL = Config::get('app.debug') ? "http://127.0.0.1:8000" : "https://leo.vic-m.co";

$calculatorHeight = $calcHeight ?? $template->settings->calculatorHeight.'px'; //in locations file do not set calcHeight so it can be pulled from db
@endphp

<script type="text/javascript">
    var allCalculatorConfigs = @json($calcConfigs);
    var vicinityData = @json($vicinityData);
    var apiURL = @json($apiURL);
    var calcHeight = @json($calculatorHeight)
</script>


@if(!empty($calcConfigs))

{{-- //Styles override using nearme general configs --}}
<style>
.calcContainer .tabs .vmt__nav {
	background-color: {{ $template->settings->backgroundColorNear}} !important;
}
.calcContainer .tabs .vmt__nav__items .vmt__nav__item, .form-control label, .fa-question-circle {
    color: {{ $template->settings->backgroundColorNear}};
}
.calcContainer .tabs .vmt__nav__items .vmt__nav__item, .displayResults p {
	color: {{ $template->settings->font_color}};
    font-family: {{ str_replace('+',' ',$template->settings->fonts_all) }};
    font-size: {{ $template->settings->font_size}};
}

.calcContainer .tabs .vmt__nav__items .vmt__nav__item, .form-control label{
    color: #000000;
    /* opacity: 1 !important; */
}

.calcContainer .flipper .leftBtn, .rightBtn{
    display: inline-block;
    font-weight: 400;
    line-height: 1.5;
    color: #212529;
    text-align: center;
    text-decoration: none;
    vertical-align: middle;
    cursor: pointer;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    background-color: transparent;
    border: 1px solid transparent;
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    border-radius: 0.25rem;
    -webkit-transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, -webkit-box-shadow 0.15s ease-in-out;
    transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, -webkit-box-shadow 0.15s ease-in-out;
    transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out, -webkit-box-shadow 0.15s ease-in-out;

}
.calcContainer .flipper .resultsHeading {
    background-color: {{$template->settings->backgroundColorNear}} !important;
    color: {{$template->settings->font_color}};
}
.calcContainer .flipper .leftBtn {
	background-color: {{$template->settings->backgroundColorNear}} !important;
	color: {{$template->settings->font_color}};
    font-family: {{ str_replace('+',' ',$template->settings->fonts_all) }};
}
.calcContainer .flipper .rightBtn {
	background-color: {{$template->settings->refreshColor}} !important;
	color: {{$template->settings->font_color}};
    font-family: {{ str_replace('+',' ',$template->settings->fonts_all) }};
}

.form-range::-webkit-range-thumb{
    background-color: {{$template->settings->backgroundColorNear}} !important;
}
.form-range::-ms-range-thumb{
    background-color: {{$template->settings->backgroundColorNear}} !important;
}
.form-range::-o-range-thumb{
    background-color: {{$template->settings->backgroundColorNear}} !important;
}
.form-range::-moz-range-thumb{
    background-color: {{$template->settings->backgroundColorNear}} !important;
}
input[type=range]::-webkit-slider-thumb {
    background-color: {{$template->settings->backgroundColorNear}} !important;
}
.calcContainer div.spacer {
    background: {!!$template->settings->lineColor!!} !important;
}
</style>

<div class="bannerWidth">
    <div id="nearme-calculators"></div>
</div>

@if(Config::get('app.debug'))
    <script type="text/javascript" src="http://127.0.0.1:8000/templates/template91/index.js?v=3"></script>
@else
    <script type="text/javascript" src="https://leo.vic-m.co/templates/template91/index.js?v=3"></script>
@endif

@endif
