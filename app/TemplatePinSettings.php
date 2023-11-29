<?php

namespace App;

use stdClass;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\TemplatePinsController;
use App\Http\Controllers\CookieController;
use App\LandingSettings;
use App\Http\Controllers\WidgetController;
use Illuminate\Support\Facades\File;

class TemplatePinSettings extends Model
{
    protected $fillable = ['template_pins_id','banner_creative_id','field','value'];
    protected $table = 'template_pins_settings';
    const UPDATED_AT = false;

    const DEFAULT_STATIC_TEMPLATE = 27;

    const WIDGET_IDS = [91];
    private static $nearmeWeatherCachePeriod = 60; //Cache nearme weather details for 60 minutes

    public static function getFieldGroups($byId = false)
    {
        $groups = [
            ["id"=>"text-message","name"=>"Distance Message","fields"=>["distance_value_text","from_location",'text_transform']],
            ["id"=>"pin-colors","name"=>"Distance Pin Colours","fields"=>["pin_img","customisable_pin_yn","distancebackgroundColor","distanceTopColor","distanceBottomColor","distanceInnerCircleColor"]],
            ["id"=>"pin-size","name"=>"Distance Pin Size","fields"=>["distanceWidth","distanceHeight","distanceTop","distanceLeft"]],
            ["id"=>"fonts","name"=>"Fonts","fields"=>["fonts_all","custom_font","font_color","font_size","font_bold","font_underline","font_italics","form_background_color"]],
            ["id"=>"fonts_animation","name"=>"Text Animation","fields"=>["font_in_animation","font_animation_duration","font_out_direction","font_out_animation","font_in_direction"]],
            ["id"=>"loop-and-background","name"=>"Loop and Background","fields"=>["backgroundColor","backgroundColorNear","nearTopPin","nearCirclePin","nearBottomPin","backgroundColorNearPanel","loop_speed","loop_speed2","loop_speed3", "nearmeBorderRadius"]],
            ["id"=>"content","name"=>"Styling","fields"=>["auto_play","navigation_arrows","nav_color","nav_size","dots"]],
            ["id"=>"geoip-settings","name"=>"GeoIP Location Panel","fields"=>["backgroundColorGeoPanel","distanceTopColorGeo","distanceBottomColorGeo","refreshColor","panelGeoTextColor","distanceInnerCircleColorGeo","panelGeoTextSize","font_italics_geo","font_underline_geo","font_bold_geo"]],
            ["id"=>"widget-content","name"=>"Icon Carousel","fields"=>["auto_play_icon","iconDistanceSize", "iconDistanceColour","navigation_arrows_icon","nav_color_icon","nav_size_icon"]],
            ["id"=>"panel-settings","name"=>"Fine Location Panel","fields"=>["lineColor","backgroundColorFinePanel","loaderColor","panelFineTextSize","panelFineTextColor","font_italics_fine","font_underline_fine","font_bold_fine"]],
            ["id"=>"map-settings", "name"=>"Map Settings", "fields"=>["map_pin_image", "background_color", "border_color","glyph_color", "background_specials_color", "border_specials_color","glyph_specials_color","map_theme", "map_styles"]],
            ["id"=>"wishlist-settings", "name"=>"Wishlist Settings", "fields"=>["add_to_cart", "wishlist"]],
            ["id"=>"other","name"=>"More Settings","fields"=>[]],
            ["id"=>"animated_window","name"=>"Animated Distance Pin","fields"=>["animated_window","loop_speed_pins"]],
            ["id"=>"form-fields","name"=>"Typography Settings","fields"=>["form_group_form_border_color","form_group_form_border_radius", "form_group_form_color","form_group_font","form_group_font_color","form_group_font_size","form_group_font_bold","form_group_font_underline","form_group_font_italics", "form_group_label_hidden",/*"form_group_label_position"*/]],
            ["id"=>"global-fields","name"=>"Global Settings","fields"=>["form_group_form_theme", "form_group_form_background_color"/*"form_group_label_position"*/]],
            // ["id"=>"form-fields","name"=>"Global Settings","fields"=>["form_group_form_theme", "form_group_form_background_color","form_group_form_border_color","form_group_form_border_radius", "form_group_form_color","form_group_font","form_group_font_color","form_group_font_size","form_group_font_bold","form_group_font_underline","form_group_font_italics", "form_group_label_hidden",/*"form_group_label_position"*/]],
            ["id"=>"form-builder","name"=>"Banner Studio", "fields"=>["form", "fieldsFormsBuilder", "pageSettings", "globalPageSettings"]],
            ["id"=>"weather-settings","name"=>"Weather Settings", "fields"=>["form", "weatherPosition", "widthClass", "weatherMainHeadingSize","weatherMainHeadingcolor", "weatherFullWidthTextSize", "weatherFullWidthTextColor", "weatherFullWidthDayLabelTextSize"]],
            ["id"=>"engagements-listing-settings","name"=>"Engagement on Listing", "fields"=>["form", "enableEngagementsOnListing", "selectEngagementOnListingIcons", "outerCircleColourEngagementsOnListing", "innerCircleColourEngagementsOnListing","iconColourEngagementsOnListing"]],
            // ["id"=>"restaurant-settings","name"=>"Trending Restaurant Settings", "fields"=>["form", ""]],
            ["id"=>"Operating Hours","name"=>"Operating Hours", "fields"=>["operating_hours"]],
            ["id"=>"category-management","name"=>"Category Management", "fields"=>["enableCategories"]],
            ["id"=>"calculators","name"=>"NearMe Calculators", "fields"=>["enableCalculators", "calculatorHeight"]],
            ["id"=>"widgetLanguage","name"=>"Widget Language", "fields"=>["widgetLanguage"]],
            // ["id"=>"taffic-widget","name"=>"Traffic Widget Management", "fields"=>["form", ""]],
            ["id"=>"dailydeals-widget","name"=>"Daily Deals Widget Management", "fields"=>["form", "dailyDealsSaveBgColor", "dailyDealsSaveTextColor", "dailyDealsSaveFontSize", "dailyDealsSalePriceTextColor", "dailyDealsProductNameTextColor", "dailyDealsProductNameFontSize"]]
        ];

        foreach(self::dynamicFormFields() as $value){
            $groups[] = [
                "id"=>$value["group"],
                "name"=>$value["name"]." Fields",
                "fields"=>$value["style-fields"]
            ];
        }

        if ($byId) {
            $temp = [];
            foreach ($groups as $value) {
                $temp[$value["id"]] = $value;
            }
            $groups = $temp;
        }

        return $groups;
    }

    public static function dynamicFormFields()
    {
        $fields = array(
            ["id"=>"text","icon"=>"fa fa-window-minimize","name"=>"Text","style-fields"=>["font_color","background_color","border_radius","padding"]],
            ["id"=>"textarea","icon"=>"fa fa-edit","name"=>"Text Area","style-fields"=>["font_color","background_color","border_radius","padding"]],
            ["id"=>"paragraph","icon"=>"fa fa-paragraph","name"=>"Paragraph","style-fields"=>["text_align","line_height","font","font_size","font_color","font_bold","font_underline","font_italics"]],
            ["id"=>"link","icon"=>"fas fa-mouse-pointer","name"=>"Link","style-fields"=>[]],
            ["id"=>"button","icon"=>"fa fa-hand-pointer","name"=>"Button","style-fields"=>["button_font_color","border_radius","padding"]],
            //["id"=>"datepicker","icon"=>"icon-date","name"=>"Date","style-fields"=>["background_color","border_radius","padding"]],
            ["id"=>"dateselect","icon"=>"fa fa-calendar","name"=>"Date Select","style-fields"=>[]],
            ["id"=>"number","icon"=>"fa fa-sort","name"=>"Number","style-fields"=>["font_color","background_color","border_radius","padding"]],
            ["id"=>"select","icon"=>"fa fa-caret-down","name"=>"Dropdown","style-fields"=>["multiple"]],
            ["id"=>"radio","icon"=>"fa fa-circle","name"=>"Radio Group","style-fields"=>["inline"]],
            ["id"=>"checkbox","icon"=>"fa fa-check-square","name"=>"Checkbox Group","style-fields"=>["inline"]],
            ["id"=>"switch","icon"=>"fas fa-toggle-on","name"=>"Switch","style-fields"=>[]],
            ["id"=>"image","icon"=>"fas fa-image","name"=>"Image","style-fields"=>[]],
            ["id"=>"distance_message","icon"=>"fas fa-map-signs","name"=>"Distance Message","style-fields"=>[]],
            ["id"=>"distance_pin","icon"=>"fas fa-map-marker-alt","name"=>"Distance Pin","style-fields"=>[]],
            ["id"=>"html","icon"=>"fas fa-code","name"=>"3rd Party Creative","style-fields"=>[]],
            ["id"=>"custom","icon"=>"fas fa-shapes","name"=>"Custom Button","style-fields"=>[]],
            ["id"=>"video","icon"=>"fas fa-play","name"=>"Video","style-fields"=>[]],
            ["id"=>"feed","icon"=>"fa fa-file-excel","name"=>"Feed","style-fields"=>[]],
            ["id"=>"map","icon"=>"fa fa-map","name"=>"Map","style-fields"=>[]],
        );

        $defaultIcons  = LandingSettings::getPresetIcons();
        $defaultColors = LandingSettings::getDefaultColors();

        foreach($fields as &$value){
            $value["group"] = $value["id"]."_group";
            foreach($value['style-fields'] as &$style_field){
                $style_field = $value['group'] .'_'. $style_field;
            }

            $field_type = $value["id"];
            //basic defaults
            $value['defaults'] = [];
            $value['defaults'][$field_type] = [];
            $value['defaults'][$field_type]['type'] = $value['id'];
            $value['defaults'][$field_type]['name'] = 'Label';
            $value['defaults'][$field_type]['page'] = 0;
            $value['defaults'][$field_type]['id'] = $value['id'];

            //multiple option defaults
            if(in_array($value['id'],["select", "radio", "checkbox"])){
                $value['defaults'][$field_type]['options'] = ['Option 1','Option 2','Option 3'];
            }

            if($value['id']=='select'){
                $value['defaults'][$field_type]['noneEnabled'] = false;
                $value['defaults'][$field_type]['noneLabel'] = 'Select';
            }

            if($value['id']=='image'){
                $value['defaults'][$field_type]['src'] = '';
            }

            if($value['id']=='html'){
                $value['defaults'][$field_type]['size'] = '300x250';
                $value['defaults'][$field_type]['client'] = 'Vicinity';
                /** Defined as anything that can later be styled according to custom settings */
                $value['defaults'][$field_type]['creativeCategory'] = 'Ad'; //LocationBankReview, GoogleReview, PlainHTML, VAST etc;
                $value['defaults'][$field_type]['code'] = '';
                $value['defaults'][$field_type]['alerts'] = [];
                /** If enabled, CSS styling will be overridden based on the creativeCategory which is auto-detected */
                $value['defaults'][$field_type]['customStyling'] = false;

                /** Default style options for LocationBankReview s*/
                $value['defaults'][$field_type]['LB_reviewBackgroundColor'] = '#ffffff';
                $value['defaults'][$field_type]['LB_reviewFont'] = '';
                $value['defaults'][$field_type]['LB_reviewPadding'] = 1;
                $value['defaults'][$field_type]['LB_reviewDisplayNameColor'] = '#212529';
                $value['defaults'][$field_type]['LB_reviewRatingColor'] = '#ff5d48';
                $value['defaults'][$field_type]['LB_recordDateStringColor'] = '#212529';
                $value['defaults'][$field_type]['LB_reviewCommentColor'] = '#212529';
            }

            if($value['id']=='link'){
                $value['defaults'][$field_type]['href'] = '#';
                $value['defaults'][$field_type]['target'] = '_blank';
                $value['defaults'][$field_type]['text'] = 'Link';
                $value['defaults'][$field_type]['linkButton'] = false;
            }

            if($value['id']=='dateselect'){
                $value['defaults'][$field_type]['showLabel'] = false;
                $value['defaults'][$field_type]['startYear'] = 1900;
                $value['defaults'][$field_type]['endYear'] = date('Y');
                $value['defaults'][$field_type]['dayFormat'] = 'j';
                $value['defaults'][$field_type]['monthFormat'] = 'n';
                $value['defaults'][$field_type]['yearFormat'] = 'Y';
                $value['defaults'][$field_type]['dateFormat'] = 'month,day,year'; //or day,month,year //or year,month,day
                $value['defaults'][$field_type]['yearOrder'] = 'asc';
            }

            if($value['id']=='paragraph'){
                $value['defaults'][$field_type]['text'] = '';
            }

            if($value['id']=='custom'){
                $value['defaults'][$field_type]['contentType'] = 'icon';
                $value['defaults'][$field_type]['buttonShape'] = CustomIcon::DEFAULT_SHAPE;
                $value['defaults'][$field_type]['contentText'] = $defaultIcons[0]['name'];
                $value['defaults'][$field_type]['contentIcon'] = $defaultIcons[0]['icon'];
                $value['defaults'][$field_type]['buttonType'] = $defaultIcons[0]['id'];
                $value['defaults'][$field_type]['buttonOuterColor'] = $defaultColors['icon']['outer']['color'];
                $value['defaults'][$field_type]['buttonInnerColor'] = $defaultColors['icon']['inner']['color'];
                $value['defaults'][$field_type]['buttonIconColor'] = $defaultColors['icon']['icon']['color'];
                $value['defaults'][$field_type]['buttonTextColor'] = $defaultColors['icon']['text']['color'];
                $value['defaults'][$field_type]['linkEditable'] = $defaultIcons[0]['link_editabled'];
                $value['defaults'][$field_type]['socialMedia'] = $defaultIcons[0]['social_media'];
                $value['defaults'][$field_type]['buttonLink'] = $defaultIcons[0]['link'];

                //Subtypes download button
                $value['defaults']['download']['buttonLink'] = '';
                $value['defaults']['download']['buttonIOSLink'] = '';
                $value['defaults']['download']['buttonAndroidLink'] = '';

                //Subtypes calevent button
                $value['defaults']['calevent']['eventTitle'] = 'Event Title';
                $value['defaults']['calevent']['eventStart'] = date('Y-m-d\TH:i:s');
                $value['defaults']['calevent']['eventEnd'] = date('Y-m-d\TH:i:s', strtotime('+1 hour'));
                $value['defaults']['calevent']['eventDetails'] = 'Event Details';
                $value['defaults']['calevent']['eventAllDay'] = false;
            }

            if($value['id']=='video'){
                $value['defaults'][$field_type]['src'] = '';
                $value['defaults'][$field_type]['format'] = '';
                $value['defaults'][$field_type]['playtime'] = 0;
                $value['defaults'][$field_type]['key'] = '';
                $value['defaults'][$field_type]['width'] = 300;
                $value['defaults'][$field_type]['height'] = 250;
            }
        }
        return $fields;
    }

    public static function getFieldTypeDefaults($field)
    {
        $field_types = [
            #general
            "font" => ['type'=>'font','value'=>"Open Sans"],
            "font_color" => ['type'=>'color','value'=>'#495057'],
            "font_size" => ['type'=>'font_size','value'=>'12'],
            "font_bold" => ['type'=>'checkbox','value'=>false],
            "font_underline" => ['type'=>'checkbox','value'=>false],
            "font_italics" => ['type'=>'checkbox','value'=>false],
            "background_color" => ['type'=>'color','value'=>'#FFF'],
            "border_color" => ['type'=>'color','value'=>'#555'],
            "border_style" => ['type'=>'select','value'=>'solid','options'=>['none'=>"None",'inset'=>"Inset",'outset'=>"Outset",'dashed'=>"Dashed",'dotted'=>"Dotted",'double'=>"Double",'groove'=>"Groove",'hidden'=>"Hidden",'ridge'=>"Ridge",'solid'=>"Solid"]],
            "border_width" => ['type'=>'loop','value'=>1,'multiply'=>1,'min'=>0,'max'=>25,'allow_empty'=>true],
            "border_radius" => ['type'=>'loop','value'=>4,'multiply'=>1,'min'=>0,'max'=>25,'allow_empty'=>true],
            "padding" => ['type'=>'loop','value'=>6,'multiply'=>1,'min'=>0,'max'=>25,'allow_empty'=>true],
            #image specific
            "image_width" => ['type'=>'number','value'=>100,],
            "image_height" => ['type'=>'number','value'=>100,],
            # input specific
            "input_type" => ['type'=>'select','value'=>'text','options'=>['text'=>"Text",'password'=>"Password",'email'=>"Email",'color'=>"Color",'tel'=>"Telephone"]],
            "input_required" => ['type'=>'checkbox','value'=>true],
            # label specific
            "label_hidden" => ['type'=>'checkbox','value'=>false,],
            "label_position" => ['type'=>'select','value'=>'top','options'=>['top'=>"Top",'left'=>"Left"]],
            #select specific
            "multiple" => ['type'=>'checkbox','value'=>false],
            #radio/checkbox specific
            "inline" => ['type'=>'checkbox','value'=>false],
            #number specific
            "thousands_seperator" => ['type'=>'checkbox','value'=>false],
            #form specific
            "form_theme" => ['type'=>'select','label'=>'Global Theme','value'=>'bootstrap','options'=>['bootstrap'=>"Bootstrap",'material'=>"Material Design"]],
            "form_color" => ['type'=>'color','label'=>'Button Colour','value'=>'#007bff'],
            "form_background_color" => ['type'=>'color','label'=>'Canvas Background Colour' ,'value'=>'#FFF'],
            "form_border_color" => ['type'=>'color','label'=>'Border Colour','value'=>'#FFF'],
            "form_border_radius" =>['type'=>'loop','label'=>'Border Radius','value'=>0,'multiply'=>1,'min'=>0,'max'=>25,'allow_empty'=>true],
            #button specific
            "button_font_color" => ['type'=>'color','value'=>'#FFF'],
            #paragraph message specific
            "text_align" => ['type'=>'select','value'=>'left','options'=>['left'=>"Left",'center'=>"Center",'right'=>"right"]],
            "line_height" => ['type'=>'number','value'=>1.5,],
            /*
            #distance message specific
            "distance_value_text" =>  ['type'=>'text','label'=>'Display Message','allow_empty'=>false,'value'=>"%%DISTANCE%%",],
            #distance pin specific
            "customisable_pin_yn" => ['type'=>'checkbox', 'value'=>true,'label'=>'Use Customisable Distance Pin?'],
            "pin_img" => ['type'=>'upload', 'allow_empty'=>true, 'value'=>"/templates/pin/template34/default.svg", 'label'=>'Upload New Distance Window Pin'],
            "distancebackgroundColor" => ['type'=>'color','value'=>'#FFF', 'label'=>'Pin Background Color'],
            "distanceTopColor" => ['type'=>'color','value'=>'#759AC6', 'label'=>'Pin Top Color'],
            "distanceBottomColor" => ['type'=>'color','value'=>'#075D86', 'label'=>'Pin Bottom Color'],
            "distanceInnerCircleColor" => ['type'=>'color','value'=>'#075D86', 'label'=>'Pin Inner-Circle Color']
            */
        ];

        return $field_types[$field] ?? [];
    }

    public static function getTemplate2Settings()
    {
        $template2Settings = [];

        $fields = [
            'GoMetro 10% OFF','123 Street','10 Minutes Away','50m Away','GoMetro 10% OFF','Animation Pin',
            ];

        $template2Settings[] = [
            'label'=>'Font',
            'type'=>'font', //uses text box and comes with color picker
            'name'=>'all_font',
            'value'=>'Open Sans'
        ];

        $template2Settings[] = [
            'label'=>'Font Size',
            'type'=>'font_size', //uses text box and comes with color picker
            'name'=>'font_size',
            'value'=>'13'
        ];

        $template2Settings[] = [
            'label'=>'Background Color',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'backgroundColor',
            'value'=>'#ffffff'
        ];

        foreach ($fields as $key => $value) {
            $pos = $key+1;
            $template2Settings[] = [
                'label'=>'Text '.$pos,
                'type'=>'text',
                'name'=>'name'.$pos,
                'value'=>$value,
                'allow_empty'=>true
            ];
        }
        return $template2Settings;
    }

    public static function getGoogleFonts()
    {
        $fontsJson = [];
        $cacheKey = 'googleFonts';

        if (Cache::has($cacheKey)) {
            $fontsJson = Cache::get($cacheKey);
        } else {
            if(!\Config::get('app.debug')){
                $url = config('google.fonts.url').'?key='.config('google.fonts.key');

                $arrContextOptions=[
                    "ssl"=>[
                        "verify_peer"=>false,
                        "verify_peer_name"=>false,
                    ],
                ];

                $data = file_get_contents($url, false, stream_context_create($arrContextOptions));
                $data = json_decode($data);

                $fontFamalies = [];
                foreach ($data->items as $font) {
                    $fontFamalies[] = $font->family;
                }
                $fontsJson['fontFamalies'] = $fontFamalies;
                $fontsJson['fonts'] = $data;
                $fontsJson = json_encode($fontsJson);
            }

            Cache::put($cacheKey, $fontsJson, 600);
        }

        if(empty($fontsJson)){
            return json_encode($fontsJson);
        }else{
            return $fontsJson;
        }

    }

    public static function getFeedNames($id = null){
        $names = array_unique(DB::table('macro_feed')->where('name','<>','')->orderBy('name')->get()->pluck('name')->toArray());

        $temp = $names;
        $names = [];
        foreach ($temp as $value) {
            $key = md5($value);
            $names[$key] = $value;
            if($id == $key ){
                return $value;
            }
        }

        return $names;
    }

    public static function getTemplate154Settings(){
        $settings = [

            [
                'type'=>'text',
                'name'=>'distance_value_text',
                'label'=>'Distance',
                'allow_empty'=>true,
                'value'=>"%%DISTANCE%% From your location"
            ],
            [
                'type'=>'text',
                'name'=>'from_location',
                'label'=>'Location Address',
                'allow_empty'=>true,
                'value'=>"From your location"
            ],
            [
                'label'=>'Background Color',
                'type'=>'color',
                'name'=>'backgroundColor',
                'value'=>'#ffffff'
            ],
            [
                'type'=>'font',
                'name'=>'fonts_all',
                'label'=>'Google Fonts',
                'value'=>"Open Sans"
            ],
            [
                'type'=>'custom_font',
                'name'=>'custom_font',
                'label'=>'Custom Fonts',
                'value'=>''
            ],
            [
                'label'=>'Font Colour',
                'type'=>'color',
                'name'=>'font_color',
                'value'=>'#070275'
            ],
            [
                'label'=>'Font Size',
                'type'=>'font_size',
                'name'=>'font_size',
                'value'=>'13'
            ],
            [
                'label'=>'Font Bold',
                'type'=>'checkbox',
                'name'=>'font_bold',
                'type'=>'checkbox',
                'value'=>false
            ],
            [
                'label'=>'Font Underline',
                'type'=>'checkbox',
                'name'=>'font_underline',
                'type'=>'checkbox',
                'value'=>false
            ],
            [
                'label'=>'Font Italics',
                'type'=>'checkbox',
                'name'=>'font_italics',
                'type'=>'checkbox',
                'value'=>false
            ],

            [
                'type'=>'upload',
                'name'=>'pin_img',
                'label'=>'Upload New Distance Window Pin',
                'allow_empty'=>true,
                'value'=>"https://static.vic-m.co/templates/pin/template25/default.png"
            ]

        ];

        $names = self::getFeedNames();

        $settings[] = [
            'label'=>'Select A Feed',
            'type'=>'select',
            'name'=>'feed_name',
            'value'=>'',
            'options'=>$names
        ];

        $settings[] = [
        'type'=>'font',
        'name'=>'fonts_all',
        'label'=>'Google Fonts',
        'value'=>"Open Sans" //Please use google fonts
        ];

        $settings[] = [
            'label'=>'Number Of Page',
            'type'=>'number',
            'name'=>'number_of_pages',
            'value'=>'4'
        ];
        $settings[] = [
            'label'=>'Products Per Page',
            'type'=>'number',
            'name'=>'products_per_page',
            'value'=>'1'
        ];


        $settings[] = [
                    'label'=>'Use Customisable Distance Pin?',
                    'type'=>'checkbox',
                    'name'=>'customisable_pin_yn',
                    'value'=>false
            ];
        $settings[] = [
                    'label'=>'Distance Background Color',
                    'type'=>'color',
                    'name'=>'distancebackgroundColor',
                    'value'=>'#151515'
            ];
        $settings[] = [
                    'label'=>'Distance Top Color',
                    'type'=>'color',
                    'name'=>'distanceTopColor',
                    'value'=>'#759AC6'
            ];
        $settings[] = [
                    'label'=>'Distance Bottom Color',
                    'type'=>'color',
                    'name'=>'distanceBottomColor',
                    'value'=>'#075D86'
            ];
        $settings[] = [
                    'label'=>'Distance Inner-Circle Color',
                    'type'=>'color',
                    'name'=>'distanceInnerCircleColor',
                    'value'=>'#075D86'
            ];
        $settings[] = [
                    'label'=>'Distance Width',
                    'type'=>'number',
                    'name'=>'distanceWidth',
                    'value'=>'115'
            ];
        $settings[] = [
                    'label'=>'Distance Height',
                    'type'=>'number',
                    'name'=>'distanceHeight',
                    'value'=>'50'
            ];
        $settings[] = [
                    'label'=>'Distance Top',
                    'type'=>'number',
                    'name'=>'distanceTop',
                    'value'=>'0'
            ];
        $settings[] = [
                    'label'=>'Distance Left',
                    'type'=>'number',
                    'name'=>'distanceLeft',
                    'value'=>'0'
            ];

        return $settings;
    }

    public static function getTemplate25Settings()
    {
        $settings = [];

        $settings = [
                [
                    'type'=>'text',
                    'name'=>'distance_value_text',
                    'label'=>'Distance',
                    'allow_empty'=>true,
                    'value'=>"%%DISTANCE%% From your location"
                ],
                [
                    'type'=>'text',
                    'name'=>'from_location',
                    'label'=>'Location Address',
                    'allow_empty'=>true,
                    'value'=>"From your location"
                ],
                [
                    'label'=>'Background Color',
                    'type'=>'color',
                    'name'=>'backgroundColor',
                    'value'=>'#ffffff'
                ],
                [
                    'type'=>'font',
                    'name'=>'fonts_all',
                    'label'=>'Google Fonts',
                    'value'=>"Open Sans"
                ],
                [
                    'type'=>'custom_font',
                    'name'=>'custom_font',
                    'label'=>'Custom Fonts',
                    'value'=>''
                ],
                [
                    'label'=>'Font Colour',
                    'type'=>'color',
                    'name'=>'font_color',
                    'value'=>'#070275'
                ],
                [
                    'label'=>'Font Size',
                    'type'=>'font_size',
                    'name'=>'font_size',
                    'value'=>'13'
                ],
                [
                    'label'=>'Font Bold',
                    'type'=>'checkbox',
                    'name'=>'font_bold',
                    'type'=>'checkbox',
                    'value'=>false
                ],
                [
                    'label'=>'Font Underline',
                    'type'=>'checkbox',
                    'name'=>'font_underline',
                    'type'=>'checkbox',
                    'value'=>false
                ],
                [
                    'label'=>'Font Italics',
                    'type'=>'checkbox',
                    'name'=>'font_italics',
                    'type'=>'checkbox',
                    'value'=>false
                ],

                [
                    'type'=>'upload',
                    'name'=>'pin_img',
                    'label'=>'Upload New Distance Window Pin',
                    'allow_empty'=>true,
                    'value'=>"https://static.vic-m.co/templates/pin/template25/default.png"
                ]   ,
                [
                    'label'=>'Loop Speed',
                    'type'=>'loop',
                    'name'=>'loop_speed',
                    'value'=>5,
                    'multiply'=>1,
                    'min'=>3,
                    'max'=>25,
                ],
            ];

        $settings[] = [
                    'label'=>'Use Customisable Distance Pin?',
                    'type'=>'checkbox',
                    'name'=>'customisable_pin_yn',
                    'value'=>false
            ];
        $settings[] = [
                    'label'=>'Distance Background Color',
                    'type'=>'color',
                    'name'=>'distancebackgroundColor',
                    'value'=>'#151515'
            ];
        $settings[] = [
                    'label'=>'Distance Top Color',
                    'type'=>'color',
                    'name'=>'distanceTopColor',
                    'value'=>'#759AC6'
            ];
        $settings[] = [
                    'label'=>'Distance Bottom Color',
                    'type'=>'color',
                    'name'=>'distanceBottomColor',
                    'value'=>'#075D86'
            ];
        $settings[] = [
                    'label'=>'Distance Inner-Circle Color',
                    'type'=>'color',
                    'name'=>'distanceInnerCircleColor',
                    'value'=>'#075D86'
            ];
        $settings[] = [
                    'label'=>'Distance Width',
                    'type'=>'number',
                    'name'=>'distanceWidth',
                    'value'=>'115'
            ];
        $settings[] = [
                    'label'=>'Distance Height',
                    'type'=>'number',
                    'name'=>'distanceHeight',
                    'value'=>'50'
            ];
        $settings[] = [
                    'label'=>'Distance Top',
                    'type'=>'number',
                    'name'=>'distanceTop',
                    'value'=>'0'
            ];
        $settings[] = [
                    'label'=>'Distance Left',
                    'type'=>'number',
                    'name'=>'distanceLeft',
                    'value'=>'0'
            ];

        return $settings;
    }

    public static function getTemplate6Settings()
    {
        $settings = [];

        $settings = [

                [
                    'type'=>'text',
                    'name'=>'distance_value_text',
                    'label'=>'Display Message',
                    'allow_empty'=>true,
                    'value'=>"%%DISTANCE%%"
                ],
                [
                    'label'=>'Background Color',
                    'type'=>'color', //uses text box and comes with color picker
                    'name'=>'backgroundColor',
                    'value'=>'#ffffff'
                ],
                [
                    'type'=>'upload',
                    'name'=>'pin_img',
                    'label'=>'Upload New Distance Window Pin',
                    'allow_empty'=>true,
                    'value'=>"https://static.vic-m.co/templates/pin/template6/default.png"
                ],
                [
                    'type'=>'font',
                    'name'=>'fonts_all',
                    'label'=>'Google Fonts',
                    'value'=>"Open Sans" //Please use google fonts
                ],
                [
                    'type'=>'custom_font',
                    'name'=>'custom_font',
                    'label'=>'Custom Fonts',
                    'value'=>''
                ],
                [
                    'label'=>'Font Colour',
                    'type'=>'color', //uses text box and comes with color picker
                    'name'=>'font_color',
                    'value'=>'#070275'
                ],
                [
                    'label'=>'Font Size',
                    'type'=>'font_size',
                    'name'=>'font_size',
                    'value'=>'13'
                ],
                [
                    'label'=>'Font Bold',
                    'type'=>'checkbox',
                    'name'=>'font_bold',
                    'type'=>'checkbox',
                    'value'=>false
                ],
                [
                    'label'=>'Font Underline',
                    'type'=>'checkbox',
                    'name'=>'font_underline',
                    'type'=>'checkbox',
                    'value'=>false
                ],
                [
                    'label'=>'Font Italics',
                    'type'=>'checkbox',
                    'name'=>'font_italics',
                    'type'=>'checkbox',
                    'value'=>false
                ],
            ];
        return $settings;
    }
    public static function getTemplate30Settings()
    {
        $settings=[

        [
        'label'=>'Use Customisable Distance Pin?',
        'type'=>'checkbox',
        'name'=>'customisable_pin_yn',
        'value'=>true
        ],
        [
        'type'=>'upload',
        'name'=>'pin_img',
        'label'=>'Upload New Distance Window Pin',
        'allow_empty'=>true,
        'value'=>"/templates/pin/template30/svg.svg"
        ],
        [
        'label'=>'Pin Background Color',
        'type'=>'color',
        'name'=>'distancebackgroundColor',
        'value'=>'#FFFFFF'
        ],
        [
        'label'=>'Pin Top Color',
        'type'=>'color',
        'name'=>'distanceTopColor',
        'value'=>'#759AC6'
        ],
        [
        'label'=>'Pin Bottom Color',
        'type'=>'color',
        'name'=>'distanceBottomColor',
        'value'=>'#075D86'
        ],
        [
        'label'=>'Pin Inner-Circle Color',
        'type'=>'color',
        'name'=>'distanceInnerCircleColor',
        'value'=>'#075D86'
        ],
        [
        'label'=>'Distance Pin Display Time(s)',
        'type'=>'loop',
        'name'=>'loop_speed',
        'value'=>8,
        'multiply'=>1,
        'min'=>1,
        'max'=>20,
        ],
        [
        'type'=>'font',
        'name'=>'fonts_all',
        'label'=>'Google Fonts',
        'value'=>"Open Sans"
        ],
        [
        'type'=>'custom_font',
        'name'=>'custom_font',
        'label'=>'Custom Fonts',
        'value'=>''
        ],
        [
        'label'=>'Font Colour',
        'type'=>'color',
        'name'=>'font_color',
        'value'=>'#070275'
        ],
        [
        'label'=>'Font Size',
        'type'=>'font_size',
        'name'=>'font_size',
        'value'=>'13'
        ],
        [
        'label'=>'Font Bold',
        'type'=>'checkbox',
        'name'=>'font_bold',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'label'=>'Font Underline',
        'type'=>'checkbox',
        'name'=>'font_underline',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'label'=>'Font Italics',
        'type'=>'checkbox',
        'name'=>'font_italics',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'type'=>'text',
        'name'=>'distance_value_text',
        'label'=>'Display Message',
        'allow_empty'=>true,
        'value'=>"%%DISTANCE%%"
        ],

        ];
        return $settings;
    }

    public static function getTemplate83Settings()
    {
        $settings=[

        [
        'label'=>'Use Customisable Distance Pin?',
        'type'=>'checkbox',
        'name'=>'customisable_pin_yn',
        'value'=>true
        ],
        [
        'type'=>'upload',
        'name'=>'pin_img',
        'label'=>'Upload New Distance Window Pin',
        'allow_empty'=>true,
        'value'=>"/templates/pin/template30/svg.svg"
        ],
        [
        'label'=>'Pin Background Color',
        'type'=>'color',
        'name'=>'distancebackgroundColor',
        'value'=>'#FFFFFF'
        ],
        [
        'label'=>'Pin Top Color',
        'type'=>'color',
        'name'=>'distanceTopColor',
        'value'=>'#759AC6'
        ],
        [
        'label'=>'Pin Bottom Color',
        'type'=>'color',
        'name'=>'distanceBottomColor',
        'value'=>'#075D86'
        ],
        [
        'label'=>'Pin Inner-Circle Color',
        'type'=>'color',
        'name'=>'distanceInnerCircleColor',
        'value'=>'#075D86'
        ],
        [
        'label'=>'Distance Pin Display Time(s)',
        'type'=>'loop',
        'name'=>'loop_speed',
        'value'=>8,
        'multiply'=>1,
        'min'=>1,
        'max'=>20,
        ],
        [
        'type'=>'font',
        'name'=>'fonts_all',
        'label'=>'Google Fonts',
        'value'=>"Open Sans"
        ],
        [
        'type'=>'custom_font',
        'name'=>'custom_font',
        'label'=>'Custom Fonts',
        'value'=>''
        ],
        [
        'label'=>'Font Colour',
        'type'=>'color',
        'name'=>'font_color',
        'value'=>'#070275'
        ],
        [
        'label'=>'Font Size',
        'type'=>'font_size',
        'name'=>'font_size',
        'value'=>'13'
        ],
        [
        'label'=>'Font Bold',
        'type'=>'checkbox',
        'name'=>'font_bold',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'label'=>'Font Underline',
        'type'=>'checkbox',
        'name'=>'font_underline',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'label'=>'Font Italics',
        'type'=>'checkbox',
        'name'=>'font_italics',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'type'=>'text',
        'name'=>'distance_value_text',
        'label'=>'Display Message',
        'allow_empty'=>true,
        'value'=>"%%DISTANCE%%"
        ],

        ];
        return $settings;
    }

    public static function getTemplate51Settings($tempId = 51)
    {
        $settings = [
        [
        'label'=>'Loop Speed',
        'type'=>'loop',
        'name'=>'loop_speed3',
        'value'=>2000,
        'multiply'=>1000,
        'min'=>1,
        'max'=>20,
        ],
        [
        'label'=>'Banner Display Time(s)',
        'type'=>'loop',
        'name'=>'loop_speed',
        'value'=>3000,
        'multiply'=>1000,
        'min'=>3,
        'max'=>20,
        ],
        [
        'label'=>'Distance Pin Display Time(s)',
        'type'=>'loop',
        'name'=>'loop_speed2',
        'value'=>3000,
        'multiply'=>1000,
        'min'=>3,
        'max'=>20,
        ],
        [
        'label'=>'Use Customisable Distance Pin?',
        'type'=>'checkbox',
        'name'=>'customisable_pin_yn',
        'value'=>true,
        ],

        [
        'label'=>'Pin Background Color',
        'type'=>'color',
        'name'=>'distancebackgroundColor',
        'value'=>'#FFFFFF'
        ],
        [
        'label'=>'Pin Top Color',
        'type'=>'color',
        'name'=>'distanceTopColor',
        'value'=>'#759AC6'
        ],
        [
        'label'=>'Pin Bottom Color',
        'type'=>'color',
        'name'=>'distanceBottomColor',
        'value'=>'#075D86'
        ],
        [
        'label'=>'Pin Inner-Circle Color',
        'type'=>'color',
        'name'=>'distanceInnerCircleColor',
        'value'=>'#075D86'
        ],
        [
        'type'=>'text',
        'name'=>'distance_value_text',
        'label'=>'Display Message',
        'allow_empty'=>false,
        'value'=>"%%DISTANCE%%",
        ],
        [
        'type'=>'font',
        'name'=>'fonts_all',
        'label'=>'Google Fonts',
        'value'=>"Open Sans" //Please use google fonts
        ],
        [
        'type'=>'custom_font',
        'name'=>'custom_font',
        'label'=>'Custom Fonts',
        'value'=>''
        ],
        [
        'label'=>'Font Colour',
        'type'=>'color', //uses text box and comes with color picker
        'name'=>'font_color',
        'value'=>'#175582'
        ],
        [
        'label'=>'Font Bold',
        'type'=>'checkbox',
        'name'=>'font_bold',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'label'=>'Font Underline',
        'type'=>'checkbox',
        'name'=>'font_underline',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'label'=>'Font Italics',
        'type'=>'checkbox',
        'name'=>'font_italics',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'label'=>'Slide Style',
        'type'=>'select',
        'name'=>'slide_style',
        'value'=>'linear',
        'options'=>[
            'jswing'=>"moves slower at the beginning/end, but faster in the middle",
            'linear'=>"Moves in a constant speed",
            'easeInOutQuad'=>"Quad",
            'easeInOutCubic'=>"Cubic",
            'easeInOutQuart'=>"Quart",
            'easeInOutQuint'=>"Quint",
            'easeInOutSine'=>"Sine",
            'easeInOutExpo'=>"Expo",
            'easeInOutCirc'=>"Circ",
            'easeInOutElastic'=>"Elastic",
            'easeInOutBack'=>"Back",
            'easeInOutBounce'=>"Bounce",
        ]
        ],

        ];
        $settings[] =  [
          'label'=>'Orientation',
          'type'=>'select',
          'name'=>'orientation_type',
          'value'=>'height',
          'options'=>[
              'width'=>"Horizontal",
              'height'=>"Vertical",
              'none'=>"None"
          ]
        ];

        $settings[] = [
          'label'=>'Transition Direction',
          'type'=>'select',
          'name'=>'transition_direction',
          'value'=>'leftToRight',
          'options'=>[
            'leftToRight'=>'Left-to-right',
            'topToBottom'=>'Top-to-bottom',
            'rightToLeft'=>'Right-to-left',
            'bottomToTop'=>'Bottom-to-top'
          ]

        ];

        $settings[] = [
        'label'=>'Font Size',
        'type'=>'font_size',
        'name'=>'font_size',
        'value'=>'13'
        ];

        if ($tempId == 52) {
            $settings[] = [
                    'type'=>'upload',
                    'name'=>'pin_img',
                    'label'=>'Upload New Distance Window Pin',
                    'allow_empty'=>true,
                    'value'=>"/templates/pin/template50/default.svg",
            ];
        } else {
            $settings[] = [
                    'type'=>'upload',
                    'name'=>'pin_img',
                    'label'=>'Upload New Distance Window Pin',
                    'allow_empty'=>true,
                    'value'=>"https://static.vic-m.co/templates/pin/template11/default.png",
            ];
        }

        $settings = array_merge($settings, self::animationFonts());
        return $settings;
    }

    public static function getTemplate49Settings()
    {
        $settings = [];
        $settings = [
        [
        'label'=>'Loop Speed',
        'type'=>'loop',
        'name'=>'loop_speed3',
        'value'=>600,
        'multiply'=>300,
        'min'=>1,
        'max'=>30,
        ],
        [
        'label'=>'Banner Display Time(s)',
        'type'=>'loop',
        'name'=>'loop_speed',
        'value'=>4000,
        'multiply'=>1000,
        'min'=>3,
        'max'=>20,
        ],
        [
        'label'=>'Distance Pin Display Time(s)',
        'type'=>'loop',
        'name'=>'loop_speed2',
        'value'=>6000,
        'multiply'=>1000,
        'min'=>3,
        'max'=>20,
        ],
        [
        'label'=>'Use Customisable Distance Pin?',
        'type'=>'checkbox',
        'name'=>'customisable_pin_yn',
        'value'=>true,
        ],

        [
        'label'=>'Pin Background Color',
        'type'=>'color',
        'name'=>'distancebackgroundColor',
        'value'=>'#FFFFFF'
        ],
        [
        'label'=>'Pin Top Color',
        'type'=>'color',
        'name'=>'distanceTopColor',
        'value'=>'#759AC6'
        ],
        [
        'label'=>'Pin Bottom Color',
        'type'=>'color',
        'name'=>'distanceBottomColor',
        'value'=>'#075D86'
        ],
        [
        'label'=>'Pin Inner-Circle Color',
        'type'=>'color',
        'name'=>'distanceInnerCircleColor',
        'value'=>'#075D86'
        ],

        [
        'type'=>'text',
        'name'=>'distance_value_text',
        'label'=>'Display Message',
        'allow_empty'=>false,
        'value'=>"%%DISTANCE%%",
        ],
        [
            'type'=>'font',
            'name'=>'fonts_all',
            'label'=>'Google Fonts',
            'value'=>"Open Sans" //Please use google fonts
        ],
        [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
        ],
        [
            'label'=>'Font Colour',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#175582'
        ],

        [
            'label'=>'Font Bold',
            'type'=>'checkbox',
            'name'=>'font_bold',
            'value'=>false
        ],
        [
            'label'=>'Font Italics',
            'type'=>'checkbox',
            'name'=>'font_italics',
            'value'=>false
        ],
        [
            'label'=>'Number Of Slices',
            'type'=>'number',
            'name'=>'slices',
            'value'=>3
        ],
        [
            'label'=>'Slice Separater',
            'type'=>'number',
            'name'=>'disperseFactor',
            'value'=>40
        ],
        [
            'label'=>'Color Between Slices',
            'type'=>'color',
            'name'=>'colorSlices',
            'value'=>'#000000'
        ],
        [
            'label'=>'Font Size',
            'type'=>'font_size',
            'name'=>'font_size',
            'value'=>'20'
        ],
        [
            'type'=>'upload',
            'name'=>'pin_img',
            'label'=>'Upload New Distance Window Pin',
            'allow_empty'=>true,
            'value'=>"https://static.vic-m.co/templates/pin/template11/default.png",
        ],
        [
        'label'=>'Orientation',
        'type'=>'select',
        'name'=>'orientation_type',
        'value'=>'h',
        'options'=>[
            'h'=>"Horizontal",
            'v'=>"Vertical",
            'r'=>"Random",
            ]
        ],
        [
          'label'=>'Transition Direction',
          'type'=>'select',
          'name'=>'transition_direction',
          'value'=>'leftToRight',
          'options'=>[
            'leftToRight'=>'Left-to-right',
            'topToBottom'=>'Top-to-bottom',
            'rightToLeft'=>'Right-to-left',
            'bottomToTop'=>'Bottom-to-top'
          ]
        ]

        ];

        $settings = array_merge($settings, self::animationFonts());
        return $settings;
    }

    public static function workForm($input)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode(' ', $ret);
    }

    private static function animationFonts()
    {
        $settings = [];
        $animation = "none,flash,bounce,shake,tada,swing,wobble,pulse,flip,flipInX,flipOutX,flipInY,flipOutY,fadeIn,fadeInUp,fadeInDown,fadeInLeft,fadeInRight,fadeInUpBig,fadeInDownBig,fadeInLeftBig,fadeInRightBig,fadeOut,fadeOutUp,fadeOutDown,fadeOutLeft,fadeOutRight,fadeOutUpBig,fadeOutDownBig,fadeOutLeftBig,fadeOutRightBig,bounceIn,bounceInDown,bounceInUp,bounceInLeft,bounceInRight,bounceOut,bounceOutDown,bounceOutUp,bounceOutLeft,bounceOutRight,rotateIn,rotateInDownLeft,rotateInDownRight,rotateInUpLeft,rotateInUpRight,rotateOut,rotateOutDownLeft,rotateOutDownRight,rotateOutUpLeft,rotateOutUpRight,hinge,rollIn,rollOut";
        $animation = explode(",", $animation);

        $direction = ["sequence","reverse","sync","shuffle"];

        $temp = [];
        foreach ($animation as $value) {
            $temp[$value] = ucfirst(self::workForm($value));
        }
        $animation = $temp;

        $temp = [];
        foreach ($direction as $value) {
            $temp[$value] = ucfirst(self::workForm($value));
        }
        $direction = $temp;


        $settings = [
        [
            'label'=>'Font Animation Duration',
            'type'=>'loop',
            'name'=>'font_animation_duration',
            'value'=>3000,
            'multiply'=>1000,
            'min'=>1,
            'max'=>25,
        ],
        [
            'label'=>'Font In Animation',
            'type'=>'select',
            'name'=>'font_in_animation',
            'value'=>array_rand($animation),
            'options'=>$animation
        ],
        [
            'label'=>'Font In Direction',
            'type'=>'select',
            'name'=>'font_in_direction',
            'value'=>array_rand($direction),
            'options'=>$direction
        ],
        [
            'label'=>'Font Out Animation',
            'type'=>'select',
            'name'=>'font_out_animation',
            'value'=>array_rand($animation),
            'options'=>$animation
        ],
        [
            'label'=>'Font Out Direction',
            'type'=>'select',
            'name'=>'font_out_direction',
            'value'=>array_rand($direction),
            'options'=>$direction
        ],
        ];
        return $settings;
    }

    public static function getTemplate48Settings()
    {
        $settings=[];
        return $settings;
    }

    public static function getTemplate36Settings()
    {
        $settings=[];

        $settings[] = [
            'label' => 'Pin Top',
            'type' => 'number',
            'name' => 'distanceTop',
            'value' => '0'
        ];

        $settings[] = [
            'label' => 'Pin Left',
            'type' => 'number',
            'name' => 'distanceLeft',
            'value' => '0'
        ];
        $settings[] = [
            'label' => 'Pin Width',
            'type' => 'number',
            'name' => 'distanceWidth',
            'value' => '115'
        ];
        $settings[] = [
            'label' => 'Pin Height',
            'type' => 'number',
            'name' => 'distanceHeight',
            'value' => '50'
        ];


        $settings[] = [
        'label'=>'Loop Speed',
        'type'=>'loop',
        'name'=>'loop_speed',
        'value'=>0,
        'multiply'=>5,
        'min'=>-25,
        'max'=>25,
        ];
        $settings[] = [
        'label'=>'Use Customisable Distance Pin?',
        'type'=>'checkbox',
        'name'=>'customisable_pin_yn',
        'value'=>false
        ];
        $settings[] = [
        'type'=>'upload',
        'name'=>'pin_img',
        'label'=>'Upload New Distance Window Pin',
        'allow_empty'=>true,
        'value'=>"/templates/pin/template36/svg.svg"
        ];
        $settings[] = [
        'label'=>'Pin Background Color',
        'type'=>'color',
        'name'=>'distancebackgroundColor',
        'value'=>'#FFFFFF'
        ];
        $settings[] = [
        'label'=>'Pin Top Color',
        'type'=>'color',
        'name'=>'distanceTopColor',
        'value'=>'#759AC6'
        ];
        $settings[] = [
        'label'=>'Pin Bottom Color',
        'type'=>'color',
        'name'=>'distanceBottomColor',
        'value'=>'#075D86'
        ];
        $settings[] = [
        'label'=>'Pin Inner-Circle Color',
        'type'=>'color',
        'name'=>'distanceInnerCircleColor',
        'value'=>'#075D86'
        ];

        $settings[] = [
        'type'=>'text',
        'name'=>'distance_value_text',
        'label'=>'Display Message',
        'allow_empty'=>true,
        'value'=>"%%DISTANCE%%"
        ];

        $settings[] = [
        'type'=>'text',
        'name'=>'from_location',
        'label'=>'From your location',
        'allow_empty'=>true,
        'value'=>"From your location"
        ];


        $settings[] = [
        'type'=>'font',
        'name'=>'fonts_all',
        'label'=>'Google Fonts',
        'value'=>"Open Sans" //Please use google fonts
        ];

        $settings[] = [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
        ];

        $settings[] = [
        'label'=>'Font Colour',
        'type'=>'color', //uses text box and comes with color picker
        'name'=>'font_color',
        'value'=>'#070275'
        ];


        $settings[] = [
        'label'=>'Font Size',
        'type'=>'font_size',
        'name'=>'font_size',
        'value'=>'13'
        ];

        $settings[] = [
        'label'=>'Font Bold',
        'type'=>'checkbox',
        'name'=>'font_bold',
        'type'=>'checkbox',
        'value'=>false
        ];




        $settings[] = [
        'label'=>'Font Underline',
        'type'=>'checkbox',
        'name'=>'font_underline',
        'type'=>'checkbox',
        'value'=>false
        ];

        $settings[] = [
        'label'=>'Font Italics',
        'type'=>'checkbox',
        'name'=>'font_italics',
        'type'=>'checkbox',
        'value'=>false
        ];



        return $settings;
    }

    public static function getTemplate35Settings()
    {
        $settings=[];


        $settings[] = [
        'label'=>'Loop Speed',
        'type'=>'loop',
        'name'=>'loop_speed',
        'value'=>20000,
        'multiply'=>1000,
        'min'=>0,
        'max'=>10,
        ];
        $settings[] = [
        'label'=>'Use Customisable Distance Pin?',
        'type'=>'checkbox',
        'name'=>'customisable_pin_yn',
        'value'=>false
        ];
        $settings[] = [
        'type'=>'upload',
        'name'=>'pin_img',
        'label'=>'Upload New Distance Window Pin',
        'allow_empty'=>true,
        'value'=>"/templates/pin/template35/default.png"
        ];
        $settings[] = [
        'label'=>'Pin Background Color',
        'type'=>'color',
        'name'=>'distancebackgroundColor',
            'value'=>'#ffffff'

        ];
        $settings[] = [
        'label'=>'Pin Top Color',
        'type'=>'color',
        'name'=>'distanceTopColor',
        'value'=>'#759AC6'
        ];
        $settings[] = [
        'label'=>'Pin Bottom Color',
        'type'=>'color',
        'name'=>'distanceBottomColor',
        'value'=>'#075D86'
        ];
        $settings[] = [
        'label'=>'Pin Inner-Circle Color',
        'type'=>'color',
        'name'=>'distanceInnerCircleColor',
        'value'=>'#075D86'
        ];


        $settings[] = [
            'label'=>'Background Color',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'backgroundColor',
            'value'=>'#ffffff'
        ];
        $settings[] = [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>true,
            'value'=>"%%DISTANCE%% from your location"
        ];

        $settings[] = [
        'type'=>'font',
        'name'=>'fonts_all',
        'label'=>'Google Fonts',
        'value'=>"Open Sans"
        ];

        $settings[] = [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
        ];

        $settings[] = [
        'label'=>'Font Colour',
        'type'=>'color',
        'name'=>'font_color',
        'value'=>'#070275'
        ];


        $settings[] = [
        'label'=>'Font Size',
        'type'=>'font_size',
        'name'=>'font_size',
        'value'=>'13'
        ];


        $settings[] = [
        'label'=>'Font Bold',
        'type'=>'checkbox',
        'name'=>'font_bold',
        'type'=>'checkbox',
        'value'=>false
        ];


        $settings[] = [
        'label'=>'Font Underline',
        'type'=>'checkbox',
        'name'=>'font_underline',
        'type'=>'checkbox',
        'value'=>false
        ];

        $settings[] = [
        'label'=>'Font Italics',
        'type'=>'checkbox',
        'name'=>'font_italics',
        'type'=>'checkbox',
        'value'=>false
        ];

        return $settings;
    }
    public static function getTemplate31Settings()
    {
        $settings = [
        [
        'label'=>'Loop Speed',
        'type'=>'loop',
        'name'=>'loop_speed',
        'value'=>3,
        'multiply'=>1000,
        'min'=>1,
        'max'=>20,
        ],
        [
        'label'=>'Use Customisable Distance Pin?',
        'type'=>'checkbox',
        'name'=>'customisable_pin_yn',
        'value'=>true,
        ],
        [
        'type'=>'upload',
        'name'=>'pin_img',
        'label'=>'Upload New Distance Window Pin',
        'allow_empty'=>true,
        'value'=>"https://static.vic-m.co/templates/pin/template31/default.png",
        ],
        [
        'label'=>'Pin Background Color',
        'type'=>'color',
        'name'=>'distancebackgroundColor',
        'value'=>'#FFFFFF'
        ],
        [
        'label'=>'Pin Top Color',
        'type'=>'color',
        'name'=>'distanceTopColor',
        'value'=>'#759AC6'
        ],
        [
        'label'=>'Pin Bottom Color',
        'type'=>'color',
        'name'=>'distanceBottomColor',
        'value'=>'#075D86'
        ],
        [
        'label'=>'Pin Inner-Circle Color',
        'type'=>'color',
        'name'=>'distanceInnerCircleColor',
        'value'=>'#075D86'
        ],

        [
        'type'=>'text',
        'name'=>'distance_value_text',
        'label'=>'Display Message',
        'allow_empty'=>false,
        'value'=>"%%DISTANCE%%",
        ],
        [
        'type'=>'text',
        'name'=>'from_location',
        'label'=>'Location Address',
        'allow_empty'=>true,
        'value'=>"From your location"
        ],
        [
        'type'=>'font',
        'name'=>'fonts_all',
        'label'=>'Google Fonts',
        'value'=>"Open Sans" //Please use google fonts
        ],
        [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
        ],
        [
        'label'=>'Font Colour',
        'type'=>'color', //uses text box and comes with color picker
        'name'=>'font_color',
        'value'=>'#175582'
        ],
        [
        'label'=>'Font Bold',
        'type'=>'checkbox',
        'name'=>'font_bold',
        'type'=>'checkbox',
        'value'=>false
        ],

        [
        'label'=>'Font Underline',
        'type'=>'checkbox',
        'name'=>'font_underline',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'label'=>'Font Italics',
        'type'=>'checkbox',
        'name'=>'font_italics',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'label'=>'Font Size',
        'type'=>'font_size',
        'name'=>'font_size',
        'value'=>'13'
        ]
        ];

        return $settings;
    }

    public static function getTemplate149Settings(){
        $settings = [
            [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>false,
            'value'=>"%%DISTANCE%%",
            ],
        ];

        return $settings;
    }


    public static function getTemplate91Settings(){

        $settings = [];

        $widget_categories = WidgetCategories::all();
        $wselectOptions = [];
        $selectedValues = [];
        $wCategoryGroupLabels['special'] = 'Special Categories';
        $wCategoryGroupLabels['normal'] = 'Normal Categories';

        if(!empty($widget_categories)) {
            foreach ($widget_categories AS $cat) {
                if(in_array($cat->id, array_values(WidgetCategories::$specialCategories))){
                    $wselectOptions['special'][$cat->id] = ucwords($cat->categoriesName);
                }else{
                    $wselectOptions['normal'][$cat->id] = ucwords($cat->categoriesName);
                }
            }
        }

        $presetIcons = LandingSettings::getPresetIcons();
        //Create a list of options for the dropdown selector on CDDW
        $engagementIcons = [];
        foreach($presetIcons as $icon){
            
            if(in_array($icon['id'], ['call', 'whatsapp', 'drive', 'walk', 'web'])){
                $icon['name'] = str_replace('WhatsApp', 'Share', $icon['name']);
                $engagementIcons[$icon['id']] = $icon['name'];
            }
        }

        $calcSelectOptions = [];
        $optGroupLabels = NearMeCalculator::getCategoryName();

        $allCalculators = NearMeCalculator::getCalculators();

        if(!empty($allCalculators)){
            foreach($allCalculators as $category => $calculators){
                foreach($calculators as $calcId => $calculator){
                    $calcSelectOptions[$category][$calcId] = $calculator['label'];
                }
            }
        }

        $settings = [
        [
          'label'=>'Weather Icon Size',
          'type'=>'select',
          'name'=>'widthClass',
          'value'=>'small',
          'options'=>[
              'fullWidth'=>"Full Width",
              'small'=>"Small Icon",
          ]
        ],
        [
          'label'=>'Turn On/Off Engagements on Listing',
          'type'=>'select',
          'name'=>'enableEngagementsOnListing',
          'value'=>true,
          'options'=>[
              'false'=>"Off",
              'true'=>"On",
          ]
        ],
        [
            'label'=>'Outer Circle',
            'type'=>'color',
            'name'=>'outerCircleColourEngagementsOnListing',
            'value'=>'#000000'
        ],
        [
            'label'=>'Inner Circle',
            'type'=>'color',
            'name'=>'innerCircleColourEngagementsOnListing',
            'value'=>'#000000'
        ],
        [
            'label'=>'Icon Color',
            'type'=>'color',
            'name'=>'iconColourEngagementsOnListing',
            'value'=>'#ffffff'
        ],
        [
            'label'=>'Text Color',
            'type'=>'color',
            'name'=>'textColourEngagementsOnListing',
            'value'=>'#000000'
        ],
        [
            'label'=>'Underline Color',
            'type'=>'color',
            'name'=>'underlineColourEngagementsOnListing',
            'value'=>'#000000'
        ],
        [
            "label"=> "Select which icons to display on engagements",
            "type"=> "select-multiple",
            "name" => "selectEngagementOnListingIcons",
            "value" => [],
            "options" => $engagementIcons
        ],
        [
          'label'=>'Widget Language',
          'type'=>'select',
          'name'=>'widgetLanguage',
          'value'=>'English',
          'options'=>[
              'English'=>"English",
              'Afrikaans'=>"Afrikaans",
          ]
        ],
        [
          'label'=>'Position of Weather Icon',
          'type'=>'select',
          'name'=>'weatherPosition',
          'value'=>3,
          'options'=>[
              'first'=>"First",
              'last'=>"Last",
              'middle'=>"Middle",
              'random'=>"Random",
          ]
        ],

        [
          'label'=>'Weather Main Heading Text Size',
          'type'=>'font_size',
          'name'=>'weatherMainHeadingSize',
          'value'=>'14'
        ],
        [
          'label'=>'Full Size Forecast Text Size',
          'type'=>'font_size',
          'name'=>'weatherFullWidthTextSize',
          'value'=>'12',
          'min' => 10,
          'max' => 16,
        ],
        [
          'label'=>'Full Size Day Label Text Size',
          'type'=>'font_size',
          'name'=>'weatherFullWidthDayLabelTextSize',
          'value'=>'12',
          'min' => 10,
          'max' => 19,
        ],
        [
          'type'=>'color',
          'name'=>'weatherFullWidthTextColor',
          'label'=>'Full Size Forecast Text Colour',
          'value'=>'#000000'
        ],
        [
          'type'=>'color',
          'name'=>'weatherMainHeadingColour',
          'label'=>'Weather Main Heading Text Colour',
          'value'=>'#000000'
        ],
        [
            'label'=>'Loop Speed (seconds)',
            'type'=>'loop',
            'name'=>'loop_speed',
            'value'=>10,
            'multiply'=>1,
            'min'=>1,
            'max'=>25,
        ],
        [
          'type'=>'text',
          'name'=>'distance_value_text',
          'label'=>'Display Message',
          'allow_empty'=>true,
          'value'=>"Near",
        ],
        [
          'type'=>'text',
          'name'=>'from_location',
          'label'=>'Location Address',
          'allow_empty'=>true,
          'value'=>"Me"
        ],
        [
          'type'=>'font',
          'name'=>'fonts_all',
          'label'=>'Google Fonts',
          'value'=>"Open Sans" //Please use google fonts
        ],
        [
          'label'=>'Auto Play',
          'type'=>'select',
          'name'=>'auto_play_icon',
          'value'=>true,
          'options'=>[
              'false'=>"Off",
              'true'=>"On",
          ]
        ],
        [
          'label'=>'Background Colour',
          'type'=>'color',
          'name'=>'backgroundColorFinePanel',
          'value'=>'#ffffff'
        ],
        [
        'label'=>'Pin Top Color',
        'type'=>'color',
        'name'=>'distanceTopColorGeo',
        'value'=>'#759AC6'
        ],
        // [
        //   'type'=>'text',
        //   'name'=>'geoip_heading',
        //   'label'=>'GeoIP Heading',
        //   'value'=>'Please activate your location and click refresh below :-)'
        // ],
        // [
        //   'type'=>'text',
        //   'name'=>'geoip_address',
        //   'label'=>'GeoIP Address',
        //   'value'=>"If you do not get a pop-up to share location you may have blocked this site! \n\nOpen browser settings > Open - Site Settings > then Location > the Blocked list > then Click on the site name and Allow."
        // ],
        [
            'label'=>'Pin Bottom Color',
            'type'=>'color',
            'name'=>'distanceBottomColorGeo',
            'value'=>'#075D86'
        ],
        [
            'label'=>'Pin Inner-Circle Color',
            'type'=>'color',
            'name'=>'distanceInnerCircleColorGeo',
            'value'=>'#075D86'
        ],
        [
            'label'=>'Line Separator Colour',
            'type'=>'color',
            'name'=>'lineColor',
            'value'=>'#001FFF'
        ],
        [
          'label'=>'Background Colour',
          'type'=>'color',
          'name'=>'backgroundColorGeoPanel',
          'value'=>'#ffffff'
        ],
        [
          'type'=>'color',
          'name'=>'panelFineTextColor',
          'label'=>'Fine Panel Text Colour',
          'value'=>'#000000',
        ],
        [
          'type'=>'color',
          'name'=>'panelGeoTextColor',
          'label'=>'GeoIP Panel Text Colour',
          'value'=>'#000000',
        ],
        [
          'label'=>'Fine Panel Text Size',
          'type'=>'font_size',
          'name'=>'panelFineTextSize',
          'value'=>'15'
        ],
        [
          'label'=>'GeoIP Panel Text Size',
          'type'=>'font_size',
          'name'=>'panelGeoTextSize',
          'value'=>'14'
        ],
        [
          'label'=>'Icon Distance Text Size',
          'type'=>'font_size',
          'name'=>'iconDistanceSize',
          'value'=>'15'
        ],
        [
          'type'=>'color',
          'name'=>'iconDistanceColour',
          'label'=>'Icon Distance Text Colour',
          'value'=>'#000000'
        ],
        [
          'type'=>'color',
          'name'=>'nearTopPin',
          'label'=>'Near Me Top Pin Colour',
          'value'=>'#759ac6',
        ],
        [
          'type'=>'color',
          'name'=>'nearCirclePin',
          'label'=>'Near Me Circle Colour',
          'value'=>'#759ac6',
        ],
        [
          'type'=>'color',
          'name'=>'nearBottomPin',
          'label'=>'Near Me Bottom Pin Colour',
          'value'=>'#FFFFFF',
        ],
        [
          'type'=>'color',
          'name'=>'closeTextColor',
          'label'=>'Close Button Text Colour',
          'value'=>'#000000',
        ],
        [
          'type'=>'color',
          'name'=>'loaderColor',
          'label'=>'Loader Colour',
          'value'=>'#759AC6',
        ],
        [
          'type'=>'color',
          'name'=>'refreshColor',
          'label'=>'Refresh Colour',
          'value'=>'#759AC6',
        ],
        [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
        ],
        [
          'label'=>'Font Colour',
          'type'=>'color', //uses text box and comes with color picker
          'name'=>'font_color',
          'value'=>'#4D5E6F'
        ],
        [
          'label'=>'Font Bold',
          'type'=>'checkbox',
          'name'=>'font_bold',
          'type'=>'checkbox',
          'value'=>false
        ],
        [
          'label'=>'Font Bold',
          'type'=>'checkbox',
          'name'=>'font_bold_fine',
          'type'=>'checkbox',
          'value'=>false
        ],
        [
          'label'=>'Font Bold',
          'type'=>'checkbox',
          'name'=>'font_bold_geo',
          'type'=>'checkbox',
          'value'=>false
        ],
        [
          'label'=>'Font Underline',
          'type'=>'checkbox',
          'name'=>'font_underline',
          'type'=>'checkbox',
          'value'=>false
        ],
        [
          'label'=>'Font Underline',
          'type'=>'checkbox',
          'name'=>'font_underline_fine',
          'type'=>'checkbox',
          'value'=>false
        ],
        [
          'label'=>'Font Underline',
          'type'=>'checkbox',
          'name'=>'font_underline_geo',
          'type'=>'checkbox',
          'value'=>false
        ],
        [
          'label'=>'Font Italics',
          'type'=>'checkbox',
          'name'=>'font_italics',
          'type'=>'checkbox',
          'value'=>false
        ],
        [
          'label'=>'Font Italics',
          'type'=>'checkbox',
          'name'=>'font_italics_fine',
          'type'=>'checkbox',
          'value'=>false
        ],
        [
          'label'=>'Font Italics',
          'type'=>'checkbox',
          'name'=>'font_italics_geo',
          'type'=>'checkbox',
          'value'=>false
        ],
        [
          'label'=>'Font Size',
          'type'=>'font_size',
          'name'=>'font_size',
          'value'=>'15'
        ],
        [
          'label'=>'Near Me Background Colour',
          'type'=>'color',
          'name'=>'backgroundColorNear',
          'value'=>'#CBD1DA'
        ],
        [
          'label'=>'Near Me Curves (px)',
          'type'=>'border_radius',
          'name'=>'nearmeBorderRadius',
          'value'=>'15'
        ],
        [
          'label'=>'Navigation Arrow Style',
          'type'=>'select',
          'name'=>'navigation_arrows_icon',
          'value'=>5,
          'options'=>[
              'none'=>"None",
              '1'=>"Style 1: Chevron",
              '2'=>"Style 2: Caret",
              '4'=>"Style 3: Arrow",
              '5'=>"Style 4: Chevron-Circle",
              '6'=>"Style 5: Caret-Square",
              '7'=>"Style 6: Long-Arrow",
              '8'=>"Style 7: Arrow-Alt",
          ]
        ],

        [
          'label'=>'Navigation Arrow Colour',
          'type'=>'color', //uses text box and comes with color picker
          'name'=>'nav_color_icon',
          'value'=>'rgba(0,0,0,0.81)'
        ],
        [
          'label'=>'Navigation Arrow Size',
          'type'=>'number',
          'name'=>'nav_size_icon',
          'value'=>'18',
        ],
        [
          'label'=>'Carousel Tracker',
          'type'=>'hidden',
          'name'=>'carousel_images',
          'value'=>"/templates/pin/template42/Svg.svg",
        ],
        [
          'label'=>'',
          'type'=>'hidden',
          'name'=>'carousel_order',
          'value'=>"",
        ],
        [
          'label' => 'Nav Dots',
          'type' => 'hidden',
          'name' => 'widget_dots',
          'value' => true,
          'options' => [
              'false' => "No",
              'true' => "Yes",
          ]
        ],
        [
          'label'=>'Pin Size',
          'type'=>'hidden',
          'name'=>'pin_size',
          'value'=>'30',
        ],
        [
            'label'=>'Background Color',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'operating_hours_background',
            'value'=>'rgba(203, 209, 218, 1)'
        ],
        [
            'label'=>'Text Color',
            'type'=>'color',
            'name'=>'operating_hours_text_color',
            'value'=>'rgba(51, 51, 51, 1)'
        ],
        [
            'label'     =>'Select which categories to switch on/off',
            'type'      =>'select-multiple-optgroup',
            'name'      =>'enableCategories',
            'value'     => $selectedValues,
            'options'   => $wselectOptions,
            'optGroupLabels' => $wCategoryGroupLabels,
        ],
        [
            'label'     =>'',
            'type'      =>'hidden',
            'name'      =>'hiddenCategories',
            'value'     => ''
        ],
        [
            'label'     =>'Select which Calculators to display',
            'type'      =>'select-multiple-optgroup',
            'name'      =>'enableCalculators',
            'value'     => [],
            'options'   => $calcSelectOptions,
            'optGroupLabels' => $optGroupLabels,
        ],
        [
            'label'=>'Calculator Height (px)',
            'type'=>'border_radius',
            'name'=>'calculatorHeight',
            'value'=>'150'
          ],
          [
              'label'=>'Save Background Color',
              'type'=>'color',
              'name'=>'dailyDealsSaveBgColor',
              'value'=>'#E10613'
          ],
          [
              'label'=>'Save Text Color',
              'type'=>'color',
              'name'=>'dailyDealsSaveTextColor',
              'value'=>'#ffffff'
          ],
          [
            'label'=>'Save Font Size',
            'type'=>'font_size',
            'min' => 8,
            'max' => 19,
            'name'=>'dailyDealsSaveFontSize',
            'value'=> 12
          ], //End Save Btn
          [
              'label'=>'Sale Price Text Color',
              'type'=>'color',
              'name'=>'dailyDealsSalePriceTextColor',
              'value'=>'#E10613'
          ],
          [
            'label'=>'Sale Price Font Size',
            'type'=>'font_size',
            'min' => 8,
            'max' => 19,
            'name'=>'dailyDealsSalePriceFontSize',
            'value'=> 12
          ], //End Sale Price Config
          [
              'label'=>'Discount Price Text Color',
              'type'=>'color',
              'name'=>'dailyDealsDiscountPriceTextColor',
              'value'=>'#ffffff'
          ],
          [
            'label'=>'Discount Price Font Size',
            'type'=>'font_size',
            'min' => 8,
            'max' => 19,
            'name'=>'dailyDealsDiscountPriceFontSize',
            'value'=> 12
          ], //End Discount Price Config
          [
              'label'=>'Product Name Text Color',
              'type'=>'color',
              'name'=>'dailyDealsProductNameTextColor',
              'value'=>'#000000'
          ],
          [
            'label'=>'Product Name Font Size',
            'type'=>'font_size',
            'min' => 8,
            'max' => 19,
            'name'=>'dailyDealsProductNameFontSize',
            'value'=> 12
          ], //End ProductName Config

        ];
        return $settings;

    }

    public static function getTemplate86Settings()
    {
        $settings = [
        [
        'label'=>'Loop Speed',
        'type'=>'loop',
        'name'=>'loop_speed',
        'value'=>3,
        'multiply'=>1000,
        'min'=>1,
        'max'=>20,
        ],
        [
        'label'=>'Use Customisable Distance Pin?',
        'type'=>'checkbox',
        'name'=>'customisable_pin_yn',
        'value'=>true,
        ],
        [
        'type'=>'upload',
        'name'=>'pin_img',
        'label'=>'Upload New Distance Window Pin',
        'allow_empty'=>true,
        'value'=>"https://static.vic-m.co/templates/pin/template31/default.png",
        ],
        [
        'label'=>'Pin Background Color',
        'type'=>'color',
        'name'=>'distancebackgroundColor',
        'value'=>'#FFFFFF'
        ],
        [
        'label'=>'Pin Top Color',
        'type'=>'color',
        'name'=>'distanceTopColor',
        'value'=>'#759AC6'
        ],
        [
        'label'=>'Pin Bottom Color',
        'type'=>'color',
        'name'=>'distanceBottomColor',
        'value'=>'#075D86'
        ],
        [
        'label'=>'Pin Inner-Circle Color',
        'type'=>'color',
        'name'=>'distanceInnerCircleColor',
        'value'=>'#075D86'
        ],

        [
        'type'=>'text',
        'name'=>'distance_value_text',
        'label'=>'Display Message',
        'allow_empty'=>false,
        'value'=>"%%DISTANCE%%",
        ],
        [
        'type'=>'text',
        'name'=>'from_location',
        'label'=>'Location Address',
        'allow_empty'=>true,
        'value'=>"From your location"
        ],
        [
        'type'=>'font',
        'name'=>'fonts_all',
        'label'=>'Google Fonts',
        'value'=>"Open Sans" //Please use google fonts
        ],
        [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
        ],
        [
        'label'=>'Font Colour',
        'type'=>'color', //uses text box and comes with color picker
        'name'=>'font_color',
        'value'=>'#175582'
        ],
        [
        'label'=>'Font Bold',
        'type'=>'checkbox',
        'name'=>'font_bold',
        'type'=>'checkbox',
        'value'=>false
        ],

        [
        'label'=>'Font Underline',
        'type'=>'checkbox',
        'name'=>'font_underline',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'label'=>'Font Italics',
        'type'=>'checkbox',
        'name'=>'font_italics',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'label'=>'Font Size',
        'type'=>'font_size',
        'name'=>'font_size',
        'value'=>'13'
        ]
        ];

        return $settings;
    }

    public static function getTemplate93Settings()
    {
        $settings = [];

        return $settings;

    }

    public static function getTemplate94Settings()
    {
        $settings = [];

        return $settings;

    }

    public static function getTemplate34Settings()
    {
        $settings=[];

        $settings[] = [
        'label'=>'Loop Speed',
        'type'=>'loop',
        'name'=>'loop_speed',
        'value'=>20000,
        'multiply'=>1000,
        'min'=>0,
        'max'=>10,
        ];

        $settings[] = [
        'label'=>'Use Customisable Distance Pin?',
        'type'=>'checkbox',
        'name'=>'customisable_pin_yn',
        'value'=>false
        ];
        $settings[] = [
        'type'=>'upload',
        'name'=>'pin_img',
        'label'=>'Upload New Distance Window Pin',
        'allow_empty'=>true,
        'value'=>"/templates/pin/template34/default.svg"
        ];
        $settings[] = [
        'label'=>'Pin Background Color',
        'type'=>'color',
        'name'=>'distancebackgroundColor',
        'value'=>'#FFFFFF'
        ];
        $settings[] = [
        'label'=>'Pin Top Color',
        'type'=>'color',
        'name'=>'distanceTopColor',
        'value'=>'#759AC6'
        ];
        $settings[] = [
        'label'=>'Pin Bottom Color',
        'type'=>'color',
        'name'=>'distanceBottomColor',
        'value'=>'#075D86'
        ];
        $settings[] = [
        'label'=>'Pin Inner-Circle Color',
        'type'=>'color',
        'name'=>'distanceInnerCircleColor',
        'value'=>'#075D86'
        ];

        $settings[] = [
        'label'=>'Background Color',
        'type'=>'color', //uses text box and comes with color picker
        'name'=>'backgroundColor',
        'value'=>'#ffffff'
        ];
        $settings[] = [
        'type'=>'text',
        'name'=>'distance_value_text',
        'label'=>'Display Message',
        'allow_empty'=>true,
        'value'=>"%%DISTANCE%% from your location"
        ];

        $settings[] = [
        'type'=>'font',
        'name'=>'fonts_all',
        'label'=>'Google Fonts',
        'value'=>"Open Sans"
        ];

        $settings[] = [
        'type'=>'custom_font',
        'name'=>'custom_font',
        'label'=>'Custom Fonts',
        'value'=>''
        ];

        $settings[] = [
        'label'=>'Font Colour',
        'type'=>'color',
        'name'=>'font_color',
        'value'=>'#070275'
        ];



        $settings[] = [
        'label'=>'Font Size',
        'type'=>'font_size',
        'name'=>'font_size',
        'value'=>'13'
        ];


        $settings[] = [
        'label'=>'Font Bold',
        'type'=>'checkbox',
        'name'=>'font_bold',
        'type'=>'checkbox',
        'value'=>false
        ];


        $settings[] = [
        'label'=>'Font Underline',
        'type'=>'checkbox',
        'name'=>'font_underline',
        'type'=>'checkbox',
        'value'=>false
        ];

        $settings[] = [
        'label'=>'Font Italics',
        'type'=>'checkbox',
        'name'=>'font_italics',
        'type'=>'checkbox',
        'value'=>false
        ];



        return $settings;
    }
    public static function getTemplate33Settings()
    {
        $settings=[];



        $settings[] = [
        'label'=>'Banner Display Time(s)',
        'type'=>'loop',
        'name'=>'loop_speed',
        'value'=>20000,
        'multiply'=>1000,
        'min'=>0,
        'max'=>10,
        ];


        $settings[] = [
        'label'=>'Use Customisable Distance Pin?',
        'type'=>'checkbox',
        'name'=>'customisable_pin_yn',
        'value'=>false
        ];
        $settings[] = [
        'type'=>'upload',
        'name'=>'pin_img',
        'label'=>'Upload New Distance Window Pin',
        'allow_empty'=>true,
        'value'=>"/templates/pin/template33/default.png"
        ];
        $settings[] = [
        'label'=>'Pin Background Color',
        'type'=>'color',
        'name'=>'distancebackgroundColor',
        'value'=>'#FFFFFF'
        ];
        $settings[] = [
        'label'=>'Pin Top Color',
        'type'=>'color',
        'name'=>'distanceTopColor',
        'value'=>'#759AC6'
        ];
        $settings[] = [
        'label'=>'Pin Bottom Color',
        'type'=>'color',
        'name'=>'distanceBottomColor',
        'value'=>'#075D86'
        ];
        $settings[] = [
        'label'=>'Pin Inner-Circle Color',
        'type'=>'color',
        'name'=>'distanceInnerCircleColor',
        'value'=>'#075D86'
        ];

        $settings[] = [
        'label'=>'Background Color',
        'type'=>'color', //uses text box and comes with color picker
        'name'=>'backgroundColor',
        'value'=>'#ffffff'
        ];

        $settings[] = [
        'type'=>'font',
        'name'=>'fonts_all',
        'label'=>'Google Fonts',
        'value'=>"Open Sans"
        ];

        $settings[] = [
        'type'=>'custom_font',
        'name'=>'custom_font',
        'label'=>'Custom Fonts',
        'value'=>''
        ];

        $settings[] = [
        'label'=>'Font Colour',
        'type'=>'color',
        'name'=>'font_color',
        'value'=>'#070275'
        ];


        $settings[] = [
        'label'=>'Font Size',
        'type'=>'font_size',
        'name'=>'font_size',
        'value'=>'13'
        ];

        $settings[] = [
        'label'=>'Font Bold',
        'type'=>'checkbox',
        'name'=>'font_bold',
        'type'=>'checkbox',
        'value'=>false
        ];


        $settings[] = [
        'label'=>'Font Underline',
        'type'=>'checkbox',
        'name'=>'font_underline',
        'type'=>'checkbox',
        'value'=>false
        ];

        $settings[] = [
        'label'=>'Font Italics',
        'type'=>'checkbox',
        'name'=>'font_italics',
        'type'=>'checkbox',
        'value'=>false
        ];


        $settings[] = [
        'type'=>'text',
        'name'=>'distance_value_text',
        'label'=>'Display Message',
        'allow_empty'=>true,
        'value'=>"%%DISTANCE%% from your location"
        ];

        return $settings;
    }
    public static function getTemplate32Settings()
    {
        $settings=[
        [
        'label'=>'Use Customisable Distance Pin?',
        'type'=>'checkbox',
        'name'=>'customisable_pin_yn',
        'value'=>true
        ],
        [
        'type'=>'upload',
        'name'=>'pin_img',
        'label'=>'Upload New Distance Window Pin',
        'allow_empty'=>true,
        'value'=>"/templates/pin/template32/svg.svg"
        ],
        [
        'label'=>'Pin Background Color',
        'type'=>'color',
        'name'=>'distancebackgroundColor',
        'value'=>'#000'
        ],
        [
        'label'=>'Pin Top Color',
        'type'=>'color',
        'name'=>'distanceTopColor',
        'value'=>'#759AC6'
        ],
        [
        'label'=>'Pin Bottom Color',
        'type'=>'color',
        'name'=>'distanceBottomColor',
        'value'=>'#075D86'
        ],
        [
        'label'=>'Pin Inner-Circle Color',
        'type'=>'color',
        'name'=>'distanceInnerCircleColor',
        'value'=>'#075D86'
        ],
        [
        'type'=>'font',
        'name'=>'fonts_all',
        'label'=>'Google Fonts',
        'value'=>"Open Sans"
        ],
        [
        'type'=>'custom_font',
        'name'=>'custom_font',
        'label'=>'Custom Fonts',
        'value'=>''
        ],
        [
        'label'=>'Font Colour',
        'type'=>'color',
        'name'=>'font_color',
        'value'=>'#070275'
        ],
        [
        'label'=>'Font Size',
        'type'=>'font_size',
        'name'=>'font_size',
        'value'=>'13'
        ],
        [
        'label'=>'Font Bold',
        'type'=>'checkbox',
        'name'=>'font_bold',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'label'=>'Font Underline',
        'type'=>'checkbox',
        'name'=>'font_underline',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'label'=>'Font Italics',
        'type'=>'checkbox',
        'name'=>'font_italics',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'type'=>'text',
        'name'=>'distance_value_text',
        'label'=>'Display Message',
        'allow_empty'=>true,
        'value'=>"%%DISTANCE%%"
        ],
        [
        'type'=>'text',
        'name'=>'from_location',
        'label'=>'Location Address',
        'allow_empty'=>true,
        'value'=>"From your location"
        ]
        ];
        return $settings;
    }
    public static function getTemplate29Settings()
    {
        $settings=[
            [
            'label'=>'Loop Speed',
            'type'=>'loop',
            'name'=>'loop_speed',
            'value'=>'7000',
            'multiply'=>1000,
            'min'=>0,
            'max'=>20,
            ],
            [
            'label'=>'Use Customisable Distance Pin?',
            'type'=>'checkbox',
            'name'=>'customisable_pin_yn',
            'value'=>true
            ],
            [
            'type'=>'upload',
            'name'=>'pin_img',
            'label'=>'Upload New Distance Window Pin',
            'allow_empty'=>true,
            'value'=>"/templates/pin/template42/Svg.svg"
            ],
            [
            'label'=>'Pin Background Color',
            'type'=>'color',
            'name'=>'distancebackgroundColor',
            'value'=>'#FFFFFF'
            ],
            [
            'label'=>'Pin Top Color',
            'type'=>'color',
            'name'=>'distanceTopColor',
            'value'=>'#759AC6'
            ],
            [
            'label'=>'Pin Bottom Color',
            'type'=>'color',
            'name'=>'distanceBottomColor',
            'value'=>'#075D86'
            ],
            [
            'label'=>'Pin Inner-Circle Color',
            'type'=>'color',
            'name'=>'distanceInnerCircleColor',
            'value'=>'#075D86'
            ],
            [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>true,
            'value'=>"%%DISTANCE%%"
            ],
            [
            'type'=>'text',
            'name'=>'from_location',
            'label'=>'From your location',
            'allow_empty'=>true,
            'value'=>"From your location"
            ],
            [
            'type'=>'font',
            'name'=>'fonts_all',
            'label'=>'Google Fonts',
            'value'=>"Open Sans" //Please use google fonts
            ],
            [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
            ],
            [
            'label'=>'Font Colour',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#070275'
            ],
            [
            'label'=>'Font Size',
            'type'=>'font_size',
            'name'=>'font_size',
            'value'=>'13'
            ],
            [
            'label'=>'Font Bold',
            'type'=>'checkbox',
            'name'=>'font_bold',
            'type'=>'checkbox',
            'value'=>false
            ],
            [
            'label'=>'Font Underline',
            'type'=>'checkbox',
            'name'=>'font_underline',
            'type'=>'checkbox',
            'value'=>false
            ],
            [
            'label'=>'Font Italics',
            'type'=>'checkbox',
            'name'=>'font_italics',
            'type'=>'checkbox',
            'value'=>false
            ],
            [
            'label'=>'Opacity',
            'name'=>'Opacity',
            'type'=>'select',
            'value'=>0.1,
            'options'=>[
                '1'=>"100% Opacity",
                '0.8'=>"80% Opacity",
            ],
            ],
        ];

        return $settings;
    }
    public static function getTemplate40Settings()
    {
        $settings=[];
        $settings[] = [
            'label'=>'Loop Speed',
            'type'=>'loop',
            'name'=>'loop_speed',
            'value'=>0,
            'multiply'=>1,
            'min'=>-10,
            'max'=>10,
        ];
        $settings[] = [
            'label'=>'Use Customisable Distance Pin?',
            'type'=>'checkbox',
            'name'=>'customisable_pin_yn',
            'value'=>true
        ];
        $settings[] = [
            'type'=>'upload',
            'name'=>'pin_img',
            'label'=>'Upload New Distance Window Pin',
            'allow_empty'=>true,
            'value'=>"https://static.vic-m.co/templates/pin/template11/default.png",
        ];
        $settings[] = [
            'label'=>'Pin Background Color',
            'type'=>'color',
            'name'=>'distancebackgroundColor',
            'value'=>'#ffffff'
        ];
        $settings[] = [
            'label'=>'Pin Top Color',
            'type'=>'color',
            'name'=>'distanceTopColor',
            'value'=>'#759AC6'
        ];
        $settings[] = [
            'label'=>'Pin Bottom Color',
            'type'=>'color',
            'name'=>'distanceBottomColor',
            'value'=>'#075D86'
        ];
        $settings[] = [
            'label'=>'Pin Inner-Circle Color',
            'type'=>'color',
            'name'=>'distanceInnerCircleColor',
            'value'=>'#075D86'
        ];

        $settings[] = [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>true,
            'value'=>"%%DISTANCE%%"
        ];

        $settings[] = [
            'type'=>'font',
            'name'=>'fonts_all',
            'label'=>'Google Fonts',
            'value'=>"Open Sans" //Please use google fonts
        ];

        $settings[] = [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
        ];

        $settings[] = [
            'label'=>'Font Colour',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#070275'
        ];


        $settings[] = [
            'label'=>'Font Size',
            'type'=>'font_size',
            'name'=>'font_size',
            'value'=>'13'
        ];

        $settings[] = [
        'label'=>'Font Bold',
        'type'=>'checkbox',
        'name'=>'font_bold',
        'type'=>'checkbox',
        'value'=>false
        ];


        $settings[] = [
        'label'=>'Font Underline',
        'type'=>'checkbox',
        'name'=>'font_underline',
        'type'=>'checkbox',
        'value'=>false
        ];

        $settings[] = [
        'label'=>'Font Italics',
        'type'=>'checkbox',
        'name'=>'font_italics',
        'type'=>'checkbox',
        'value'=>false
        ];


        return $settings;
    }

    public static function getTemplate53Settings()
    {
        $settings[] = [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>true,
            'value'=>"%%DISTANCE%%"
        ];

        $settings[] = [
            'type'=>'font',
            'name'=>'fonts_all',
            'label'=>'Google Fonts',
            'value'=>"Open Sans" //Please use google fonts
        ];

        $settings[] = [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
        ];

        $settings[] = [
            'label'=>'Font Colour',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#FFFFFF'
        ];

        $settings[] = [
            'label'=>'Font Size',
            'type'=>'font_size',
            'name'=>'font_size',
            'value'=>'35'
        ];


        $settings[] = [
                'label'=>'Background Color',
                'type'=>'color',
                'name'=>'distancebackgroundColor',
                'value'=>'#C81617'
        ];
        $settings[] = [
                'label'=>'Pin Top Color',
                'type'=>'color',
                'name'=>'distanceTopColor',
                'value'=>'#FFFFFF'
        ];
        $settings[] = [
                'label'=>'Pin Bottom Color',
                'type'=>'color',
                'name'=>'distanceBottomColor',
                'value'=>'#FFFFFF'

        ];

        $settings[] = [
            'label'=>'Ball Color',
            'type'=>'color',
            'name'=>'ballColor',
            'value'=>'#FFF'
        ];
        $settings[] = [
            'label'=>'Ball Glow Color',
            'type'=>'color',
            'name'=>'ballGlow',
            'value'=>'#FFF'
        ];



        return $settings;
    }

    public static function getTemplate58Settings()
    {
        $settings[] = [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>true,
            'value'=>"%%DISTANCE%%"
        ];

        $settings[] = [
            'type'=>'font',
            'name'=>'fonts_all',
            'label'=>'Google Fonts',
            'value'=>"Open Sans" //Please use google fonts
        ];

        $settings[] = [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
        ];

        $settings[] = [
            'label'=>'Font Colour',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#FFFFFF'
        ];


        $settings[] = [
            'label'=>'Font Size',
            'type'=>'font_size',
            'name'=>'font_size',
            'value'=>'13'
        ];


        $settings[] = [
                'label'=>'Background Color',
                'type'=>'color',
                'name'=>'distancebackgroundColor',
                'value'=>'#C81617'
        ];
        $settings[] = [
                'label'=>'Pin Top Color',
                'type'=>'color',
                'name'=>'distanceTopColor',
                'value'=>'#FFFFFF'
        ];
        $settings[] = [
                'label'=>'Pin Bottom Color',
                'type'=>'color',
                'name'=>'distanceBottomColor',
                'value'=>'#FFFFFF'

        ];
        $settings[] = [
            'label'=>'Loop Speed',
            'type'=>'loop',
            'name'=>'loop_speed',
            'value'=>24,
            'multiply'=>1,
            'min'=>12,
            'max'=>36,
        ];

        $settings[] = [
            'label'=>'Ball Color',
            'type'=>'color',
            'name'=>'ballColor',
            'value'=>'#FFF'
        ];
        $settings[] = [
            'label'=>'Ball Glow Color',
            'type'=>'color',
            'name'=>'ballGlow',
            'value'=>'#FFF'
        ];



        return $settings;
    }

    public static function getTemplate62Settings()
    {
        $settings[] = [
            'label'=>'Loop Speed',
            'type'=>'loop',
            'name'=>'loop_speed',
            'value'=>24,
            'multiply'=>1,
            'min'=>12,
            'max'=>36,
        ];

        $settings[] = [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>true,
            'value'=>"%%DISTANCE%%"
        ];

        $settings[] = [
            'type'=>'font',
            'name'=>'fonts_all',
            'label'=>'Google Fonts',
            'value'=>"Open Sans" //Please use google fonts
        ];

        $settings[] = [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
        ];

        $settings[] = [
            'label'=>'Font Colour',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#FFFFFF'
        ];

        $settings[] = [
            'label'=>'Font Size',
            'type'=>'font_size',
            'name'=>'font_size',
            'value'=>'20'
        ];

        $settings[] = [
            'label'=>'Background Color',
            'type'=>'color',
            'name'=>'distancebackgroundColor',
            'value'=>'#A87847'
        ];

        $settings[] = [
            'label'=>'Pin Color',
            'type'=>'color',
            'name'=>'pinColor',
            'value'=>'#ffffff'
        ];

        $settings[] = [
            'label'=>'First Cup Color',
            'type'=>'color',
            'name'=>'firstColor',
            'value'=>'#ffffff'
        ];

        $settings[] = [
            'label'=>'Froth Color',
            'type'=>'color',
            'name'=>'froth',
            'value'=>'#B3845E'
        ];


        $settings[] = [
            'label'=>'Cuppucino',
            'type'=>'color',
            'name'=>'cuppucino',
            'value'=>'#B3845E'
        ];

        $settings[] = [
            'label'=>'Saucer Color',
            'type'=>'color',
            'name'=>'saucerColor',
            'value'=>'#E6E6E6'
        ];

        $settings[] = [
            'label'=>'Distance Pin Color',
            'type'=>'color',
            'name'=>'distanceTopColor',
            'value'=>'#ffffff'
        ];

        $settings[] = [
            'label'=>'Inner Cup Color',
            'type'=>'color',
            'name'=>'cupInner',
            'value'=>'#ffffff'
        ];

        $settings[] = [
            'label'=>'Milk Pour Color',
            'type'=>'color',
            'name'=>'milk',
            'value'=>'#E3DAD3'
        ];

        $settings[] = [
            'label'=>'Coffee Liquid',
            'type'=>'color',
            'name'=>'coffeeLiquid',
            'value'=>'#E3DAD3'
        ];


        return $settings;
    }
    public static function getTemplate63Settings()
    {
        $settings[] = [
            'label'=>'Loop Speed',
            'type'=>'loop',
            'name'=>'loop_speed',
            'value'=>24,
            'multiply'=>1,
            'min'=>12,
            'max'=>36,
        ];

        $settings[] = [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>true,
            'value'=>"%%DISTANCE%%"
        ];

        $settings[] = [
            'type'=>'font',
            'name'=>'fonts_all',
            'label'=>'Google Fonts',
            'value'=>"Open Sans" //Please use google fonts
        ];

        $settings[] = [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
        ];

        $settings[] = [
            'label'=>'Pin Color',
            'type'=>'color',
            'name'=>'pinColor',
            'value'=>'#ffffff'
        ];

        $settings[] = [
            'label'=>'Font Colour',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#FFFFFF'
        ];

        $settings[] = [
            'label'=>'Font Size',
            'type'=>'font_size',
            'name'=>'font_size',
            'value'=>'13',
            'multiply'=>100
        ];

        $settings[] = [
            'label'=>'Background Color',
            'type'=>'color',
            'name'=>'distancebackgroundColor',
            'value'=>'#A87847'
        ];


        $settings[] = [
            'label'=>'First Cup Color',
            'type'=>'color',
            'name'=>'firstColor',
            'value'=>'#ffffff'
        ];

        $settings[] = [
            'label'=>'Froth Color',
            'type'=>'color',
            'name'=>'froth',
            'value'=>'#B3845E'
        ];


        $settings[] = [
            'label'=>'Cuppucino',
            'type'=>'color',
            'name'=>'cuppucino',
            'value'=>'#B3845E'
        ];

        $settings[] = [
            'label'=>'Saucer Color',
            'type'=>'color',
            'name'=>'saucerColor',
            'value'=>'#E6E6E6'
        ];

        $settings[] = [
            'label'=>'Distance Pin Color',
            'type'=>'color',
            'name'=>'distanceBottomColor',
            'value'=>'#ffffff'
        ];

        $settings[] = [
            'label'=>'Inner Cup Color',
            'type'=>'color',
            'name'=>'cupInner',
            'value'=>'#ffffff'
        ];

        $settings[] = [
            'label'=>'Milk Pour Color',
            'type'=>'color',
            'name'=>'milk',
            'value'=>'#E3DAD3'
        ];

        $settings[] = [
            'label'=>'Coffee Liquid',
            'type'=>'color',
            'name'=>'coffeeLiquid',
            'value'=>'#E3DAD3'
        ];



        return $settings;
    }



    public static function getTemplate64Settings()
    {
        $settings[] = [
            'label'=>'Loop Speed',
            'type'=>'loop',
            'name'=>'loop_speed',
            'value'=>24,
            'multiply'=>1,
            'min'=>12,
            'max'=>36,
        ];


        $settings[] = [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>true,
            'value'=>"%%DISTANCE%%"
        ];

        $settings[] = [
            'type'=>'font',
            'name'=>'fonts_all',
            'label'=>'Google Fonts',
            'value'=>"Open Sans" //Please use google fonts
        ];

        $settings[] = [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
        ];

        $settings[] = [
            'label'=>'Font Colour',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#FFFFFF'
        ];

        $settings[] = [
            'label'=>'Phone Color',
            'type'=>'color',
            'name'=>'phone',
            'value'=>'#003300'
        ];

        $settings[] = [
            'label'=>'Phone Background Color',
            'type'=>'color',
            'name'=>'phoneBG',
            'value'=>'#009966'
        ];

        $settings[] = [
            'label'=>'Message Color',
            'type'=>'color',
            'name'=>'message',
            'value'=>'#0099FF'
        ];

        $settings[] = [
            'label'=>'Message Background Color',
            'type'=>'color',
            'name'=>'messageBG',
            'value'=>'#003366'
        ];



        $settings[] = [
            'label'=>'Wifi Color',
            'type'=>'color',
            'name'=>'wifi',
            'value'=>'#FFFFFF'
        ];

        $settings[] = [
            'label'=>'Wifi Background Color',
            'type'=>'color',
            'name'=>'wifiBG',
            'value'=>'#CC3333'
        ];


        $settings[] = [
            'label'=>'Font Size',
            'type'=>'font_size',
            'name'=>'font_size',
            'value'=>'30'
        ];

        $settings[] = [
            'label'=>'Background Color',
            'type'=>'color',
            'name'=>'distancebackgroundColor',
            'value'=>'#000'
        ];
        $settings[] = [
            'label'=>'Pin Top Color',
            'type'=>'color',
            'name'=>'distanceTopColor',
            'value'=>'#FFFFFF'
        ];
        $settings[] = [
            'label'=>'Pin Bottom Color',
            'type'=>'color',
            'name'=>'distanceBottomColor',
            'value'=>'#FFFFFF'
        ];


        return $settings;
    }

    public static function getTemplate65Settings()
    {
        $settings[] = [
            'label'=>'Loop Speed',
            'type'=>'loop',
            'name'=>'loop_speed',
            'value'=>24,
            'multiply'=>1,
            'min'=>12,
            'max'=>36,
        ];


        $settings[] = [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>true,
            'value'=>"%%DISTANCE%%"
        ];

        $settings[] = [
            'type'=>'font',
            'name'=>'fonts_all',
            'label'=>'Google Fonts',
            'value'=>"Open Sans" //Please use google fonts
        ];

        $settings[] = [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
        ];

        $settings[] = [
            'label'=>'Font Colour',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#FFFFFF'
        ];

        $settings[] = [
            'label'=>'Phone Color',
            'type'=>'color',
            'name'=>'phone',
            'value'=>'#003300'
        ];

        $settings[] = [
            'label'=>'Phone Background Color',
            'type'=>'color',
            'name'=>'phoneBG',
            'value'=>'#009966'
        ];

        $settings[] = [
            'label'=>'Message Color',
            'type'=>'color',
            'name'=>'message',
            'value'=>'#0099FF'
        ];

        $settings[] = [
            'label'=>'Message Background Color',
            'type'=>'color',
            'name'=>'messageBG',
            'value'=>'#003366'
        ];



        $settings[] = [
            'label'=>'Wifi Color',
            'type'=>'color',
            'name'=>'wifi',
            'value'=>'#FFFFFF'
        ];

        $settings[] = [
            'label'=>'Wifi Background Color',
            'type'=>'color',
            'name'=>'wifiBG',
            'value'=>'#CC3333'
        ];


        $settings[] = [
            'label'=>'Font Size',
            'type'=>'font_size',
            'name'=>'font_size',
            'value'=>'20'
        ];

        $settings[] = [
            'label'=>'Background Color',
            'type'=>'color',
            'name'=>'distancebackgroundColor',
            'value'=>'#000'
        ];
        $settings[] = [
            'label'=>'Pin Top Color',
            'type'=>'color',
            'name'=>'distanceTopColor',
            'value'=>'#FFFFFF'
        ];
        $settings[] = [
            'label'=>'Pin Bottom Color',
            'type'=>'color',
            'name'=>'distanceBottomColor',
            'value'=>'#FFFFFF'
        ];


        return $settings;
    }

    public static function getTemplate66Settings()
    {
        $settings[] = [
            'label'=>'Loop Speed',
            'type'=>'loop',
            'name'=>'loop_speed',
            'value'=>24,
            'multiply'=>1,
            'min'=>12,
            'max'=>36,
        ];

        $settings[] = [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>true,
            'value'=>"%%DISTANCE%%"
        ];

        $settings[] = [
            'type'=>'font',
            'name'=>'fonts_all',
            'label'=>'Google Fonts',
            'value'=>"Open Sans" //Please use google fonts
        ];

        $settings[] = [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
        ];

        $settings[] = [
            'label'=>'Font Colour',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#FFFFFF'
        ];

        $settings[] = [
            'label'=>'Font Size',
            'type'=>'font_size',
            'name'=>'font_size',
            'value'=>'13'
        ];


        $settings[] = [
            'label'=>'Box Background Color',
            'type'=>'color',
            'name'=>'distancebackgroundColor',
            'value'=>'#000'
        ];
        $settings[] = [
            'label'=>'Signs Color',
            'type'=>'color',
            'name'=>'signs',
            'value'=>'#FFF'
        ];

        $settings[] = [
            'label'=>'Text Color',
            'type'=>'color',
            'name'=>'textColor',
            'value'=>'#000'
        ];

        $settings[] = [
            'label'=>'Pin Top Color',
            'type'=>'color',
            'name'=>'distanceTopColor',
            'value'=>'#FFF'
        ];
        $settings[] = [
            'label'=>'Pin Bottom Color',
            'type'=>'color',
            'name'=>'distanceBottomColor',
            'value'=>'#FFFFFF'
        ];


        return $settings;
    }

    public static function getTemplate67Settings()
    {
        $settings[] = [
            'label'=>'Loop Speed',
            'type'=>'loop',
            'name'=>'loop_speed',
            'value'=>24,
            'multiply'=>1,
            'min'=>12,
            'max'=>36,
        ];

        $settings[] = [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>true,
            'value'=>"%%DISTANCE%%"
        ];

        $settings[] = [
            'type'=>'font',
            'name'=>'fonts_all',
            'label'=>'Google Fonts',
            'value'=>"Open Sans" //Please use google fonts
        ];

        $settings[] = [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
        ];

        $settings[] = [
            'label'=>'Font Colour',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#FFFFFF'
        ];

        $settings[] = [
            'label'=>'Font Size',
            'type'=>'font_size',
            'name'=>'font_size',
            'value'=>'20',
            'multiple'=>'50'
        ];

        $settings[] = [
            'label'=>'Box Background Color',
            'type'=>'color',
            'name'=>'distancebackgroundColor',
            'value'=>'#000'
        ];
        $settings[] = [
            'label'=>'Signs Color',
            'type'=>'color',
            'name'=>'signs',
            'value'=>'#FFF'
        ];

        $settings[] = [
            'label'=>'Text Color',
            'type'=>'color',
            'name'=>'textColor',
            'value'=>'#231F20'
        ];

        $settings[] = [
            'label'=>'Pin Top Color',
            'type'=>'color',
            'name'=>'distanceTopColor',
            'value'=>'#FFF'
        ];

        $settings[] = [
            'label'=>'Pin Bottom Color',
            'type'=>'color',
            'name'=>'distanceBottomColor',
            'value'=>'#FFFFFF'
        ];


        return $settings;
    }

    public static function getTemplate69Settings()
    {
        $settings[] = [
            'type'=>'upload',
            'name'=>'pin_img',
            'label'=>'Upload New Distance Window Pin',
            'allow_empty'=>false,
            'value'=>"https://static.vic-m.co/templates/pin/template11/default.png"
        ];

        $settings[] = [
            'label'=>'Flip Direction',
            'type'=>'select',
            'name'=>'direction_type',
            'value'=>"x",
            'options'=>[
                "y"=>"Horizontal",
                "x"=>"Vertical",
            ]
        ];

        $settings[] = [
                'label'=>'Background Color',
                'type'=>'color',
                'name'=>'distancebackgroundColor',
                'value'=>'#ffffff'
        ];

        $settings[] = [
                'label'=>'Pin Top Color',
                'type'=>'color',
                'name'=>'distanceTopColor',
                'value'=>'#759AC6'
        ];

        $settings[] = [
            'label'=>'Distance Inner-Circle Color',
            'type'=>'color',
            'name'=>'distanceInnerCircleColor',
            'value'=>'#075D86'
        ];

        $settings[] = [
                'label'=>'Pin Bottom Color',
                'type'=>'color',
                'name'=>'distanceBottomColor',
                'value'=>'#075D86'
        ];

        $settings[] = [
            'label'=>'Animated Distance Window',
            'type'=>'select',
            'name'=>'animated_window',
            'value'=>"none",
            'options'=>[
                "none"=>"None",
                "falling"=>"Falling Down",
                "upDown"=>"Falling Up and Down",
                "all"=>"All Directions",
                "roulette"=>"Roulette"
            ]
        ];

        $settings[] = [
            'label'=>'Pin Display Time',
            'type'=>'loop',
            'name'=>'loop_speed_pins',
            'value'=>2,
            'multiply'=>1,
            'min'=>1,
            'max'=>5,
        ];

        $settings[] = [
            'label'=>'Loop Speed',
            'type'=>'loop',
            'name'=>'loop_speed',
            'value'=>2,
            'multiply'=>1,
            'min'=>1,
            'max'=>5,
        ];


        $settings[] = [
            'label'=>'Reverse Direction',
            'type'=>'select',
            'name'=>'opposite_direction',
            'value'=>true,
            'options'=>[
                true=>"None",
                false=>"Reverse"
            ]
        ];

        $settings[] = [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>true,
            'value'=>"%%DISTANCE%%"
        ];

        $settings[] = [
            'type'=>'font',
            'name'=>'fonts_all',
            'label'=>'Google Fonts',
            'value'=>"Open Sans" //Please use google fonts
        ];

        $settings[] = [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
        ];

        $settings[] = [
            'label'=>'Font Colour',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#070275'
        ];


        $settings[] = [
            'label'=>'Font Size',
            'type'=>'font_size',
            'name'=>'font_size',
            'value'=>'13'
        ];

        $settings[] = [
        'label'=>'Font Bold',
        'type'=>'checkbox',
        'name'=>'font_bold',
        'type'=>'checkbox',
        'value'=>false
        ];


        $settings[] = [
        'label'=>'Font Underline',
        'type'=>'checkbox',
        'name'=>'font_underline',
        'type'=>'checkbox',
        'value'=>false
        ];

        $settings[] = [
        'label'=>'Font Italics',
        'type'=>'checkbox',
        'name'=>'font_italics',
        'type'=>'checkbox',
        'value'=>false
        ];



        return $settings;
    }




    public static function getTemplate55Settings()
    {
        $settings[] = [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>true,
            'value'=>"%%DISTANCE%% from %%STORENAME%%"
        ];

        $settings[] = [
            'type'=>'font',
            'name'=>'fonts_all',
            'label'=>'Google Fonts',
            'value'=>"Arial" //Please use google fonts
        ];

        $settings[] = [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
        ];

        $settings[] = [
            'label'=>'Font Colour',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#000033'
        ];

        $settings[] = [
            'label'=>'Font Size',
            'type'=>'font_size',
            'name'=>'font_size',
            'value'=>'12'
        ];

        $settings[] = [
            'label'=>'Loop Speed',
            'type'=>'loop',
            'name'=>'loop_speed',
            'value'=>64,
            'multiply'=>1,
            'min'=>10,
            'max'=>150,
        ];

        $settings[] = [
            'label'=>'Banner Display Time',
            'type'=>'loop',
            'name'=>'loop_speed2',
            'value'=>64,
            'multiply'=>2,
            'min'=>0,
            'max'=>10,
        ];




        $settings[] = [
                'label'=>'Pin Inner Color',
                'type'=>'color',
                'name'=>'distanceTopColor',
                'value'=>'#00D0E5'
        ];

        $settings[] = [
                'label'=>'Pin Outer Color',
                'type'=>'color',
                'name'=>'distanceBottomColor',
                'value'=>'#000033'
        ];

        $settings[] = [
            'label'=>'Glow Road Color',
            'type'=>'color',
            'name'=>'glowRoadColor',
            'value'=>'#00D0E5'
        ];


        $settings[] = [
            'label'=>'Glow Circle',
            'type'=>'color',
            'name'=>'glowCircle',
            'value'=>'#00D0E5'
        ];

        $settings[] = [
            'label'=>'Text Box Outer',
            'type'=>'color',
            'name'=>'shapeOuter',
            'value'=>'#000032'
        ];

        $settings[] = [
            'label'=>'Text Box Inner',
            'type'=>'color',
            'name'=>'shapeInner',
            'value'=>'#00D0E5'
        ];

        $settings[] = [
            'label'=>'Text Box Shadow',
            'type'=>'color',
            'name'=>'shapeShadow',
            'value'=>'#000033'
        ];

        $settings[] = [
            'label'=>'Text Width',
            'type'=>'font_size',
            'name'=>'textBoxSize',
            'value'=>'12'
        ];

        return $settings;
    }

    public static function getTemplate54Settings()
    {
        $settings[] = [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>true,
            'value'=>"%%DISTANCE%%"
        ];

        $settings[] = [
            'type'=>'font',
            'name'=>'fonts_all',
            'label'=>'Google Fonts',
            'value'=>"Open Sans" //Please use google fonts
        ];

        $settings[] = [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
        ];

        $settings[] = [
            'label'=>'Font Colour',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#FFFFFF'
        ];


        $settings[] = [
            'label'=>'Font Size',
            'type'=>'font_size',
            'name'=>'font_size',
            'value'=>'15'
        ];


        $settings[] = [
                'label'=>'Background Color',
                'type'=>'color',
                'name'=>'distancebackgroundColor',
                'value'=>'#0A56A4'
        ];

        $settings[] = [
                'label'=>'Pin Top Color',
                'type'=>'color',
                'name'=>'distanceTopColor',
                'value'=>'#FFFFFF'
        ];

        $settings[] = [
                'label'=>'Pin Bottom Color',
                'type'=>'color',
                'name'=>'distanceBottomColor',
                'value'=>'#BDD62B'
        ];

        $settings[] = [
            'label'=>'Trolley Color',
            'type'=>'color',
            'name'=>'trolleyColor',
            'value'=>'#ffffff'
        ];
        $settings[] = [
            'label'=>'Items Color',
            'type'=>'color',
            'name'=>'itemColor',
            'value'=>'#ffffff'
        ];
        $settings[] = [
            'label'=>'Zoom Color',
            'type'=>'color',
            'name'=>'zoomColor',
            'value'=>'#BDD62B'
        ];




        return $settings;
    }

    public static function getTemplate57Settings()
    {
        $settings[] = [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>true,
            'value'=>"%%DISTANCE%%"
        ];

        $settings[] = [
            'type'=>'font',
            'name'=>'fonts_all',
            'label'=>'Google Fonts',
            'value'=>"Open Sans" //Please use google fonts
        ];

        $settings[] = [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
        ];

        $settings[] = [
            'label'=>'Font Colour',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#FFFFFF'
        ];

        $settings[] = [
            'label'=>'Font Size',
            'type'=>'font_size',
            'name'=>'font_size',
            'value'=>'8'
        ];
        $settings[] = [
            'label'=>'Loop Speed',
            'type'=>'loop',
            'name'=>'loop_speed',
            'value'=>24,
            'multiply'=>1,
            'min'=>12,
            'max'=>36,
        ];



        $settings[] = [
                'label'=>'Background Color',
                'type'=>'color',
                'name'=>'distancebackgroundColor',
                'value'=>'#0A56A4'
        ];
        $settings[] = [
                'label'=>'Pin Top Color',
                'type'=>'color',
                'name'=>'distanceTopColor',
                'value'=>'#FFFFFF'
        ];

        $settings[] = [
                'label'=>'Pin Bottom Color',
                'type'=>'color',
                'name'=>'distanceBottomColor',
                'value'=>'#BDD62B'
        ];

        $settings[] = [
            'label'=>'Trolley Color',
            'type'=>'color',
            'name'=>'trolleyColor',
            'value'=>'#ffffff'
        ];
        $settings[] = [
            'label'=>'Items Color',
            'type'=>'color',
            'name'=>'itemColor',
            'value'=>'#ffffff'
        ];
        $settings[] = [
            'label'=>'Zoom Color',
            'type'=>'color',
            'name'=>'zoomColor',
            'value'=>'#BDD62B'
        ];




        return $settings;
    }

    public static function getTemplate41Settings()
    {
        $settings=[];

        $settings[] = [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>true,
            'value'=>"%%DISTANCE%%"
        ];

        $settings[] = [
            'type'=>'text',
            'name'=>'from_location',
            'label'=>'Location Address',
            'allow_empty'=>true,
            'value'=>"From your location"
        ];

        $settings[] = [
            'type'=>'font',
            'name'=>'fonts_all',
            'label'=>'Google Fonts',
            'value'=>"Open Sans" //Please use google fonts
        ];

        $settings[] = [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
        ];

        $settings[] = [
            'label'=>'Font Colour',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#49EAFD'
        ];


        $settings[] = [
            'label'=>'Font Size',
            'type'=>'font_size',
            'name'=>'font_size',
            'value'=>'13'
        ];

        $settings[] = [
        'label'=>'Font Bold',
        'type'=>'checkbox',
        'name'=>'font_bold',
        'type'=>'checkbox',
        'value'=>false
        ];


        $settings[] = [
        'label'=>'Font Underline',
        'type'=>'checkbox',
        'name'=>'font_underline',
        'type'=>'checkbox',
        'value'=>false
        ];

        $settings[] = [
        'label'=>'Font Italics',
        'type'=>'checkbox',
        'name'=>'font_italics',
        'type'=>'checkbox',
        'value'=>false
        ];

        return $settings;
    }


    public static function getTemplate42Settings()
    {
        $settings=[
            [
            'label'=>'Loop Speed',
            'type'=>'loop',
            'name'=>'loop_speed',
            'value'=>'7000',
            'multiply'=>1000,
            'min'=>0,
            'max'=>20,
            ],
            [
            'label'=>'Use Customisable Distance Pin?',
            'type'=>'checkbox',
            'name'=>'customisable_pin_yn',
            'value'=>true
            ],
            [
            'type'=>'upload',
            'name'=>'pin_img',
            'label'=>'Upload New Distance Window Pin',
            'allow_empty'=>true,
            'value'=>"/templates/pin/template42/Svg.svg"
            ],
            [
            'label'=>'Pin Background Color',
            'type'=>'color',
            'name'=>'distancebackgroundColor',
            'value'=>'#FFFFFF'
            ],
            [
            'label'=>'Pin Top Color',
            'type'=>'color',
            'name'=>'distanceTopColor',
            'value'=>'#759AC6'
            ],
            [
            'label'=>'Pin Bottom Color',
            'type'=>'color',
            'name'=>'distanceBottomColor',
            'value'=>'#075D86'
            ],
            [
            'label'=>'Pin Inner-Circle Color',
            'type'=>'color',
            'name'=>'distanceInnerCircleColor',
            'value'=>'#075D86'
            ],
            [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>true,
            'value'=>"%%DISTANCE%%"
            ],
            [
            'type'=>'text',
            'name'=>'from_location',
            'label'=>'From your location',
            'allow_empty'=>true,
            'value'=>"From your location"
            ],
            [
            'type'=>'font',
            'name'=>'fonts_all',
            'label'=>'Google Fonts',
            'value'=>"Open Sans" //Please use google fonts
            ],
            [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
            ],
            [
            'label'=>'Font Colour',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#070275'
            ],
            [
            'label'=>'Font Size',
            'type'=>'font_size',
            'name'=>'font_size',
            'value'=>'13'
            ],
            [
            'label'=>'Font Bold',
            'type'=>'checkbox',
            'name'=>'font_bold',
            'type'=>'checkbox',
            'value'=>false
            ],
            [
            'label'=>'Font Underline',
            'type'=>'checkbox',
            'name'=>'font_underline',
            'type'=>'checkbox',
            'value'=>false
            ],
            [
            'label'=>'Font Italics',
            'type'=>'checkbox',
            'name'=>'font_italics',
            'type'=>'checkbox',
            'value'=>false
            ],
            [
            'label'=>'Opacity',
            'name'=>'Opacity',
            'type'=>'select',
            'value'=>0.1,
            'options'=>[
                '1'=>"100% Opacity",
                '0.8'=>"80% Opacity",
            ],
            ],


        ];

        return $settings;
    }
    public static function getTemplate43Settings()
    {
        $settings=[];

        $settings[] = [
            'label'=>'Plane Color',
            'type'=>'color',
            'name'=>'distanceTopColor',
            'value'=>'#ADEFB7'
        ];

        $settings[] = [
            'label'=>'Background Color',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'backgroundColor',
            'value'=>'#003C10'
        ];

        $settings[] = [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>true,
            'value'=>"%%DISTANCE%%"
        ];

        $settings[] = [
            'type'=>'text',
            'name'=>'from_location',
            'label'=>'Location Address',
            'allow_empty'=>true,
            'value'=>"From your location"
        ];

        $settings[] = [
            'type'=>'font',
            'name'=>'fonts_all',
            'label'=>'Google Fonts',
            'value'=>"Open Sans" //Please use google fonts
        ];

        $settings[] = [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
        ];

        $settings[] = [
            'label'=>'Font Colour',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#ffffff'
        ];


        $settings[] = [
            'label'=>'Font Size',
            'type'=>'font_size',
            'name'=>'font_size',
            'value'=>'13'
        ];


        $settings[] = [
        'label'=>'Font Bold',
        'type'=>'checkbox',
        'name'=>'font_bold',
        'type'=>'checkbox',
        'value'=>false
        ];


        $settings[] = [
        'label'=>'Font Underline',
        'type'=>'checkbox',
        'name'=>'font_underline',
        'type'=>'checkbox',
        'value'=>false
        ];

        $settings[] = [
        'label'=>'Font Italics',
        'type'=>'checkbox',
        'name'=>'font_italics',
        'type'=>'checkbox',
        'value'=>false
        ];

        return $settings;
    }


    public static function getTemplate44Settings()
    {
        $settings=[];

        $settings[] = [
            'label'=>'Plane Color',
            'type'=>'color',
            'name'=>'distanceTopColor',
            'value'=>'#ADEFB7'
        ];

        $settings[] = [
            'label'=>'Background Color',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'backgroundColor',
            'value'=>'#003C10'
        ];

        $settings[] = [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>true,
            'value'=>"%%DISTANCE%%"
        ];

        $settings[] = [
            'type'=>'text',
            'name'=>'from_location',
            'label'=>'Location Address',
            'allow_empty'=>true,
            'value'=>"From your location"
        ];


        $settings[] = [
            'type'=>'font',
            'name'=>'fonts_all',
            'label'=>'Google Fonts',
            'value'=>"Open Sans" //Please use google fonts
        ];

        $settings[] = [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
        ];

        $settings[] = [
            'label'=>'Font Colour',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#ffffff'
        ];


        $settings[] = [
            'label'=>'Font Size',
            'type'=>'font_size',
            'name'=>'font_size',
            'value'=>'13'
        ];

        $settings[] = [
        'label'=>'Font Bold',
        'type'=>'checkbox',
        'name'=>'font_bold',
        'type'=>'checkbox',
        'value'=>false
        ];


        $settings[] = [
        'label'=>'Font Underline',
        'type'=>'checkbox',
        'name'=>'font_underline',
        'type'=>'checkbox',
        'value'=>false
        ];

        $settings[] = [
        'label'=>'Font Italics',
        'type'=>'checkbox',
        'name'=>'font_italics',
        'type'=>'checkbox',
        'value'=>false
        ];

        return $settings;
    }
    public static function getTemplate38Settings()
    {
        $settings=[];
        $settings[] = [
            'label'=>'Loop Speed',
            'type'=>'loop',
            'name'=>'loop_speed',
            'value'=>0,
            'multiply'=>5,
            'min'=>-25,
            'max'=>25,
        ];

        $settings[] = [
            'label'=>'Use Customisable Distance Pin?',
            'type'=>'checkbox',
            'name'=>'customisable_pin_yn',
            'value'=>false
        ];
        $settings[] = [
            'type'=>'upload',
            'name'=>'pin_img',
            'label'=>'Upload New Distance Window Pin',
            'allow_empty'=>true,
            'value'=>"https://static.vic-m.co/templates/pin/template38/default.svg"
        ];
        $settings[] = [
            'label'=>'Pin Background Color',
            'type'=>'color',
            'name'=>'distancebackgroundColor',
            'value'=>'#FFFFFF'
        ];
        $settings[] = [
            'label'=>'Pin Top Color',
            'type'=>'color',
            'name'=>'distanceTopColor',
            'value'=>'#759AC6'
        ];
        $settings[] = [
            'label'=>'Pin Bottom Color',
            'type'=>'color',
            'name'=>'distanceBottomColor',
            'value'=>'#075D86'
        ];
        $settings[] = [
            'label'=>'Pin Inner-Circle Color',
            'type'=>'color',
            'name'=>'distanceInnerCircleColor',
            'value'=>'#075D86'
        ];

        $settings[] = [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>true,
            'value'=>"%%DISTANCE%%"
        ];

        $settings[] = [
            'type'=>'text',
            'name'=>'from_location',
            'label'=>'Location Address',
            'allow_empty'=>true,
            'value'=>"From your location"
        ];


        $settings[] = [
            'type'=>'font',
            'name'=>'fonts_all',
            'label'=>'Google Fonts',
            'value'=>"Open Sans" //Please use google fonts
        ];

        $settings[] = [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
        ];

        $settings[] = [
            'label'=>'Font Colour',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#070275'
        ];


        $settings[] = [
            'label'=>'Font Size',
            'type'=>'font_size',
            'name'=>'font_size',
            'value'=>'13'
        ];

        $settings[] = [
        'label'=>'Font Bold',
        'type'=>'checkbox',
        'name'=>'font_bold',
        'type'=>'checkbox',
        'value'=>false
        ];


        $settings[] = [
        'label'=>'Font Underline',
        'type'=>'checkbox',
        'name'=>'font_underline',
        'type'=>'checkbox',
        'value'=>false
        ];

        $settings[] = [
        'label'=>'Font Italics',
        'type'=>'checkbox',
        'name'=>'font_italics',
        'type'=>'checkbox',
        'value'=>false
        ];


        return $settings;
    }
    public static function getTemplate39Settings()
    {
        $settings=[];
        $settings = [
            [
            'label'=>'Loop Speed',
            'type'=>'loop',
            'name'=>'loop_speed',
            'value'=>10,
            'multiply'=>5,
            'min'=>1,
            'max'=>20,
            ],
            [
            'label'=>'Use Customisable Distance Pin?',
            'type'=>'checkbox',
            'name'=>'customisable_pin_yn',
            'value'=>true
            ],
            [
            'type'=>'upload',
            'name'=>'pin_img',
            'label'=>'Upload New Distance Window Pin',
            'allow_empty'=>true,
            'value'=>"https://static.vic-m.co/templates/pin/template39/default.svg"
            ],
            [
            'label'=>'Pin Background Color',
            'type'=>'color',
            'name'=>'distancebackgroundColor',
            'value'=>'#FFFFFF'
            ],
            [
            'label'=>'Pin Top Color',
            'type'=>'color',
            'name'=>'distanceTopColor',
            'value'=>'#759AC6'
            ],
            [
            'label'=>'Pin Bottom Color',
            'type'=>'color',
            'name'=>'distanceBottomColor',
            'value'=>'#075D86'
            ],
            [
            'label'=>'Pin Inner-Circle Color',
            'type'=>'color',
            'name'=>'distanceInnerCircleColor',
            'value'=>'#075D86'
            ],
            [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>true,
            'value'=>"%%DISTANCE%%"
            ],
            [
            'type'=>'text',
            'name'=>'from_location',
            'label'=>'Location Address',
            'allow_empty'=>true,
            'value'=>"From your location"
            ],
            [
            'type'=>'font',
            'name'=>'fonts_all',
            'label'=>'Google Fonts',
            'value'=>"Open Sans" //Please use google fonts
            ],
            [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
            ],
            [
            'label'=>'Font Colour',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#070275'
            ],
            [
            'label'=>'Font Size',
            'type'=>'font_size',
            'name'=>'font_size',
            'value'=>'13'
            ],
            [
            'label'=>'Font Bold',
            'type'=>'checkbox',
            'name'=>'font_bold',
            'type'=>'checkbox',
            'value'=>false
            ],
            [
            'label'=>'Font Underline',
            'type'=>'checkbox',
            'name'=>'font_underline',
            'type'=>'checkbox',
            'value'=>false
            ],
            [
            'label'=>'Font Italics',
            'type'=>'checkbox',
            'name'=>'font_italics',
            'type'=>'checkbox',
            'value'=>false
            ],
        ];

        return $settings;
    }
    public static function getTemplate37Settings()
    {
        $settings=[];
        $settings[] = [
            'label'=>'Loop Speed',
            'type'=>'loop',
            'name'=>'loop_speed',
            'value'=>'0',
            'multiply'=>5,
            'min'=>-25,
            'max'=>25,
        ];
        $settings[] = [
            'label'=>'Use Customisable Distance Pin?',
            'type'=>'checkbox',
            'name'=>'customisable_pin_yn',
            'value'=>false
        ];
        $settings[] = [
            'type'=>'upload',
            'name'=>'pin_img',
            'label'=>'Upload New Distance Window Pin',
            'allow_empty'=>true,
            'value'=>"https://static.vic-m.co/templates/pin/template39/default.svg"
        ];
        $settings[] = [
            'label'=>'Pin Background Color',
            'type'=>'color',
            'name'=>'distancebackgroundColor',
            'value'=>'#FFFFFF'
        ];
        $settings[] = [
            'label'=>'Pin Top Color',
            'type'=>'color',
            'name'=>'distanceTopColor',
            'value'=>'#759AC6'
        ];
        $settings[] = [
            'label'=>'Pin Bottom Color',
            'type'=>'color',
            'name'=>'distanceBottomColor',
            'value'=>'#075D86'
        ];
        $settings[] = [
            'label'=>'Pin Inner-Circle Color',
            'type'=>'color',
            'name'=>'distanceInnerCircleColor',
            'value'=>'#075D86'
        ];

        $settings[] = [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>true,
            'value'=>"%%DISTANCE%%"
        ];

        $settings[] = [
            'type'=>'text',
            'name'=>'from_location',
            'label'=>'Location Address',
            'allow_empty'=>true,
            'value'=>"From your location"
        ];

        $settings[] = [
            'type'=>'font',
            'name'=>'fonts_all',
            'label'=>'Google Fonts',
            'value'=>"Open Sans" //Please use google fonts
        ];

        $settings[] = [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
        ];

        $settings[] = [
            'label'=>'Font Colour',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#070275'
        ];


        $settings[] = [
            'label'=>'Font Size',
            'type'=>'font_size',
            'name'=>'font_size',
            'value'=>'13'
        ];


        $settings[] = [
        'label'=>'Font Bold',
        'type'=>'checkbox',
        'name'=>'font_bold',
        'type'=>'checkbox',
        'value'=>false
        ];


        $settings[] = [
        'label'=>'Font Underline',
        'type'=>'checkbox',
        'name'=>'font_underline',
        'type'=>'checkbox',
        'value'=>false
        ];

        $settings[] = [
        'label'=>'Font Italics',
        'type'=>'checkbox',
        'name'=>'font_italics',
        'type'=>'checkbox',
        'value'=>false
        ];

        return $settings;
    }

    public static function getTemplate5Settings()
    {
        $settings = [
        [
        'label'=>'Distance Pin Display Time(s)',
        'type'=>'loop',
        'name'=>'loop_speed',
        'value'=>8,
        'multiply'=>1,
        'min'=>1,
        'max'=>20,
        ],
        [
        'label'=>'Use Customisable Distance Pin?',
        'type'=>'checkbox',
        'name'=>'customisable_pin_yn',
        'value'=>true,
        ],
        [
        'type'=>'upload',
        'name'=>'pin_img',
        'label'=>'Upload New Distance Window Pin',
        'allow_empty'=>true,
        'value'=>"https://static.vic-m.co/templates/pin/template11/default.png",
        ],
        [
        'label'=>'Pin Background Color',
        'type'=>'color',
        'name'=>'distancebackgroundColor',
        'value'=>'#FFFFFF'
        ],
        [
        'label'=>'Pin Top Color',
        'type'=>'color',
        'name'=>'distanceTopColor',
        'value'=>'#759AC6'
        ],
        [
        'label'=>'Pin Bottom Color',
        'type'=>'color',
        'name'=>'distanceBottomColor',
        'value'=>'#075D86'
        ],
        [
        'label'=>'Pin Inner-Circle Color',
        'type'=>'color',
        'name'=>'distanceInnerCircleColor',
        'value'=>'#075D86'
        ],

        [
        'type'=>'text',
        'name'=>'distance_value_text',
        'label'=>'Display Message',
        'allow_empty'=>false,
        'value'=>"%%DISTANCE%%",
        ],
        [
        'type'=>'font',
        'name'=>'fonts_all',
        'label'=>'Google Fonts',
        'value'=>"Open Sans" //Please use google fonts
        ],
        [
        'type'=>'custom_font',
        'name'=>'custom_font',
        'label'=>'Custom Fonts',
        'value'=>''
        ],
        [
        'label'=>'Font Colour',
        'type'=>'color', //uses text box and comes with color picker
        'name'=>'font_color',
        'value'=>'#175582'
        ],
        [
        'label'=>'Font Bold',
        'type'=>'checkbox',
        'name'=>'font_bold',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'type'=>'text',
        'name'=>'from_location',
        'label'=>'Location Address',
        'allow_empty'=>true,
        'value'=>"From your location"
        ],
        [
        'label'=>'Font Underline',
        'type'=>'checkbox',
        'name'=>'font_underline',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'label'=>'Font Italics',
        'type'=>'checkbox',
        'name'=>'font_italics',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'label'=>'Font Size',
        'type'=>'font_size',
        'name'=>'font_size',
        'value'=>'13'
        ]
        ];

        return $settings;
    }

    public static function getTemplate68Settings()
    {
        $settings=[];
        $settings = [
            [
            'label'=>'Loop Speed',
            'type'=>'loop',
            'name'=>'loop_speed',
            'value'=>50,
            'multiply'=>10,
            'min'=>1,
            'max'=>20,
            ],
            [
            'label'=>'Use Customisable Distance Pin?',
            'type'=>'checkbox',
            'name'=>'customisable_pin_yn',
            'value'=>true
            ],
            [
            'type'=>'upload',
            'name'=>'pin_img',
            'label'=>'Upload New Distance Window Pin',
            'allow_empty'=>true,
            'value'=>"https://static.vic-m.co/templates/pin/template39/default.svg"
            ],
            [
            'label'=>'Pin Background Color',
            'type'=>'color',
            'name'=>'distancebackgroundColor',
            'value'=>'#FFFFFF'
            ],
            [
            'label'=>'Pin Top Color',
            'type'=>'color',
            'name'=>'distanceTopColor',
            'value'=>'#759AC6'
            ],
            [
            'label'=>'Pin Bottom Color',
            'type'=>'color',
            'name'=>'distanceBottomColor',
            'value'=>'#075D86'
            ],
            [
            'label'=>'Pin Inner-Circle Color',
            'type'=>'color',
            'name'=>'distanceInnerCircleColor',
            'value'=>'#075D86'
            ],
            [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>true,
            'value'=>"%%DISTANCE%%"
            ],
            [
            'type'=>'font',
            'name'=>'fonts_all',
            'label'=>'Google Fonts',
            'value'=>"Open Sans" //Please use google fonts
            ],
            [
                'type'=>'custom_font',
                'name'=>'custom_font',
                'label'=>'Custom Fonts',
                'value'=>''
            ],
            [
            'label'=>'Font Colour',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#070275'
            ],
            [
            'label'=>'Font Size',
            'type'=>'font_size',
            'name'=>'font_size',
            'value'=>'13'
            ],
            [
            'label'=>'Font Bold',
            'type'=>'checkbox',
            'name'=>'font_bold',
            'type'=>'checkbox',
            'value'=>false
            ],
            [
            'label'=>'Font Underline',
            'type'=>'checkbox',
            'name'=>'font_underline',
            'type'=>'checkbox',
            'value'=>false
            ],
            [
            'label'=>'Font Italics',
            'type'=>'checkbox',
            'name'=>'font_italics',
            'type'=>'checkbox',
            'value'=>false
            ],
        ];
        return $settings;
    }

    public static function getTemplate75Settings()
    {

        $settings = [
        [
        'label'=>'Loop Speed',
        'type'=>'loop',
        'name'=>'loop_speed',
        'value'=>1500,
        'multiply'=>500,
        'min'=>3,
        'max'=>20,
        ],
        [
        'label'=>'Use Customisable Distance Pin?',
        'type'=>'checkbox',
        'name'=>'customisable_pin_yn',
        'value'=>true,
        ],

        [
        'label'=>'Pin Background Color',
        'type'=>'color',
        'name'=>'distancebackgroundColor',
        'value'=>'#FFFFFF'
        ],
        [
        'label'=>'Pin Top Color',
        'type'=>'color',
        'name'=>'distanceTopColor',
        'value'=>'#759AC6'
        ],
        [
        'label'=>'Pin Bottom Color',
        'type'=>'color',
        'name'=>'distanceBottomColor',
        'value'=>'#075D86'
        ],
        [
        'label'=>'Pin Inner-Circle Color',
        'type'=>'color',
        'name'=>'distanceInnerCircleColor',
        'value'=>'#075D86'
        ],
        [
        'type'=>'text',
        'name'=>'distance_value_text',
        'label'=>'Display Message',
        'allow_empty'=>false,
        'value'=>"%%DISTANCE%%",
        ],
        [
        'type'=>'font',
        'name'=>'fonts_all',
        'label'=>'Google Fonts',
        'value'=>"Open Sans" //Please use google fonts
        ],
        [
        'type'=>'custom_font',
        'name'=>'custom_font',
        'label'=>'Custom Fonts',
        'value'=>''
        ],
        [
        'label'=>'Font Colour',
        'type'=>'color', //uses text box and comes with color picker
        'name'=>'font_color',
        'value'=>'#175582'
        ],

        [
        'label'=>'Font Bold',
        'type'=>'checkbox',
        'name'=>'font_bold',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'label'=>'Font Underline',
        'type'=>'checkbox',
        'name'=>'font_underline',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'label'=>'Font Italics',
        'type'=>'checkbox',
        'name'=>'font_italics',
        'type'=>'checkbox',
        'value'=>false
        ],
        ];

        $settings[] =           [
        'label'=>'Font Size',
        'type'=>'font_size',
        'name'=>'font_size',
        'value'=>'13'
        ];

		$settings[] = [
				'type'=>'upload',
				'name'=>'pin_img',
				'label'=>'Upload New Distance Window Pin',
				'allow_empty'=>true,
				'value'=>"https://static.vic-m.co/templates/pin/template11/default.png",
		];

        return $settings;
    }

    public static function getTemplate82Settings()
    {
        $settings = [];
        $settings = [
            [
            'label'=>'Banner Display Time(s)',
            'type'=>'loop',
            'name'=>'loop_speed',
            'value'=>4000,
            'multiply'=>1000,
            'min'=>3,
            'max'=>20,
            ],

            [
            'label'=>'Use Customisable Distance Pin?',
            'type'=>'checkbox',
            'name'=>'customisable_pin_yn',
            'value'=>true,
            ],
            [
                'type'=>'upload',
                'name'=>'pin_img',
                'label'=>'Upload New Distance Window Pin',
                'allow_empty'=>true,
                'value'=>"https://static.vic-m.co/templates/pin/template11/default.png",
            ],

            [
            'label'=>'Pin Background Color',
            'type'=>'color',
            'name'=>'distancebackgroundColor',
            'value'=>'#FFFFFF'
            ],
            [
            'label'=>'Pin Top Color',
            'type'=>'color',
            'name'=>'distanceTopColor',
            'value'=>'#759AC6'
            ],
            [
            'label'=>'Pin Bottom Color',
            'type'=>'color',
            'name'=>'distanceBottomColor',
            'value'=>'#075D86'
            ],
            [
            'label'=>'Pin Inner-Circle Color',
            'type'=>'color',
            'name'=>'distanceInnerCircleColor',
            'value'=>'#075D86'
            ],
            [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>false,
            'value'=>"%%DISTANCE%%",
            ],
            [
                'type'=>'font',
                'name'=>'fonts_all',
                'label'=>'Google Fonts',
                'value'=>"Open Sans" //Please use google fonts
            ],
            [
                'type'=>'custom_font',
                'name'=>'custom_font',
                'label'=>'Custom Fonts',
                'value'=>''
            ],
            [
                'label'=>'Font Colour',
                'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#175582'
        ],

        [
            'label'=>'Font Bold',
            'type'=>'checkbox',
                'name'=>'font_bold',
                'value'=>false
            ],
            [
                'label'=>'Font Italics',
                'type'=>'checkbox',
                'name'=>'font_italics',
                'value'=>false
            ],
            [
                'label'=>'Font Size',
                'type'=>'font_size',
                'name'=>'font_size',
                'value'=>'20'
            ],
        ];

        return $settings;
    }





    public static function getTemplate72Settings()
    {
        $settings = [
        [
        'label'=>'Loop Speed',
        'type'=>'loop',
        'name'=>'loop_speed',
        'value'=>3000,
        'multiply'=>1000,
        'min'=>3,
        'max'=>20,
        ],
        [
        'label'=>'Distance Pin Display Time(s)',
        'type'=>'loop',
        'name'=>'loop_speed2',
        'value'=>3000,
        'multiply'=>1000,
        'min'=>3,
        'max'=>20,
        ],
        [
        'label'=>'Use Customisable Distance Pin?',
        'type'=>'checkbox',
        'name'=>'customisable_pin_yn',
        'value'=>true,
        ],
        [
        'label'=>'Pin Background Color',
        'type'=>'color',
        'name'=>'distancebackgroundColor',
        'value'=>'#FFFFFF'
        ],
        [
        'label'=>'Pin Top Color',
        'type'=>'color',
        'name'=>'distanceTopColor',
        'value'=>'#759AC6'
        ],
        [
        'label'=>'Pin Bottom Color',
        'type'=>'color',
        'name'=>'distanceBottomColor',
        'value'=>'#075D86'
        ],
        [
        'label'=>'Pin Inner-Circle Color',
        'type'=>'color',
        'name'=>'distanceInnerCircleColor',
        'value'=>'#075D86'
        ],
        [
        'type'=>'text',
        'name'=>'distance_value_text',
        'label'=>'Display Message',
        'allow_empty'=>false,
        'value'=>"%%DISTANCE%%",
        ],
        [
        'type'=>'font',
        'name'=>'fonts_all',
        'label'=>'Google Fonts',
        'value'=>"Open Sans" //Please use google fonts
        ],
        [
        'type'=>'custom_font',
        'name'=>'custom_font',
        'label'=>'Custom Fonts',
        'value'=>''
        ],
        [
        'label'=>'Font Colour',
        'type'=>'color', //uses text box and comes with color picker
        'name'=>'font_color',
        'value'=>'#175582'
        ],

        [
        'label'=>'Font Bold',
        'type'=>'checkbox',
        'name'=>'font_bold',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'label'=>'Font Underline',
        'type'=>'checkbox',
        'name'=>'font_underline',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'label'=>'Font Italics',
        'type'=>'checkbox',
        'name'=>'font_italics',
        'type'=>'checkbox',
        'value'=>false
        ],
        ];

        $settings[] =           [
        'label'=>'Font Size',
        'type'=>'font_size',
        'name'=>'font_size',
        'value'=>'13'
        ];

        $settings[] = [
                'type'=>'upload',
                'name'=>'pin_img',
                'label'=>'Upload New Distance Window Pin',
                'allow_empty'=>true,
                'value'=>"https://static.vic-m.co/templates/pin/template11/default.png",
        ];


        $settings = array_merge($settings, self::animationFonts());

        return $settings;
    }

    public static function getTemplate137Settings()
    {
        $settings = [
        [
        'label'=>'Number of Folds',
        'type'=>'number',
        'name'=>'numberOfFolds',
        'value'=>'5',
        ],
        [
        'label'=>'Interactive',
        'type'=>'checkbox',
        'name'=>'interactive',
        'value'=>false,
        ],
        [
        'label' => 'Fold Direction',
        'type' => 'select',
        'name' => 'foldDirection',
        'value' => 'top',
        'options' => [
            'top' => "top",
            'bottom' => "bottom",
            'left' => "left",
            'right' => "right"

        ]
        ],
        [
        'label' => 'Fold Effect (for interactive)',
        'type' => 'select',
        'name' => 'foldEffect',
        'value' => 'accordion',
        'options' => [
            'accordion' => "accordion",
            'curl' => "curl",
            'reveal' => "reveal",
            'stairs' => "stairs",
            'twist' => "twist",
            'ramp' => 'ramp',
            'fracture' => 'fracture'

        ]
        ],
        [
        'label'=>'Loop Speed',
        'type'=>'loop',
        'name'=>'loop_speed',
        'value'=>5,
        'min'=>1,
        'max'=>20,
        ],
        [
        'label'=>'Distance Pin Display Time(s)',
        'type'=>'loop',
        'name'=>'loop_speed2',
        'value'=>5,
        'multiply'=>1000,
        'min'=>1,
        'max'=>20,
        ],
        [
        'label'=>'Use Customisable Distance Pin?',
        'type'=>'checkbox',
        'name'=>'customisable_pin_yn',
        'value'=>true,
        ],
        [
        'label'=>'Pin Background Color',
        'type'=>'color',
        'name'=>'distancebackgroundColor',
        'value'=>'#FFFFFF'
        ],
        [
        'label'=>'Pin Top Color',
        'type'=>'color',
        'name'=>'distanceTopColor',
        'value'=>'#759AC6'
        ],
        [
        'label'=>'Pin Bottom Color',
        'type'=>'color',
        'name'=>'distanceBottomColor',
        'value'=>'#075D86'
        ],
        [
        'label'=>'Pin Inner-Circle Color',
        'type'=>'color',
        'name'=>'distanceInnerCircleColor',
        'value'=>'#075D86'
        ],
        [
        'type'=>'text',
        'name'=>'distance_value_text',
        'label'=>'Display Message',
        'allow_empty'=>false,
        'value'=>"%%DISTANCE%%",
        ],
        [
        'type'=>'font',
        'name'=>'fonts_all',
        'label'=>'Google Fonts',
        'value'=>"Open Sans" //Please use google fonts
        ],
        [
        'type'=>'custom_font',
        'name'=>'custom_font',
        'label'=>'Custom Fonts',
        'value'=>''
        ],
        [
        'label'=>'Font Color',
        'type'=>'color', //uses text box and comes with color picker
        'name'=>'font_color',
        'value'=>'#175582'
        ],

        [
        'label'=>'Font Bold',
        'type'=>'checkbox',
        'name'=>'font_bold',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'label'=>'Font Underline',
        'type'=>'checkbox',
        'name'=>'font_underline',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'label'=>'Font Italics',
        'type'=>'checkbox',
        'name'=>'font_italics',
        'type'=>'checkbox',
        'value'=>false
        ],
        ];

        $settings[] =           [
        'label'=>'Font Size',
        'type'=>'font_size',
        'name'=>'font_size',
        'value'=>'13'
        ];

        $settings[] = [
                'type'=>'upload',
                'name'=>'pin_img',
                'label'=>'Upload New Distance Window Pin',
                'allow_empty'=>true,
                'value'=>"https://static.vic-m.co/templates/pin/template11/default.png",
        ];


        $settings = array_merge($settings, self::animationFonts());

        return $settings;
    }

    public static function getTemplate146Settings()
    {
        $settings[] = [
            'label'=>' Swipe Positioning',
            'type'=>'select',
            'name'=>'swipeAnimationPositioning',
            'options' => [
                'top' => "top",
                'bottom' => "bottom",
            ],
            'value'=>"top"
        ];

        $settings[] = [
            'type'=>'upload',
            'name'=>'pin_img',
            'label'=>'Upload New Distance Window Pin',
            'allow_empty'=>false,
            'value'=>"https://static.vic-m.co/templates/pin/template11/default.png"
        ];

        $settings[] = [
                'label'=>'Background Color',
                'type'=>'color',
                'name'=>'distancebackgroundColor',
                'value'=>'#ffffff'
        ];

        $settings[] = [
                'label'=>'Pin Top Color',
                'type'=>'color',
                'name'=>'distanceTopColor',
                'value'=>'#759AC6'
        ];

        $settings[] = [
            'label'=>'Distance Inner-Circle Color',
            'type'=>'color',
            'name'=>'distanceInnerCircleColor',
            'value'=>'#075D86'
        ];

        $settings[] = [
                'label'=>'Pin Bottom Color',
                'type'=>'color',
                'name'=>'distanceBottomColor',
                'value'=>'#075D86'
        ];

        $settings[] = [
            'label'=>'Animated Distance Window',
            'type'=>'select',
            'name'=>'animated_window',
            'value'=>"none",
            'options'=>[
                "none"=>"None",
                "falling"=>"Falling Down",
                "upDown"=>"Falling Up and Down",
                "all"=>"All Directions",
                "roulette"=>"Roulette"
            ]
        ];

        $settings[] = [
            'label'=>'Pin Display Time',
            'type'=>'loop',
            'name'=>'loop_speed_pins',
            'value'=>2,
            'multiply'=>1,
            'min'=>1,
            'max'=>5,
        ];

        $settings[] = [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>true,
            'value'=>"%%DISTANCE%%"
        ];

        $settings[] = [
            'type'=>'font',
            'name'=>'fonts_all',
            'label'=>'Google Fonts',
            'value'=>"Open Sans" //Please use google fonts
        ];

        $settings[] = [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
        ];

        $settings[] = [
            'label'=>'Font Colour',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#070275'
        ];


        $settings[] = [
            'label'=>'Font Size',
            'type'=>'font_size',
            'name'=>'font_size',
            'value'=>'13'
        ];

        $settings[] = [
        'label'=>'Font Bold',
        'type'=>'checkbox',
        'name'=>'font_bold',
        'type'=>'checkbox',
        'value'=>false
        ];


        $settings[] = [
        'label'=>'Font Underline',
        'type'=>'checkbox',
        'name'=>'font_underline',
        'type'=>'checkbox',
        'value'=>false
        ];

        $settings[] = [
        'label'=>'Font Italics',
        'type'=>'checkbox',
        'name'=>'font_italics',
        'type'=>'checkbox',
        'value'=>false
        ];

        return $settings;
    }

    public static function getTemplate147Settings()
    {
        $settings[] = [
            'label'=>' Swipe Positioning',
            'type'=>'select',
            'name'=>'swipeAnimationPositioning',
            'options' => [
                'top' => "top",
                'bottom' => "bottom",
            ],
            'value'=>"top"
        ];

        $settings[] = [
            'type'=>'upload',
            'name'=>'pin_img',
            'label'=>'Upload New Distance Window Pin',
            'allow_empty'=>false,
            'value'=>"https://static.vic-m.co/templates/pin/template11/default.png"
        ];

        $settings[] = [
                'label'=>'Background Color',
                'type'=>'color',
                'name'=>'distancebackgroundColor',
                'value'=>'#ffffff'
        ];

        $settings[] = [
                'label'=>'Pin Top Color',
                'type'=>'color',
                'name'=>'distanceTopColor',
                'value'=>'#759AC6'
        ];

        $settings[] = [
            'label'=>'Distance Inner-Circle Color',
            'type'=>'color',
            'name'=>'distanceInnerCircleColor',
            'value'=>'#075D86'
        ];

        $settings[] = [
                'label'=>'Pin Bottom Color',
                'type'=>'color',
                'name'=>'distanceBottomColor',
                'value'=>'#075D86'
        ];

        $settings[] = [
            'label'=>'Animated Distance Window',
            'type'=>'select',
            'name'=>'animated_window',
            'value'=>"none",
            'options'=>[
                "none"=>"None",
                "falling"=>"Falling Down",
                "upDown"=>"Falling Up and Down",
                "all"=>"All Directions",
                "roulette"=>"Roulette"
            ]
        ];

        $settings[] = [
            'label'=>'Pin Display Time',
            'type'=>'loop',
            'name'=>'loop_speed_pins',
            'value'=>2,
            'multiply'=>1,
            'min'=>1,
            'max'=>5,
        ];

        $settings[] = [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>true,
            'value'=>"%%DISTANCE%%"
        ];

        $settings[] = [
            'type'=>'font',
            'name'=>'fonts_all',
            'label'=>'Google Fonts',
            'value'=>"Open Sans" //Please use google fonts
        ];

        $settings[] = [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
        ];

        $settings[] = [
            'label'=>'Font Colour',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#070275'
        ];


        $settings[] = [
            'label'=>'Font Size',
            'type'=>'font_size',
            'name'=>'font_size',
            'value'=>'13'
        ];

        $settings[] = [
        'label'=>'Font Bold',
        'type'=>'checkbox',
        'name'=>'font_bold',
        'type'=>'checkbox',
        'value'=>false
        ];


        $settings[] = [
        'label'=>'Font Underline',
        'type'=>'checkbox',
        'name'=>'font_underline',
        'type'=>'checkbox',
        'value'=>false
        ];

        $settings[] = [
        'label'=>'Font Italics',
        'type'=>'checkbox',
        'name'=>'font_italics',
        'type'=>'checkbox',
        'value'=>false
        ];

        return $settings;
    }

    public static function getTemplate151Settings()
    {
        $settings[] = [
            'label'=>'Transition Delay',
            'type'=>'loop',
            'name'=>'transition_delay',
            'value'=>"4000"
        ];

        $settings[] = [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>true,
            'value'=>"%%DISTANCE%%"
        ];

        $settings[] = [
            'type'=>'font',
            'name'=>'fonts_all',
            'label'=>'Google Fonts',
            'value'=>"Open Sans" //Please use google fonts
        ];

        $settings[] = [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
        ];

        $settings[] = [
            'label'=>'Font Colour',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#070275'
        ];


        $settings[] = [
            'label'=>'Font Size',
            'type'=>'font_size',
            'name'=>'font_size',
            'value'=>'13'
        ];

        $settings[] = [
        'label'=>'Font Bold',
        'type'=>'checkbox',
        'name'=>'font_bold',
        'type'=>'checkbox',
        'value'=>false
        ];


        $settings[] = [
        'label'=>'Font Underline',
        'type'=>'checkbox',
        'name'=>'font_underline',
        'type'=>'checkbox',
        'value'=>false
        ];

        $settings[] = [
        'label'=>'Font Italics',
        'type'=>'checkbox',
        'name'=>'font_italics',
        'type'=>'checkbox',
        'value'=>false
        ];

        return $settings;
    }

    public static function getTemplate150Settings()
    {

        $settings[] = [
            'label'=>' Swipe Positioning',
            'type'=>'select',
            'name'=>'swipeAnimationPositioning',
            'options' => [
                'none' => "none",
                'top' => "top",
                'bottom' => "bottom",
            ],
            'value'=>"none"
        ];

        $settings[] = [
            'type'=>'upload',
            'name'=>'pin_img',
            'label'=>'Upload New Distance Window Pin',
            'allow_empty'=>false,
            'value'=>"https://static.vic-m.co/templates/pin/template11/default.png"
        ];

        $settings[] = [
                'label'=>'Background Color',
                'type'=>'color',
                'name'=>'distancebackgroundColor',
                'value'=>'#ffffff'
        ];

        $settings[] = [
                'label'=>'Pin Top Color',
                'type'=>'color',
                'name'=>'distanceTopColor',
                'value'=>'#759AC6'
        ];

        $settings[] = [
            'label'=>'Distance Inner-Circle Color',
            'type'=>'color',
            'name'=>'distanceInnerCircleColor',
            'value'=>'#075D86'
        ];

        $settings[] = [
                'label'=>'Pin Bottom Color',
                'type'=>'color',
                'name'=>'distanceBottomColor',
                'value'=>'#075D86'
        ];

        $settings[] = [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>true,
            'value'=>"%%DISTANCE%%"
        ];

        $settings[] = [
            'type'=>'font',
            'name'=>'fonts_all',
            'label'=>'Google Fonts',
            'value'=>"Open Sans" //Please use google fonts
        ];

        $settings[] = [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
        ];

        $settings[] = [
            'label'=>'Font Colour',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#070275'
        ];


        $settings[] = [
            'label'=>'Font Size',
            'type'=>'font_size',
            'name'=>'font_size',
            'value'=>'13'
        ];

        $settings[] = [
        'label'=>'Font Bold',
        'type'=>'checkbox',
        'name'=>'font_bold',
        'type'=>'checkbox',
        'value'=>false
        ];


        $settings[] = [
        'label'=>'Font Underline',
        'type'=>'checkbox',
        'name'=>'font_underline',
        'type'=>'checkbox',
        'value'=>false
        ];

        $settings[] = [
        'label'=>'Font Italics',
        'type'=>'checkbox',
        'name'=>'font_italics',
        'type'=>'checkbox',
        'value'=>false
        ];

        return $settings;
    }

    public static function getTemplate145Settings()
    {
        $settings[] = [
            'label'=>' Swipe Positioning',
            'type'=>'select',
            'name'=>'swipeAnimationPositioning',
            'options' => [
                'top' => "top",
                'bottom' => "bottom",
            ],
            'value'=>"top"
        ];

        $settings[] = [
            'type'=>'upload',
            'name'=>'pin_img',
            'label'=>'Upload New Distance Window Pin',
            'allow_empty'=>false,
            'value'=>"https://static.vic-m.co/templates/pin/template11/default.png"
        ];

        $settings[] = [
                'label'=>'Background Color',
                'type'=>'color',
                'name'=>'distancebackgroundColor',
                'value'=>'#ffffff'
        ];

        $settings[] = [
                'label'=>'Pin Top Color',
                'type'=>'color',
                'name'=>'distanceTopColor',
                'value'=>'#759AC6'
        ];

        $settings[] = [
            'label'=>'Distance Inner-Circle Color',
            'type'=>'color',
            'name'=>'distanceInnerCircleColor',
            'value'=>'#075D86'
        ];

        $settings[] = [
                'label'=>'Pin Bottom Color',
                'type'=>'color',
                'name'=>'distanceBottomColor',
                'value'=>'#075D86'
        ];

        $settings[] = [
            'label'=>'Animated Distance Window',
            'type'=>'select',
            'name'=>'animated_window',
            'value'=>"none",
            'options'=>[
                "none"=>"None",
                "falling"=>"Falling Down",
                "upDown"=>"Falling Up and Down",
                "all"=>"All Directions",
                "roulette"=>"Roulette"
            ]
        ];

        $settings[] = [
            'label'=>'Pin Display Time',
            'type'=>'loop',
            'name'=>'loop_speed_pins',
            'value'=>2,
            'multiply'=>1,
            'min'=>1,
            'max'=>5,
        ];

        $settings[] = [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>true,
            'value'=>"%%DISTANCE%%"
        ];

        $settings[] = [
            'type'=>'font',
            'name'=>'fonts_all',
            'label'=>'Google Fonts',
            'value'=>"Open Sans" //Please use google fonts
        ];

        $settings[] = [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
        ];

        $settings[] = [
            'label'=>'Font Colour',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#070275'
        ];


        $settings[] = [
            'label'=>'Font Size',
            'type'=>'font_size',
            'name'=>'font_size',
            'value'=>'13'
        ];

        $settings[] = [
        'label'=>'Font Bold',
        'type'=>'checkbox',
        'name'=>'font_bold',
        'type'=>'checkbox',
        'value'=>false
        ];


        $settings[] = [
        'label'=>'Font Underline',
        'type'=>'checkbox',
        'name'=>'font_underline',
        'type'=>'checkbox',
        'value'=>false
        ];

        $settings[] = [
        'label'=>'Font Italics',
        'type'=>'checkbox',
        'name'=>'font_italics',
        'type'=>'checkbox',
        'value'=>false
        ];

        return $settings;
    }

    public static function getTemplate7Settings()
    {
        $settings = [
        [
        'label'=>'Use Customisable Distance Pin?',
        'type'=>'checkbox',
        'name'=>'customisable_pin_yn',
        'value'=>true,
        ],
        [
        'type'=>'upload',
        'name'=>'pin_img',
        'label'=>'Upload New Distance Window Pin',
        'allow_empty'=>true,
        'value'=>"https://static.vic-m.co/templates/pin/template7/default.svg",
        ],
        [
        'label'=>'Distance Pin Display Time(s)',
        'type'=>'loop',
        'name'=>'loop_speed',
        'value'=>8,
        'multiply'=>1,
        'min'=>1,
        'max'=>20,
        ],
        [
        'label'=>'Pin Background Color',
        'type'=>'color',
        'name'=>'distancebackgroundColor',
        'value'=>'#FFFFFF'
        ],
        [
        'label'=>'Pin Top Color',
        'type'=>'color',
        'name'=>'distanceTopColor',
        'value'=>'#759AC6'
        ],
        [
        'label'=>'Pin Bottom Color',
        'type'=>'color',
        'name'=>'distanceBottomColor',
        'value'=>'#075D86'
        ],
        [
        'label'=>'Pin Inner-Circle Color',
        'type'=>'color',
        'name'=>'distanceInnerCircleColor',
        'value'=>'#075D86'
        ],
        [
        'type'=>'text',
        'name'=>'distance_value_text',
        'label'=>'Display Message',
        'allow_empty'=>true,
        'value'=>"%%DISTANCE%%"
        ],
        [
        'type'=>'font',
        'name'=>'fonts_all',
        'label'=>'Google Fonts',
        'value'=>"Open Sans" //Please use google fonts
        ],
        [
        'type'=>'custom_font',
        'name'=>'custom_font',
        'label'=>'Custom Fonts',
        'value'=>''
        ],
        [
        'label'=>'Font Colour',
        'type'=>'color', //uses text box and comes with color picker
        'name'=>'font_color',
        'value'=>'#175582'
        ],
        [
        'label'=>'Font Size',
        'type'=>'font_size',
        'name'=>'font_size',
        'value'=>'13'
        ],
        [
        'label'=>'Font Bold',
        'type'=>'checkbox',
        'name'=>'font_bold',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'label'=>'Font Underline',
        'type'=>'checkbox',
        'name'=>'font_underline',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'label'=>'Font Italics',
        'type'=>'checkbox',
        'name'=>'font_italics',
        'type'=>'checkbox',
        'value'=>false
        ]

        ];
        return $settings;
    }

    public static function getTemplate8Settings()
    {
        $settings = [];

        $settings = [
                [
                    'type'=>'text',
                    'name'=>'distance_value_text',
                    'label'=>'Display Message',
                    'allow_empty'=>true,
                    'value'=>"%%DISTANCE%%"
                ],
                [
                    'label'=>'Background Color',
                    'type'=>'color', //uses text box and comes with color picker
                    'name'=>'backgroundColor',
                    'value'=>'#ffffff'
                ],
                [
                    'type'=>'font',
                    'name'=>'fonts_all',
                    'label'=>'Google Fonts',
                    'value'=>"Open Sans" //Please use google fonts
                ],
                [
                    'type'=>'custom_font',
                    'name'=>'custom_font',
                    'label'=>'Custom Fonts',
                    'value'=>''
                ],
                [
                    'label'=>'Font Colour',
                    'type'=>'color', //uses text box and comes with color picker
                    'name'=>'font_color',
                    'value'=>'#070275'
                ],
                [
                    'type'=>'text',
                    'name'=>'from_location',
                    'label'=>'Location Address',
                    'allow_empty'=>true,
                    'value'=>"From your location"
                ],
                [
                    'label'=>'Font Size',
                    'type'=>'font_size',
                    'name'=>'font_size',
                    'value'=>'13'
                ],
                [
                    'label'=>'Font Bold',
                    'type'=>'checkbox',
                    'name'=>'font_bold',
                    'type'=>'checkbox',
                    'value'=>false
                ],
                [
                    'label'=>'Font Underline',
                    'type'=>'checkbox',
                    'name'=>'font_underline',
                    'type'=>'checkbox',
                    'value'=>false
                ],
                [
                    'label'=>'Font Italics',
                    'type'=>'checkbox',
                    'name'=>'font_italics',
                    'type'=>'checkbox',
                    'value'=>false
                ],
            ];

        $settings[] = [
                    'label'=>'Use Customisable Distance Pin?',
                    'type'=>'checkbox',
                    'name'=>'customisable_pin_yn',
                    'value'=>false
            ];

        $settings[] = [
                    'type'=>'upload',
                    'name'=>'pin_img',
                    'label'=>'Upload New Distance Window Pin',
                    'allow_empty'=>true,
                    'value'=>"https://static.vic-m.co/templates/pin/template8/default.png"
            ];
        $settings[] = [
                    'label'=>'Pin Background Color',
                    'type'=>'color',
                    'name'=>'distancebackgroundColor',
                    'value'=>'#FFFFFF'
            ];
        $settings[] = [
                    'label'=>'Pin Top Color',
                    'type'=>'color',
                    'name'=>'distanceTopColor',
                    'value'=>'#759AC6'
            ];
        $settings[] = [
                    'label'=>'Pin Bottom Color',
                    'type'=>'color',
                    'name'=>'distanceBottomColor',
                    'value'=>'#075D86'
            ];
        $settings[] = [
                    'label'=>'Pin Inner-Circle Color',
                    'type'=>'color',
                    'name'=>'distanceInnerCircleColor',
                    'value'=>'#075D86'
            ];
        $settings[] = [
                    'label'=>'Pin Width',
                    'type'=>'number',
                    'name'=>'distanceWidth',
                    'value'=>'115'
            ];
        $settings[] = [
                    'label'=>'Pin Height',
                    'type'=>'number',
                    'name'=>'distanceHeight',
                    'value'=>'50'
            ];
        $settings[] = [
                    'label'=>'Pin Top',
                    'type'=>'number',
                    'name'=>'distanceTop',
                    'value'=>'0'
            ];
        $settings[] = [
                    'label'=>'Pin Left',
                    'type'=>'number',
                    'name'=>'distanceLeft',
                    'value'=>'0'
            ];


        return $settings;
    }

    public static function getTemplateSettings()
    {
        $settings = [];

        $settings = [
                [
                    'type'=>'upload',
                    'name'=>'pin_img',
                    'label'=>'Upload New Distance Window Pin',
                    'allow_empty'=>true,
                    'value'=>"https://static.vic-m.co/templates/pin/template10/default.png"
                ],
                [
                    'type'=>'font',
                    'name'=>'fonts_all',
                    'label'=>'Google Fonts',
                    'value'=>"Open Sans" //Please use google fonts
                ],
                [
                    'type'=>'custom_font',
                    'name'=>'custom_font',
                    'label'=>'Custom Fonts',
                    'value'=>''
                ],
                [
                    'label'=>'Font Colour',
                    'type'=>'color', //uses text box and comes with color picker
                    'name'=>'font_color',
                    'value'=>'#070275'
                ],
                [
                    'type'=>'text',
                    'name'=>'from_location',
                    'label'=>'Location Address',
                    'allow_empty'=>true,
                    'value'=>"From your location"
                ],
                [
                    'label'=>'Font Size',
                    'type'=>'font_size',
                    'name'=>'font_size',
                    'value'=>'13'
                ],
            ];
        return $settings;
    }

    public static function getTemplate9Settings()
    {
        $settings = [];

        $settings = [
                [
                    'type'=>'upload',
                    'name'=>'pin_img',
                    'label'=>'Upload New Distance Window Pin',
                    'allow_empty'=>true,
                    'value'=>"https://static.vic-m.co/templates/pin/template8/default.png"
                ],
                [
                    'type'=>'font',
                    'name'=>'fonts_all',
                    'label'=>'Google Fonts',
                    'value'=>"Open Sans" //Please use google fonts
                ],
                [
                    'type'=>'custom_font',
                    'name'=>'custom_font',
                    'label'=>'Custom Fonts',
                    'value'=>''
                ],
                [
                    'label'=>'Font Colour',
                    'type'=>'color', //uses text box and comes with color picker
                    'name'=>'font_color',
                    'value'=>'#070275'
                ],
                [
                    'type'=>'text',
                    'name'=>'from_location',
                    'label'=>'Location Address',
                    'allow_empty'=>true,
                    'value'=>"From your location"
                ],
                [
                    'label'=>'Font Size',
                    'type'=>'font_size',
                    'name'=>'font_size',
                    'value'=>'13'
                ],
                [
                    'label'=>'Font Bold',
                    'type'=>'checkbox',
                    'name'=>'font_bold',
                    'type'=>'checkbox',
                    'value'=>false
                ],
                [
                    'label'=>'Font Underline',
                    'type'=>'checkbox',
                    'name'=>'font_underline',
                    'type'=>'checkbox',
                    'value'=>false
                ],
                [
                    'label'=>'Font Italics',
                    'type'=>'checkbox',
                    'name'=>'font_italics',
                    'type'=>'checkbox',
                    'value'=>false
                ],
            ];
        return $settings;
    }

    public static function getTemplate1Settings()
    {
        $settings = [];

        $settings = [

                [
                    'type'=>'upload',
                    'name'=>'pin_img',
                    'label'=>'Upload New Distance Window Pin',
                    'allow_empty'=>true,
                    'value'=>"https://static.vic-m.co/templates/pin/template17/default.png"
                ],

                [
                    'label'=>'Background Color',
                    'type'=>'color', //uses text box and comes with color picker
                    'name'=>'backgroundColor',
                    'value'=>'#ffffff'
                ],
                [
                    'type'=>'text',
                    'name'=>'distance_value_text',
                    'label'=>'Display Message',
                    'allow_empty'=>true,
                    'value'=>"%%DISTANCE%% Away"
                ],
                [
                    'type'=>'font',
                    'name'=>'fonts_all',
                    'label'=>'Google Fonts',
                    'value'=>"Open Sans" //Please use google fonts
                ],
                [
                    'type'=>'custom_font',
                    'name'=>'custom_font',
                    'label'=>'Custom Fonts',
                    'value'=>''
                ],
                [
                    'label'=>'Font Colour',
                    'type'=>'color', //uses text box and comes with color picker
                    'name'=>'font_color',
                    'value'=>'#070275'
                ],
                [
                    'label'=>'Font Size',
                    'type'=>'font_size',
                    'name'=>'font_size',
                    'value'=>'13'
                ],
            ];
        return $settings;
    }

    public static function getTemplate27Settings()
    {
        $settings = [];
        $settings = [
            [
                'label'=>'Use Customisable Distance Pin?',
                'type'=>'checkbox',
                'name'=>'customisable_pin_yn',
                'value'=>true,
                'class'=>'hide' //hidden to provide as unchangeable default
            ],
            [
                'type'=>'upload',
                'name'=>'pin_img',
                'label'=>'Upload New Distance Window Pin',
                'allow_empty'=>true,
                'value'=>"https://static.vic-m.co/templates/pin/template27/default.svg",
                'class'=>'hide' //hidden to provide as unchangeable default
            ],
            [
                'label'=>'Pin Background Color',
                'type'=>'color',
                'name'=>'distancebackgroundColor',
                'value'=>'#FFFFFF'
            ],
            [
                'label'=>'Pin Top Color',
                'type'=>'color',
                'name'=>'distanceTopColor',
                'value'=>'#759AC6'
            ],
            [
                'label'=>'Pin Bottom Color',
                'type'=>'color',
                'name'=>'distanceBottomColor',
                'value'=>'#075D86'
            ],
            [
                'label'=>'Pin Inner-Circle Color',
                'type'=>'color',
                'name'=>'distanceInnerCircleColor',
                'value'=>'#075D86'
            ],
            [
                'label'=>'Pin Width',
                'type'=>'number',
                'name'=>'distanceWidth',
                'value'=>'300',
                'class'=>'hide'
            ],
            [
                'label'=>'Pin Height',
                'type'=>'number',
                'name'=>'distanceHeight',
                'value'=>'75',
                'class'=>'hide'
            ],
            [
                'label'=>'Pin Top',
                'type'=>'number',
                'name'=>'distanceTop',
                'value'=>'0',
                'class'=>'hide'
            ],
            [
                'label'=>'Pin Left',
                'type'=>'number',
                'name'=>'distanceLeft',
                'value'=>'0',
                'class'=>'hide'
            ],
            [
                'label'=>'Background Color',
                'type'=>'color',
                'name'=>'backgroundColor',
                'value'=>'#FFFFFF'
            ],
            [
                'type'=>'text',
                'name'=>'distance_value_text',
                'label'=>'Display Message',
                'allow_empty'=>false,
                'value'=>"%%DISTANCE%%",
            ],
            [
                'type'=>'font',
                'name'=>'fonts_all',
                'label'=>'Google Fonts',
                'value'=>"Open Sans" //Please use google fonts
            ],
            [
                'type'=>'custom_font',
                'name'=>'custom_font',
                'label'=>'Custom Fonts',
                'value'=>''
            ],
            [
                'label'=>'Font Colour',
                'type'=>'color', //uses text box and comes with color picker
                'name'=>'font_color',
                'value'=>'#175582'
            ],
            [
                'label'=>'Font Size',
                'type'=>'font_size',
                'name'=>'font_size',
                'value'=>'13'
            ],
            [
                'label'=>'Font Bold',
                'type'=>'checkbox',
                'name'=>'font_bold',
                'type'=>'checkbox',
                'value'=>false
            ],
            [
                'label'=>'Font Underline',
                'type'=>'checkbox',
                'name'=>'font_underline',
                'type'=>'checkbox',
                'value'=>false
            ],
            [
                'label'=>'Font Italics',
                'type'=>'checkbox',
                'name'=>'font_italics',
                'type'=>'checkbox',
                'value'=>false
            ],

        ];
        return $settings;
    }

    public static function getTemplate10Settings()
    {
        $settings = [];
        $settings = [
            [
                'label'=>'Loop Speed',
                'type'=>'loop',
                'name'=>'loop_speed',
                'value'=>'1200',
                'multiply'=>100,
                'min'=>30,
                'max'=>50,
            ],
            [
                'label'=>'Use Customisable Distance Pin?',
                'type'=>'checkbox',
                'name'=>'customisable_pin_yn',
                'value'=>true,
            ],
            [
                'type'=>'upload',
                'name'=>'pin_img',
                'label'=>'Upload New Distance Window Pin',
                'allow_empty'=>false,
                'value'=>"https://static.vic-m.co/templates/pin/template10/images/vicinity.png",

            ],
            [
                'label'=>'Pin Background Color',
                'type'=>'color',
                'name'=>'distancebackgroundColor',
                'value'=>'#FFFFFF'
            ],
            [
                'label'=>'Pin Top Color',
                'type'=>'color',
                'name'=>'distanceTopColor',
                'value'=>'#759AC6'
            ],
            [
                'label'=>'Pin Bottom Color',
                'type'=>'color',
                'name'=>'distanceBottomColor',
                'value'=>'#075D86'
            ],
            [
                'label'=>'Pin Inner-Circle Color',
                'type'=>'color',
                'name'=>'distanceInnerCircleColor',
                'value'=>'#075D86'
            ],
            [
                'label'=>'Pin Width',
                'type'=>'number',
                'name'=>'distanceWidth',
                'value'=>'300',
            ],
            [
                'label'=>'Pin Height',
                'type'=>'number',
                'name'=>'distanceHeight',
                'value'=>'75',
            ],
            [
                'label'=>'Pin Top',
                'type'=>'number',
                'name'=>'distanceTop',
                'value'=>'0',
            ],
            [
                'label'=>'Pin Left',
                'type'=>'number',
                'name'=>'distanceLeft',
                'value'=>'0',
            ],
            [
                'label'=>'Background Color',
                'type'=>'color',
                'name'=>'backgroundColor',
                'value'=>'#FFFFFF'
            ],
            [
                'type'=>'text',
                'name'=>'distance_value_text',
                'label'=>'Display Message',
                'allow_empty'=>false,
                'value'=>"%%DISTANCE%%",
            ],
            [
                'type'=>'font',
                'name'=>'fonts_all',
                'label'=>'Google Fonts',
                'value'=>"Open Sans" //Please use google fonts
            ],
            [
                'type'=>'custom_font',
                'name'=>'custom_font',
                'label'=>'Custom Fonts',
                'value'=>''
            ],
            [
                'label'=>'Font Colour',
                'type'=>'color', //uses text box and comes with color picker
                'name'=>'font_color',
                'value'=>'#175582'
            ],
            [
                'label'=>'Font Size',
                'type'=>'font_size',
                'name'=>'font_size',
                'value'=>'13'
            ],
            [
                'label'=>'Font Bold',
                'type'=>'checkbox',
                'name'=>'font_bold',
                'type'=>'checkbox',
                'value'=>false
            ],
            [
                'label'=>'Font Underline',
                'type'=>'checkbox',
                'name'=>'font_underline',
                'type'=>'checkbox',
                'value'=>false
            ],
            [
                'label'=>'Font Italics',
                'type'=>'checkbox',
                'name'=>'font_italics',
                'type'=>'checkbox',
                'value'=>false
            ],

        ];
        return $settings;
    }

    public static function getTemplate70Settings()
    {
        $settings=[];

        $settings = [
            [
            'label'=>'Use Customisable Distance Pin?',
            'type'=>'checkbox',
            'name'=>'customisable_pin_yn',
            'value'=>true,
            ],
            [
            'label'=>'Pin Background Color',
            'type'=>'color',
            'name'=>'distancebackgroundColor',
            'value'=>'#FFFFFF'
            ],
            [
            'label'=>'Pin Top Color',
            'type'=>'color',
            'name'=>'distanceTopColor',
            'value'=>'#759AC6'
            ],
            [
            'label'=>'Pin Bottom Color',
            'type'=>'color',
            'name'=>'distanceBottomColor',
            'value'=>'#075D86'
            ],
            [
            'label'=>'Pin Inner-Circle Color',
            'type'=>'color',
            'name'=>'distanceInnerCircleColor',
            'value'=>'#075D86'
            ],
            [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>false,
            'value'=>"%%DISTANCE%%",
            ],
            [
            'type'=>'font',
            'name'=>'fonts_all',
            'label'=>'Google Fonts',
            'value'=>"Open Sans" //Please use google fonts
            ],
            [
            'type'=>'custom_font',
            'name'=>'custom_font',
            'label'=>'Custom Fonts',
            'value'=>''
            ],
            [
            'label'=>'Font Colour',
            'type'=>'color', //uses text box and comes with color picker
            'name'=>'font_color',
            'value'=>'#175582'
            ],

            [
            'label'=>'Font Bold',
            'type'=>'checkbox',
            'name'=>'font_bold',
            'type'=>'checkbox',
            'value'=>false
            ],
            [
            'label'=>'Font Underline',
            'type'=>'checkbox',
            'name'=>'font_underline',
            'type'=>'checkbox',
            'value'=>false
            ],
            [
            'label'=>'Font Italics',
            'type'=>'checkbox',
            'name'=>'font_italics',
            'type'=>'checkbox',
            'value'=>false
            ],
            [
            'type'=>'upload',
            'name'=>'pin_img',
            'label'=>'Upload New Distance Window Pin',
            'allow_empty'=>true,
            'value'=>"https://static.vic-m.co/templates/pin/template11/default.png",
            ],
            [
            'label'=>'Font Size',
            'type'=>'font_size',
            'name'=>'font_size',
            'value'=>'20'
            ],
            [
            'label'=>'Tiles Speed',
            'name'=>'loop_speed',
            'type'=>'loop',
            'value'=>2,
            'multiply'=>1,
            'min'=>1,
            'max'=>20,
            ],
            [
            'label'=>'Slide Speed',
            'name'=>'loop_speed2',
            'type'=>'loop',
            'value'=>1,
            'multiply'=>1,
            'min'=>1,
            'max'=>20,
            ],
            [
            'label'=>'Transition Speed',
            'name'=>'loop_speed3',
            'type'=>'loop',
            'value'=>1,
            'multiply'=>1,
            'min'=>1,
            'max'=>20,
            ],


            [
            'label'=>'Slide Style Effects',
            'type'=>'select',
            'name'=>'style_effects',
            'value'=>'reduce','simple','left','up','leftright','updown','switchlr','switchup','fliplr','flipud','360',
                'options'=>[
                    'none'=>"default",
                    'simple'=>"simple",
                    'left'=>"left",
                    'up'=>"up",
                    'leftright'=>"leftright",
                    'updown'=>"updown",
                    'switchlr'=>"switchlr",
                    'switchup'=>"switchup",
                    'fliplr'=>"fliplr",
                    'flipud'=>"flipud",
                    'reduce'=>"reduce",
                    '360'=>"360",
                ]
            ],

            [
            'label'=>'Tiles in x axis',
            'type'=>'loop',
            'name'=>'tile_x',
            'value'=>6,
            'multiply'=>1,
            'min'=>1,
            'max'=>20,
            ],
            [
            'label'=>'Tiles in y axis',
            'type'=>'loop',
            'name'=>'tile_y',
            'value'=>6,
            'multiply'=>1,
            'min'=>1,
            'max'=>20,
            ],

            ['label'=>'Reverse Animation',
            'type'=>'select',
            'name'=>'reverse_animation',
            'value'=>'false',
                'options'=>[
                    'false'=>"No",
                    'true'=>"Yes",
                ]
            ],
            ['label'=>'Back Reverse Animation',
            'type'=>'select',
            'name'=>'backreverse_animation',
            'value'=>'false',
                'options'=>[
                    'false'=>"No",
                    'true'=>"Yes",
                ]
            ],

            ['label'=>'Rewind Animation',
            'type'=>'select',
            'name'=>'rewind_animation',
            'value'=>'false',
                'options'=>[
                    'false'=>"No",
                    'true'=>"Yes",
                ]
            ],



        ];
        return $settings;
    }

    public static function getTemplate59Settings()
    {
        $settings = [];
        $settings = [
        [
        'label'=>'Use Customisable Distance Pin?',
        'type'=>'checkbox',
        'name'=>'customisable_pin_yn',
        'value'=>true,
        ],

        [
        'label'=>'Pin Background Color',
        'type'=>'color',
        'name'=>'distancebackgroundColor',
        'value'=>'#FFFFFF'
        ],
        [
        'label'=>'Pin Top Color',
        'type'=>'color',
        'name'=>'distanceTopColor',
        'value'=>'#759AC6'
        ],
        [
        'label'=>'Pin Bottom Color',
        'type'=>'color',
        'name'=>'distanceBottomColor',
        'value'=>'#075D86'
        ],
        [
        'label'=>'Pin Inner-Circle Color',
        'type'=>'color',
        'name'=>'distanceInnerCircleColor',
        'value'=>'#075D86'
        ],

        [
            'label'=>'Loop Speed',
            'type'=>'loop',
            'name'=>'loop_speed',
            'value'=>10,
            'multiply'=>1,
            'min'=>1,
            'max'=>25,
        ],

        [
        'type'=>'text',
        'name'=>'distance_value_text',
        'label'=>'Display Message',
        'allow_empty'=>false,
        'value'=>"%%DISTANCE%%",
        ],
        [
        'type'=>'font',
        'name'=>'fonts_all',
        'label'=>'Google Fonts',
        'value'=>"Open Sans" //Please use google fonts
        ],
        [
        'type'=>'custom_font',
        'name'=>'custom_font',
        'label'=>'Custom Fonts',
        'value'=>''
        ],
        [
        'label'=>'Font Colour',
        'type'=>'color', //uses text box and comes with color picker
        'name'=>'font_color',
        'value'=>'#175582'
        ],

        [
        'label'=>'Font Bold',
        'type'=>'checkbox',
        'name'=>'font_bold',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'label'=>'Font Underline',
        'type'=>'checkbox',
        'name'=>'font_underline',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'label'=>'Font Italics',
        'type'=>'checkbox',
        'name'=>'font_italics',
        'type'=>'checkbox',
        'value'=>false
        ],
        [
        'label'=>'Auto Play',
        'type'=>'select',
        'name'=>'auto_play',
        'value'=>true,
            'options'=>[
                'false'=>"No",
                'true'=>"Yes",
            ]
            ],
        [
        'label'=>'Navigation Arrow Style',
        'type'=>'select',
        'name'=>'navigation_arrows',
        'value'=>5,
            'options'=>[
                'none'=>"None",
                '1'=>"Style 1",
                '2'=>"Style 2",
                '3'=>"Style 3",
                '4'=>"Style 4",
                '5'=>"Style 5",
                '6'=>"Style 6",
                '7'=>"Style 7",
                '8'=>"Style 8",
            ]
            ],

        [
        'label'=>'Navigation Arrow Color',
        'type'=>'color', //uses text box and comes with color picker
        'name'=>'nav_color',
        'value'=>'rgba(0,0,0,0.81)'
        ],
        [
        'label'=>'Navigation Arrow Size',
        'type'=>'number',
        'name'=>'nav_size',
        'value'=>'40',
        ],
        [
        'label'=>'Carousel Tracker',
        'type'=>'hidden',
        'name'=>'carousel_images',
        'value'=>"/templates/pin/template42/Svg.svg",
        ],
        [
        'label'=>'',
        'type'=>'hidden',
        'name'=>'carousel_order',
        'value'=>"",
        ],

            [
                'label' => 'Nav Dots',
                'type' => 'select',
                'name' => 'dots',
                'value' => true,
                'options' => [
                    'false' => "No",
                    'true' => "Yes",
                ]
            ],



        ];

        $settings[] = [
                    'type'=>'upload',
                    'name'=>'pin_img',
                    'label'=>'Upload New Distance Window Pin',
                    'allow_empty'=>true,
                    'value'=>"https://static.vic-m.co/templates/pin/template11/default.png",
            ];
        $settings[] =           [
                'label'=>'Font Size',
                'type'=>'font_size',
                'name'=>'font_size',
                'value'=>'20'
            ];


        return $settings;
    }
    public static function getTemplate11Settings()
    {
        $settings = [];
        $settings = [
            [
                'label'=>'Loop Speed',
                'type'=>'loop',
                'name'=>'loop_speed',
                'value'=>'3',
                'multiply'=>600,
                'min'=>1,
                'max'=>20,
            ],
            [
                'label'=>'Use Customisable Distance Pin?',
                'type'=>'checkbox',
                'name'=>'customisable_pin_yn',
                'value'=>true,
            ],
            [
                'type'=>'upload',
                'name'=>'pin_img',
                'label'=>'Upload New Distance Window Pin',
                'allow_empty'=>true,
                'value'=>"https://static.vic-m.co/templates/pin/template11/default.png",

            ],

            [
                'label'=>'Pin Background Color',
                'type'=>'color',
                'name'=>'distancebackgroundColor',
                'value'=>'#FFFFFF'
            ],
            [
                'label'=>'Pin Top Color',
                'type'=>'color',
                'name'=>'distanceTopColor',
                'value'=>'#759AC6'
            ],
            [
                'label'=>'Pin Bottom Color',
                'type'=>'color',
                'name'=>'distanceBottomColor',
                'value'=>'#075D86'
            ],
            [
                'label'=>'Pin Inner-Circle Color',
                'type'=>'color',
                'name'=>'distanceInnerCircleColor',
                'value'=>'#075D86'
            ],

            [
                'type'=>'text',
                'name'=>'distance_value_text',
                'label'=>'Display Message',
                'allow_empty'=>false,
                'value'=>"%%DISTANCE%%",
            ],
            [
                'type'=>'font',
                'name'=>'fonts_all',
                'label'=>'Google Fonts',
                'value'=>"Open Sans" //Please use google fonts
            ],
            [
                'type'=>'custom_font',
                'name'=>'custom_font',
                'label'=>'Custom Fonts',
                'value'=>''
            ],
            [
                'label'=>'Font Colour',
                'type'=>'color', //uses text box and comes with color picker
                'name'=>'font_color',
                'value'=>'#175582'
            ],
            [
                'label'=>'Font Size',
                'type'=>'font_size',
                'name'=>'font_size',
                'value'=>'13'
            ],
            [
                'label'=>'Font Bold',
                'type'=>'checkbox',
                'name'=>'font_bold',
                'type'=>'checkbox',
                'value'=>false
            ],
            [
                'label'=>'Font Underline',
                'type'=>'checkbox',
                'name'=>'font_underline',
                'type'=>'checkbox',
                'value'=>false
            ],
            [
                'label'=>'Font Italics',
                'type'=>'checkbox',
                'name'=>'font_italics',
                'type'=>'checkbox',
                'value'=>false
            ],

        ];
        return $settings;
    }


    public static function getTemplate28Settings()
    {
        return [];
    }
    public static function getTemplate15Settings()
    {
        $settings = [];
        $settings = [
            [
                'label'=>'Loop Speed',
                'type'=>'loop',
                'name'=>'loop_speed',
                'value'=>'1200',
                'multiply'=>250,
                'min'=>30,
                'max'=>50,
            ],
            [
                'label'=>'Use Customisable Distance Pin?',
                'type'=>'checkbox',
                'name'=>'customisable_pin_yn',
                'value'=>false,
            ],
            [
                'type'=>'upload',
                'name'=>'pin_img',
                'label'=>'Upload New Distance Window Pin',
                'allow_empty'=>true,
                'value'=>"https://static.vic-m.co/templates/pin/template11/default.png",

            ],
            [
                'label'=>'Pin Background Color',
                'type'=>'color',
                'name'=>'distancebackgroundColor',
                'value'=>'#FFFFFF'
            ],
            [
                'label'=>'Pin Top Color',
                'type'=>'color',
                'name'=>'distanceTopColor',
                'value'=>'#759AC6'
            ],
            [
                'label'=>'Pin Bottom Color',
                'type'=>'color',
                'name'=>'distanceBottomColor',
                'value'=>'#075D86'
            ],
            [
                'label'=>'Pin Inner-Circle Color',
                'type'=>'color',
                'name'=>'distanceInnerCircleColor',
                'value'=>'#075D86'
            ],
            [
                'label'=>'Pin Width',
                'type'=>'number',
                'name'=>'distanceWidth',
                'value'=>'300',
            ],
            [
                'label'=>'Pin Height',
                'type'=>'number',
                'name'=>'distanceHeight',
                'value'=>'75',
            ],
            [
                'label'=>'Pin Top',
                'type'=>'number',
                'name'=>'distanceTop',
                'value'=>'0',
            ],
            [
                'label'=>'Pin Left',
                'type'=>'number',
                'name'=>'distanceLeft',
                'value'=>'0',
            ],
            [
                'label'=>'Background Color',
                'type'=>'color',
                'name'=>'backgroundColor',
                'value'=>'#FFFFFF'
            ],
            [
                'type'=>'text',
                'name'=>'distance_value_text',
                'label'=>'Display Message',
                'allow_empty'=>false,
                'value'=>"%%DISTANCE%%",
            ],
            [
                'type'=>'font',
                'name'=>'fonts_all',
                'label'=>'Google Fonts',
                'value'=>"Open Sans" //Please use google fonts
            ],
            [
                'type'=>'custom_font',
                'name'=>'custom_font',
                'label'=>'Custom Fonts',
                'value'=>''
            ],
            [
                'label'=>'Font Colour',
                'type'=>'color', //uses text box and comes with color picker
                'name'=>'font_color',
                'value'=>'#175582'
            ],
            [
                'label'=>'Font Size',
                'type'=>'font_size',
                'name'=>'font_size',
                'value'=>'13'
            ],
            [
                'label'=>'Font Bold',
                'type'=>'checkbox',
                'name'=>'font_bold',
                'type'=>'checkbox',
                'value'=>false
            ],
            [
                'label'=>'Font Underline',
                'type'=>'checkbox',
                'name'=>'font_underline',
                'type'=>'checkbox',
                'value'=>false
            ],
            [
                'label'=>'Font Italics',
                'type'=>'checkbox',
                'name'=>'font_italics',
                'type'=>'checkbox',
                'value'=>false
            ],
        ];
        return $settings;
    }
    public static function getTemplate14Settings()
    {
        $settings = [];
        $settings = [
            [
                'label'=>'Loop Speed',
                'type'=>'loop',
                'name'=>'loop_speed',
                'value'=>'1200',
                'multiply'=>100,
                'min'=>30,
                'max'=>50,
            ],
            [
                'label'=>'Use Customisable Distance Pin?',
                'type'=>'checkbox',
                'name'=>'customisable_pin_yn',
                'value'=>false,
            ],
            [
                'type'=>'upload',
                'name'=>'pin_img',
                'label'=>'Upload New Distance Window Pin',
                'allow_empty'=>true,
                'value'=>"https://static.vic-m.co/templates/pin/template11/default.png",

            ],
            [
                'label'=>'Pin Background Color',
                'type'=>'color',
                'name'=>'distancebackgroundColor',
                'value'=>'#FFFFFF'
            ],
            [
                'label'=>'Pin Top Color',
                'type'=>'color',
                'name'=>'distanceTopColor',
                'value'=>'#759AC6'
            ],
            [
                'label'=>'Pin Bottom Color',
                'type'=>'color',
                'name'=>'distanceBottomColor',
                'value'=>'#075D86'
            ],
            [
                'label'=>'Pin Inner-Circle Color',
                'type'=>'color',
                'name'=>'distanceInnerCircleColor',
                'value'=>'#075D86'
            ],
            [
                'label'=>'Pin Width',
                'type'=>'text',
                'name'=>'distanceWidth',
                'value'=>'100%',
            ],
            [
                'label'=>'Pin Height',
                'type'=>'text',
                'name'=>'distanceHeight',
                'value'=>'100%',
            ],
            [
                'label'=>'Pin Top',
                'type'=>'number',
                'name'=>'distanceTop',
                'value'=>'0',
            ],
            [
                'label'=>'Pin Left',
                'type'=>'number',
                'name'=>'distanceLeft',
                'value'=>'0',
            ],
            [
                'label'=>'Background Color',
                'type'=>'color',
                'name'=>'backgroundColor',
                'value'=>'#FFFFFF'
            ],
            [
                'type'=>'text',
                'name'=>'distance_value_text',
                'label'=>'Display Message',
                'allow_empty'=>false,
                'value'=>"%%DISTANCE%%",
            ],
            [
                'type'=>'font',
                'name'=>'fonts_all',
                'label'=>'Google Fonts',
                'value'=>"Open Sans" //Please use google fonts
            ],
            [
                'type'=>'custom_font',
                'name'=>'custom_font',
                'label'=>'Custom Fonts',
                'value'=>''
            ],
            [
                'label'=>'Font Colour',
                'type'=>'color', //uses text box and comes with color picker
                'name'=>'font_color',
                'value'=>'#175582'
            ],
            [
                'label'=>'Font Size',
                'type'=>'font_size',
                'name'=>'font_size',
                'value'=>'13'
            ],
            [
                'label'=>'Font Bold',
                'type'=>'checkbox',
                'name'=>'font_bold',
                'type'=>'checkbox',
                'value'=>false
            ],
            [
                'label'=>'Font Underline',
                'type'=>'checkbox',
                'name'=>'font_underline',
                'type'=>'checkbox',
                'value'=>false
            ],
            [
                'label'=>'Font Italics',
                'type'=>'checkbox',
                'name'=>'font_italics',
                'type'=>'checkbox',
                'value'=>false
            ],
        ];
        return $settings;
    }

    public static function getTemplate17Settings()
    {
        $settings = [];
        $settings = [
            [
                'label'=>'Loop Speed',
                'type'=>'loop',
                'name'=>'loop_speed',
                'value'=>'10',
                'multiply'=>10,
                'min'=>-20,
                'max'=>20,
            ],
            [
                'label'=>'Use Customisable Distance Pin?',
                'type'=>'checkbox',
                'name'=>'customisable_pin_yn',
                'value'=>false,
            ],
            [
                'type'=>'upload',
                'name'=>'pin_img',
                'label'=>'Upload New Distance Window Pin',
                'allow_empty'=>true,
                'value'=>"https://static.vic-m.co/templates/pin/template25/default.png",

            ],
            [
                'label'=>'Pin Background Color',
                'type'=>'color',
                'name'=>'distancebackgroundColor',
                'value'=>'#FFFFFF'
            ],
            [
                'label'=>'Pin Top Color',
                'type'=>'color',
                'name'=>'distanceTopColor',
                'value'=>'#759AC6'
            ],
            [
                'label'=>'Pin Bottom Color',
                'type'=>'color',
                'name'=>'distanceBottomColor',
                'value'=>'#075D86'
            ],
            [
                'label'=>'Pin Inner-Circle Color',
                'type'=>'color',
                'name'=>'distanceInnerCircleColor',
                'value'=>'#075D86'
            ],
            [
                'label'=>'Pin Width',
                'type'=>'number',
                'name'=>'distanceWidth',
                'value'=>'300',
            ],
            [
                'label'=>'Pin Height',
                'type'=>'number',
                'name'=>'distanceHeight',
                'value'=>'75',
            ],
            [
                'label'=>'Pin Top',
                'type'=>'number',
                'name'=>'distanceTop',
                'value'=>'0',
            ],
            [
                'label'=>'Pin Left',
                'type'=>'number',
                'name'=>'distanceLeft',
                'value'=>'0',
            ],
            [
                'label'=>'Background Color',
                'type'=>'color',
                'name'=>'backgroundColor',
                'value'=>'#FFFFFF'
            ],
            [
                'type'=>'text',
                'name'=>'distance_value_text',
                'label'=>'Display Message',
                'allow_empty'=>false,
                'value'=>"%%DISTANCE%%",
            ],
            [
                'type'=>'font',
                'name'=>'fonts_all',
                'label'=>'Google Fonts',
                'value'=>"Open Sans" //Please use google fonts
            ],
            [
                'type'=>'custom_font',
                'name'=>'custom_font',
                'label'=>'Custom Fonts',
                'value'=>''
            ],
            [
                'label'=>'Font Colour',
                'type'=>'color', //uses text box and comes with color picker
                'name'=>'font_color',
                'value'=>'#175582'
            ],
            [
                'label'=>'Font Size',
                'type'=>'font_size',
                'name'=>'font_size',
                'value'=>'13'
            ],
            [
                'label'=>'Font Bold',
                'type'=>'checkbox',
                'name'=>'font_bold',
                'type'=>'checkbox',
                'value'=>false
            ],
            [
                'label'=>'Font Underline',
                'type'=>'checkbox',
                'name'=>'font_underline',
                'type'=>'checkbox',
                'value'=>false
            ],
            [
                'label'=>'Font Italics',
                'type'=>'checkbox',
                'name'=>'font_italics',
                'type'=>'checkbox',
                'value'=>false
            ],

        ];
        return $settings;
    }

    public static function getTemplate12Settings()
    {
        $settings = [];
        $settings = [
            [
                'label'=>'Loop Speed',
                'type'=>'loop',
                'name'=>'loop_speed',
                'value'=>'1200',
                'multiply'=>100,
                'min'=>30,
                'max'=>50,
            ],
            [
                'label'=>'Use Customisable Distance Pin?',
                'type'=>'checkbox',
                'name'=>'customisable_pin_yn',
                'value'=>false,
            ],
            [
                'type'=>'upload',
                'name'=>'pin_img',
                'label'=>'Upload New Distance Window Pin',
                'allow_empty'=>true,
                'value'=>"https://static.vic-m.co/templates/pin/template12/default.png",

            ],
            [
                'label'=>'Pin Background Color',
                'type'=>'color',
                'name'=>'distancebackgroundColor',
                'value'=>'#FFFFFF'
            ],
            [
                'label'=>'Pin Top Color',
                'type'=>'color',
                'name'=>'distanceTopColor',
                'value'=>'#759AC6'
            ],
            [
                'label'=>'Pin Bottom Color',
                'type'=>'color',
                'name'=>'distanceBottomColor',
                'value'=>'#075D86'
            ],
            [
                'label'=>'Pin Inner-Circle Color',
                'type'=>'color',
                'name'=>'distanceInnerCircleColor',
                'value'=>'#075D86'
            ],
            [
                'label'=>'Pin Width',
                'type'=>'number',
                'name'=>'distanceWidth',
                'value'=>'300',
            ],
            [
                'label'=>'Pin Height',
                'type'=>'number',
                'name'=>'distanceHeight',
                'value'=>'75',
            ],
            [
                'label'=>'Pin Top',
                'type'=>'number',
                'name'=>'distanceTop',
                'value'=>'0',
            ],
            [
                'label'=>'Pin Left',
                'type'=>'number',
                'name'=>'distanceLeft',
                'value'=>'0',
            ],
            [
                'label'=>'Background Color',
                'type'=>'color',
                'name'=>'backgroundColor',
                'value'=>'#FFFFFF'
            ],
            [
                'type'=>'text',
                'name'=>'distance_value_text',
                'label'=>'Display Message',
                'allow_empty'=>false,
                'value'=>"%%DISTANCE%%",
            ],
            [
                'type'=>'font',
                'name'=>'fonts_all',
                'label'=>'Google Fonts',
                'value'=>"Open Sans" //Please use google fonts
            ],
            [
                'type'=>'custom_font',
                'name'=>'custom_font',
                'label'=>'Custom Fonts',
                'value'=>''
            ],
            [
                'label'=>'Font Colour',
                'type'=>'color', //uses text box and comes with color picker
                'name'=>'font_color',
                'value'=>'#175582'
            ],
            [
                'label'=>'Font Size',
                'type'=>'font_size',
                'name'=>'font_size',
                'value'=>'13'
            ],
            [
                'label'=>'Font Bold',
                'type'=>'checkbox',
                'name'=>'font_bold',
                'type'=>'checkbox',
                'value'=>false
            ],
            [
                'label'=>'Font Underline',
                'type'=>'checkbox',
                'name'=>'font_underline',
                'type'=>'checkbox',
                'value'=>false
            ],
            [
                'label'=>'Font Italics',
                'type'=>'checkbox',
                'name'=>'font_italics',
                'type'=>'checkbox',
                'value'=>false
            ],

        ];
        return $settings;
    }

    public static function getTemplate13Settings()
    {
        $settings = [
            [
                'label'=>'Loop Speed',
                'type'=>'loop',
                'name'=>'loop_speed',
                'value'=>'5',
                'multiply'=>1000,
                'min'=>1,
                'max'=>20,
            ],
            [
                'label'=>'Use Customisable Distance Pin?',
                'type'=>'checkbox',
                'name'=>'customisable_pin_yn',
                'value'=>true,
            ],
            [
                'type'=>'upload',
                'name'=>'pin_img',
                'label'=>'Upload New Distance Window Pin',
                'allow_empty'=>true,
                'value'=>"https://static.vic-m.co/templates/pin/template11/default.png",

            ],
            [
                'label'=>'Pin Background Color',
                'type'=>'color',
                'name'=>'distancebackgroundColor',
                'value'=>'#FFFFFF'
            ],
            [
                'label'=>'Pin Top Color',
                'type'=>'color',
                'name'=>'distanceTopColor',
                'value'=>'#759AC6'
            ],
            [
                'label'=>'Pin Bottom Color',
                'type'=>'color',
                'name'=>'distanceBottomColor',
                'value'=>'#075D86'
            ],
            [
                'label'=>'Pin Inner-Circle Color',
                'type'=>'color',
                'name'=>'distanceInnerCircleColor',
                'value'=>'#075D86'
            ],

            [
                'type'=>'text',
                'name'=>'distance_value_text',
                'label'=>'Display Message',
                'allow_empty'=>false,
                'value'=>"%%DISTANCE%%",
            ],
            [
                'type'=>'font',
                'name'=>'fonts_all',
                'label'=>'Google Fonts',
                'value'=>"Open Sans" //Please use google fonts
            ],
            [
                'type'=>'custom_font',
                'name'=>'custom_font',
                'label'=>'Custom Fonts',
                'value'=>''
            ],
            [
                'label'=>'Font Colour',
                'type'=>'color', //uses text box and comes with color picker
                'name'=>'font_color',
                'value'=>'#175582'
            ],
            [
                'label'=>'Font Size',
                'type'=>'font_size',
                'name'=>'font_size',
                'value'=>'13'
            ],
            [
                'label'=>'Font Bold',
                'type'=>'checkbox',
                'name'=>'font_bold',
                'type'=>'checkbox',
                'value'=>false
            ],
            [
                'label'=>'Font Underline',
                'type'=>'checkbox',
                'name'=>'font_underline',
                'type'=>'checkbox',
                'value'=>false
            ],
            [
                'label'=>'Font Italics',
                'type'=>'checkbox',
                'name'=>'font_italics',
                'type'=>'checkbox',
                'value'=>false
            ],
        ];
        return $settings;
    }

    public static function getTemplate16Settings()
    {
        $settings = [];
        $settings = [
            [
                'label'=>'Loop Speed',
                'type'=>'loop',
                'name'=>'loop_speed',
                'value'=>4,
                'multiply'=>1,
                'min'=>1,
                'max'=>20,
            ],
            [
                'label'=>'Use Customisable Distance Pin?',
                'type'=>'checkbox',
                'name'=>'customisable_pin_yn',
                'value'=>true,
            ],
            [
                'type'=>'upload',
                'name'=>'pin_img',
                'label'=>'Upload New Distance Window Pin',
                'allow_empty'=>true,
                'value'=>"https://static.vic-m.co/templates/pin/template11/default.png",

            ],
            [
                'label'=>'Pin Background Color',
                'type'=>'color',
                'name'=>'distancebackgroundColor',
                'value'=>'#FFFFFF'
            ],
            [
                'label'=>'Pin Top Color',
                'type'=>'color',
                'name'=>'distanceTopColor',
                'value'=>'#759AC6'
            ],
            [
                'label'=>'Pin Bottom Color',
                'type'=>'color',
                'name'=>'distanceBottomColor',
                'value'=>'#075D86'
            ],
            [
                'label'=>'Pin Inner-Circle Color',
                'type'=>'color',
                'name'=>'distanceInnerCircleColor',
                'value'=>'#075D86'
            ],
            [
                'type'=>'text',
                'name'=>'distance_value_text',
                'label'=>'Display Message',
                'allow_empty'=>false,
                'value'=>"%%DISTANCE%%",
            ],
            [
                'type'=>'font',
                'name'=>'fonts_all',
                'label'=>'Google Fonts',
                'value'=>"Open Sans" //Please use google fonts
            ],
            [
                'type'=>'custom_font',
                'name'=>'custom_font',
                'label'=>'Custom Fonts',
                'value'=>''
            ],
            [
                'label'=>'Font Colour',
                'type'=>'color', //uses text box and comes with color picker
                'name'=>'font_color',
                'value'=>'#175582'
            ],
            [
                'label'=>'Font Size',
                'type'=>'font_size',
                'name'=>'font_size',
                'value'=>'13'
            ],
            [
                'label'=>'Font Bold',
                'type'=>'checkbox',
                'name'=>'font_bold',
                'type'=>'checkbox',
                'value'=>false
            ],
            [
                'label'=>'Font Underline',
                'type'=>'checkbox',
                'name'=>'font_underline',
                'type'=>'checkbox',
                'value'=>false
            ],
            [
                'label'=>'Font Italics',
                'type'=>'checkbox',
                'name'=>'font_italics',
                'type'=>'checkbox',
                'value'=>false
            ],
        ];
        return $settings;
    }

    public static function getCubeZooSettings($id)
    {
        $settings = [];

        if (!in_array($id, [20,21,22,24])) {
            $settings[] = [
                    'label'=>'Loop Speed',
                    'type'=>'loop',
                    'name'=>'loop_speed',
                    'value'=>'1200',
                    'multiply'=>100,
                    'min'=>30,
                    'max'=>60,
            ];
        }



        if (in_array($id, [12,13])) {
            $settings[] = [
                    'label'=>'Use Customisable Distance Pin?',
                    'type'=>'checkbox',
                    'name'=>'customisable_pin_yn',
                    'value'=>false
            ];
            $settings[] = [
                    'type'=>'upload',
                    'name'=>'pin_img',
                    'label'=>'Upload New Distance Window Pin',
                    'allow_empty'=>true,
                    'value'=>"https://static.vic-m.co/templates/pin/template$id/default.png"
            ];
            $settings[] = [
                    'label'=>'Pin Background Color',
                    'type'=>'color',
                    'name'=>'distancebackgroundColor',
                    'value'=>'#FFFFFF'
            ];
            $settings[] = [
                    'label'=>'Pin Top Color',
                    'type'=>'color',
                    'name'=>'distanceTopColor',
                    'value'=>'#759AC6'
            ];
            $settings[] = [
                    'label'=>'Pin Bottom Color',
                    'type'=>'color',
                    'name'=>'distanceBottomColor',
                    'value'=>'#075D86'
            ];
            $settings[] = [
                    'label'=>'Pin Inner-Circle Color',
                    'type'=>'color',
                    'name'=>'distanceInnerCircleColor',
                    'value'=>'#075D86'
            ];
            $settings[] = [
                    'label'=>'Pin Width',
                    'type'=>'number',
                    'name'=>'distanceWidth',
                    'value'=>'115'
            ];
            $settings[] = [
                    'label'=>'Pin Height',
                    'type'=>'number',
                    'name'=>'distanceHeight',
                    'value'=>'50'
            ];
            $settings[] = [
                    'label'=>'Pin Top',
                    'type'=>'number',
                    'name'=>'distanceTop',
                    'value'=>'0'
            ];
            $settings[] = [
                    'label'=>'Pin Left',
                    'type'=>'number',
                    'name'=>'distanceLeft',
                    'value'=>'0'
            ];
        } elseif (in_array($id, [21,22,24])) {
        } else {
            $settings[] = [
                    'label'=>'Use Customisable Distance Pin?',
                    'type'=>'checkbox',
                    'name'=>'customisable_pin_yn',
                    'value'=>false
            ];

            $settings[] = [
                    'type'=>'upload',
                    'name'=>'pin_img',
                    'label'=>'Upload New Distance Window Pin',
                    'allow_empty'=>true,
                    'value'=>"https://static.vic-m.co/templates/pin/template11/default.png"
            ];
            $settings[] = [
                    'label'=>'Pin Background Color',
                    'type'=>'color',
                    'name'=>'distancebackgroundColor',
                    'value'=>'#FFFFFF'
            ];
            $settings[] = [
                    'label'=>'Pin Top Color',
                    'type'=>'color',
                    'name'=>'distanceTopColor',
                    'value'=>'#759AC6'
            ];
            $settings[] = [
                    'label'=>'Pin Bottom Color',
                    'type'=>'color',
                    'name'=>'distanceBottomColor',
                    'value'=>'#075D86'
            ];
            $settings[] = [
                    'label'=>'Pin Inner-Circle Color',
                    'type'=>'color',
                    'name'=>'distanceInnerCircleColor',
                    'value'=>'#075D86'
            ];
            $settings[] = [
                    'label'=>'Pin Width',
                    'type'=>'number',
                    'name'=>'distanceWidth',
                    'value'=>'300'
            ];
            $settings[] = [
                    'label'=>'Pin Height',
                    'type'=>'number',
                    'name'=>'distanceHeight',
                    'value'=>'250'
            ];
            $settings[] = [
                    'label'=>'Pin Top',
                    'type'=>'number',
                    'name'=>'distanceTop',
                    'value'=>'0'
            ];
            $settings[] = [
                    'label'=>'Pin Left',
                    'type'=>'number',
                    'name'=>'distanceLeft',
                    'value'=>'0'
            ];
        }

        $settings = array_merge(
            $settings,
            [
                [
                    'type'=>'text',
                    'name'=>'distance_value_text',
                    'label'=>'Display Message',
                    'allow_empty'=>true,
                    'value'=>"%%DISTANCE%%"
                ],
                [
                    'label'=>'Background Color',
                    'type'=>'color', //uses text box and comes with color picker
                    'name'=>'backgroundColor',
                    'value'=>'#ffffff'
                ],
                [
                    'type'=>'font',
                    'name'=>'fonts_all',
                    'label'=>'Google Fonts',
                    'value'=>"Open Sans" //Please use google fonts
                ],
                [
                    'type'=>'custom_font',
                    'name'=>'custom_font',
                    'label'=>'Custom Fonts',
                    'value'=>''
                ],
                [
                    'label'=>'Font Colour',
                    'type'=>'color', //uses text box and comes with color picker
                    'name'=>'font_color',
                    'value'=>'#070275'
                ],
                [
                    'label'=>'Font Size',
                    'type'=>'font_size',
                    'name'=>'font_size',
                    'value'=>'14'
                ],
                [
                    'label'=>'Font Bold',
                    'type'=>'checkbox',
                    'name'=>'font_bold',
                    'type'=>'checkbox',
                    'value'=>false
                ],
                [
                    'label'=>'Font Underline',
                    'type'=>'checkbox',
                    'name'=>'font_underline',
                    'type'=>'checkbox',
                    'value'=>false
                ],
                [
                    'label'=>'Font Italics',
                    'type'=>'checkbox',
                    'name'=>'font_italics',
                    'type'=>'checkbox',
                    'value'=>false
                ],
            ]
        );


        if (in_array($id, [0])) { //remove background setting
            foreach ($settings as $key => $setting) {
                if ($setting["name"] == "backgroundColor") {
                    unset($settings[$key]);
                }
            }
        }

        return $settings;
    }

    public static function getTemplate92Settings()
    {
        $settings[] = [
        'label'=>'',
        'type'=>'hidden',
        'name'=>'fieldsFormsBuilder',
        'value'=>"",
        ];
        $settings[] = [
            'label'=>'',
            'type'=>'hidden',
            'name'=>'pageSettings',
            'value'=>"",
        ];
        $settings[] = [
            'label'=>'',
            'type'=>'hidden',
            'name'=>'globalPageSettings',
            'value'=>"",
        ];
        $settings[] = [
            'label'=>'Banner Studio',
            'type'=>'form_builder',
            'name'=>'form',
            'value'=>"",
        ];
        $settings[] =  [
            'type'=>'text',
            'name'=>'distance_value_text',
            'label'=>'Display Message',
            'allow_empty'=>false,
            'value'=>"%%DISTANCE%%",
        ];
        $settings[] = [
            'label'=>'Use Customisable Distance Pin?',
            'type'=>'checkbox',
            'name'=>'customisable_pin_yn',
            'value'=>false
        ];
        $settings[] = [
            'type'=>'upload',
            'name'=>'pin_img',
            'label'=>'Upload New Distance Window Pin',
            'allow_empty'=>true,
            'value'=>"/templates/pin/template34/default.svg"
        ];
        $settings[] = [
            'label'=>'Pin Background Color',
            'type'=>'color',
            'name'=>'distancebackgroundColor',
            'value'=>'#FFFFFF'
        ];
        $settings[] = [
            'label'=>'Pin Top Color',
            'type'=>'color',
            'name'=>'distanceTopColor',
            'value'=>'#759AC6'
        ];
        $settings[] = [
            'label'=>'Pin Bottom Color',
            'type'=>'color',
            'name'=>'distanceBottomColor',
            'value'=>'#075D86'
        ];
        $settings[] = [
            'label'=>'Pin Inner-Circle Color',
            'type'=>'color',
            'name'=>'distanceInnerCircleColor',
            'value'=>'#075D86'
        ];
        $settings[] = [
            'label'=>'Add to cart',
            'type'=>'select',
            'name'=>'add_to_cart',
            'value'=>'0',
            'options' => [
                '0' => 'No',
                '1' => 'Yes',
            ],
        ];

        $settings[] = [
            'label'=>'Wishlist',
            'type'=>'select',
            'name'=>'wishlist',
            'value'=>'0',
            'options' => [
                '0' => 'No',
                '1' => 'Yes',
            ],
        ];

        $settings[] = [
            'label'=>'Pin Image (Paste svg)',
            'type'=>'upload',
            'name' =>'map_pin_image',
            'value' =>'https://static.vic-m.co/templates/pin/template36/default.svg',
            'allow_empty' => true,
        ];

        $settings[] = [
            'label'=>'Map Theme',
            'type'=>'select',
            'name'=>'map_theme',
            'value'=>'roadmap',
            'options' => [
                'roadmap' => 'Road Map',
                'satellite' => 'Satellite',
                'hybrid' => 'Hybrid',
                'terrain' => 'Terrain',
            ],
        ];

        $settings[] = [
            'label' => 'Map Related Brand',
            'type' => 'select',
            'name' => 'related_brand_1',
            'value' => "",
            'options' => Brand::orderBy('brandName')->pluck('brandName','id')->toArray()
        ];


        $settings[] = [
            'label' => 'Background Colour',
            'type' => 'color',
            'name' => 'background_color',
            'value' => '#faaf40'
        ];

        $settings[] = [
            'label' => 'Border Colour',
            'type' => 'color',
            'name' => 'border_color',
            'value' => '#faaf40'
        ];

        $settings[] = [
            'label' => 'Glyph Colour',
            'type' => 'color',
            'name' => 'glyph_color',
            'value' => '#faaf40'
        ];

        $settings[] = [
            'label' => 'Background Colour Specials' ,
            'type' => 'color',
            'name' => 'background_specials_color',
            'value' => '#faaf40'
        ];

        $settings[] = [
            'label' => 'Border Colour Specials',
            'type' => 'color',
            'name' => 'border_specials_color',
            'value' => '#faaf40'
        ];

        $settings[] = [
            'label' => 'Glyph Colour Specials',
            'type' => 'color',
            'name' => 'glyph_specials_color',
            'value' => '#faaf40'
        ];

        $settings[] = [
            'label' => 'Map Style JSON',
            'name' => 'map_styles',
            'type' => 'textarea',
            'allow_empty' => true,
            'value' => ''
        ];

        $dynamicFields[] = ["id"=>"form","group"=>"form_group","name"=>"Form Fields","style-fields"=>
            [
            "form_group_form_theme", "form_group_form_background_color", "form_group_form_color",
            "form_group_form_border_color", "form_group_form_border_radius",
            "form_group_font","form_group_font_color","form_group_font_size","form_group_font_bold","form_group_font_underline","form_group_font_italics",
            "form_group_label_hidden",/*"form_group_label_position",*/
            ]
        ];


        $dynamicFields =  array_merge($dynamicFields, self::dynamicFormFields());

        foreach($dynamicFields as $group){
            foreach($group['style-fields'] as $style_field){
                $field = trim(str_replace([$group['group'].'_'], [''], $style_field));
                $label = trim(ucwords(str_replace(['_'], [' '], $field)));
                $settings[] = array_merge([
                    'type'=>'text-field',
                    'name'=> $style_field,
                    'label'=> $label,
                    'value'=>""
                ],self::getFieldTypeDefaults($field));
            }
        }


        return $settings;
    }


    public static function getTemplate144Settings()
    {
        $settings= [
            [
                'label' => 'Use Customisable Distance Pin?',
                'type' => 'checkbox',
                'name' => 'customisable_pin_yn',
                'value' => true
            ],
            [
                'type' => 'upload',
                'name' => 'pin_img',
                'label' => 'Upload New Distance Window Pin',
                'allow_empty' => true,
                'value' => "https://static.vic-m.co/templates/pin/template36/default.svg", //REVIEW: which default svg can we use
            ],
            [
                'label' => 'Pin Background Color',
                'type' => 'color',
                'name' => 'distancebackgroundColor',
                'value' => '#FFFFFF'
            ],
            [
                'label' => 'Pin Top Color',
                'type' => 'color',
                'name' => 'distanceTopColor',
                'value' => '#759AC6'
            ],
            [
                'label' => 'Pin Bottom Color',
                'type' => 'color',
                'name' => 'distanceBottomColor',
                'value' => '#075D86'
            ],
            [
                'label' => 'Pin Inner-Circle Color',
                'type' => 'color',
                'name' => 'distanceInnerCircleColor',
                'value' => '#075D86'
            ],
            [
                'label'=>'Pin Background Opacity',
                'name'=>'Opacity',
                'type'=>'select',
                'value'=>0.1,
                'options'=>[
                    '1'=>"100% Opacity",
                    '0.8'=>"80% Opacity",
                    '0.5'=>"50% Opacity",
                    '0'=>"Transparent",
                ],
            ],
            [
                'type' => 'font',
                'name' => 'fonts_all',
                'label' => 'Google Fonts',
                'value' => "Open Sans"
            ],
            [
                'type' => 'custom_font',
                'name' => 'custom_font',
                'label' => 'Custom Fonts',
                'value' => ''
            ],
            [
                'label' => 'Font Colour',
                'type' => 'color',
                'name' => 'font_color',
                'value' => '#070275'
            ],
            [
                'label' => 'Font Size',
                'type' => 'font_size',
                'name' => 'font_size',
                'value' => '13'
            ],
            [
                'label' => 'Font Bold',
                'type' => 'checkbox',
                'name' => 'font_bold',
                'type' => 'checkbox',
                'value' => false
            ],
            [
                'label' => 'Font Underline',
                'type' => 'checkbox',
                'name' => 'font_underline',
                'type' => 'checkbox',
                'value' => false
            ],
            [
                'label' => 'Font Italics',
                'type' => 'checkbox',
                'name' => 'font_italics',
                'type' => 'checkbox',
                'value' => false
            ],
            [
                'type' => 'text',
                'name' => 'distance_value_text',
                'label' => 'Display Message',
                'allow_empty' => true,
                'value' => "%%DISTANCE%%"
            ],
        ];

        return $settings;
    }

    public static function makeDynamicPins($inputs, $templateId, $banner, $campaignId, $bannerId, $action)
    {
        // Nice Work Devin :)
        $carouse_settings = [];
        foreach ($inputs as $key => $value) {
            if (strpos($key, '~') !== false) {
                $extractedId = explode("~", $key);
                $carouse_settings[$extractedId[1]][$extractedId[0]] = $value;
                unset($inputs[$key]);
            }
        }

        $implodedCarouselSettings = [];
        foreach ($carouse_settings as $key => $settings) {
            if (TemplatePinSettings::DEFAULT_STATIC_TEMPLATE != $templateId) {
                if ($banner->height == 250) {
                    $settings['y'] = str_replace('px', '', $settings['distanceHeight']);
                    $settings['distanceHeight'] = '250px';
                }
            }
            $pinImageName = $action . $campaignId . '.' . $bannerId . '.' . md5($key . $action . $bannerId . $campaignId . $templateId . microtime(true) . rand()) . '.svg';

            $settings["width"] = $banner->width;
            $settings["height"] = $banner->height;

            $carouse_settings[$key]['path'] = $value = TemplatePinSettings::uploadCustomisablePin($pinImageName, $settings, $templateId);
            foreach ($settings as $key2 => $setting) {
                if ($key2 == 'path') {
                    $inputs[$key2."~". $key] = $value;
                    continue;
                }
                $inputs[$key2."~". $key] = $setting;
            }
        }
        return $carouse_settings;
    }

    public static function saveImages(&$inputs, $templateId, $banner, $campaignId, $bannerId, $action)
    {
        $imagePaths = [];

        if(isset($inputs['fieldsFormsBuilder'])){
            $fields = json_decode($inputs['fieldsFormsBuilder']);
            if($fields){
                foreach($fields as &$value){
                    if($value->type == 'image'){
                        if(!isset($value->src) || empty(trim($value->src))) continue;
                        if(in_array("template$templateId",explode("/",$value->src))) continue;

                        list(, $ext) = explode('/', str_replace(['x-','+xml'],'',mime_content_type($value->src)));
                        $img_file = "/templates/pin/template".$templateId."/img/".rand(1000,999999).uniqid().".$ext";

                        $data = $value->src;

                        list($type, $data) = explode(';', $data);
                        list(, $data)      = explode(',', $data);
                        $data = base64_decode($data);

                        $path = public_path($img_file);

                        $dir = public_path("/templates/pin/template".$templateId."/img/");
                        if(!File::exists($dir)) {
                            File::makeDirectory($dir, 0755, true, true);
                        }

                        file_put_contents($path, $data);

                        $value->src = $img_file;
                    }
                }
                $inputs['fieldsFormsBuilder'] = json_encode($fields);
            }

        }else{
            if (!isset($inputs["caurousel_images"])) {
                return $imagePaths;
            }
            if (!count($inputs["caurousel_images"])) {
                return $imagePaths;
            }

            if (is_array($inputs["caurousel_images"])) {
                foreach ($inputs["caurousel_images"] as $key1 => $file) {
                    $pinImageFile = $file;
                    $pinImageName = $action.$campaignId.'.'.$bannerId. '/carousel/'.$action.md5($key1.$bannerId.$campaignId.$templateId.microtime(true).rand()).'.'.$pinImageFile->getClientOriginalExtension();


                    $id = "container-".$file->getSize();

                    $file = $pinImageFile;
                    if (env('STORAGE_DRIVER') == 's3') {
                        $disk = 's3';
                        $filePath = "/templates/pin/template".$templateId."/carousel/".$pinImageName;
                        $imagePaths[] = ["path"=>url('https://static.vic-m.co/templates/pin/template'.$templateId."/carousel/".$pinImageName),"id"=>$id ];
                    } else {
                        $disk = 'local';
                        $filePath = "/public/templates/pin/template" . $templateId . "/carousel/" . $pinImageName;
                        $imagePaths[] = ["path"=>url('storage/templates/pin/template'.$templateId."/carousel/".$pinImageName),"id"=>$id ];
                    }
                    \Storage::disk($disk)->put($filePath, file_get_contents($file));
                }
            }
        }

        return $imagePaths;
    }

    public final static function testWidget($templateId)
    {
         $templateIds = TemplatePinSettings::WIDGET_IDS;
         if(in_array($templateId,$templateIds)){
           return true;
         } else {
           return false;
         }
    }

    public final static function formBuilderSettings(&$settings)
    {
        //Page Settings
        if(!isset($settings->pageSettings) || empty($settings->pageSettings)){
            //TODO: Get these from somewhere else?
            $settings->pageSettings = [
                [
                    'id' => 0, //(int)microtime(true)*1000,
                    'name'=>'New Page',
                    'deleted_at'=>false,
                    'page-settings'=>[
                        'transition' => [
                            'display_times' => 1,
                        ]
                    ]
                ]
            ];
            $settings->pageSettings = json_decode(json_encode($settings->pageSettings));
        } else {
            $settings->pageSettings = json_decode($settings->pageSettings);
        }

        //Page Gloabal Settings
        //TODO: Get these from somewhere else?
        $defaultSettingsForGlobal = [
            'submit' => [
                0 => [
                    'id' => 'none', //none or e.g #item-0
                    'page' => $settings->pageSettings[0]->id,
                    'event' => 'click', //click|submit?|change?|focus?
                    'validate' => true //wether or not to run validation (based on HTML5)
                ]
            ],
            'transition'=> [
                'name' => 'carousel',
                'trigger' => 'auto', //auto|user|input
                'input'=> [
                    0 => [
                        'id' => 'none', //none || e.g #item-0
                        'action' => 'next', //next|prev
                        'event' => 'click', //click|submit?|change?|focus?
                        'page' => $settings->pageSettings[0]->id,
                        'validate' => true //wether or not to run validation (based on HTML5)
                    ]
                ], //buttons that need to trigger transition
                'carousel' => [
                    //settings for carousel
                    'loop_speed' => 10,
                    'nav_style' => 'chevron',
                    'nav_color' => '#869791',
                    'nav_size' => 40,
                    'dots' => true,
                    'dots_color' => '#869791',
                ],
                'multi-slides' => [
                    'loop_speed' => 1
                    //settings for multi-slides
                ],
                'multi-slices' => [
                    'loop_speed' => 1
                    //settings for multi-slices
                ],
                'flip-over' => [
                    'loop_speed' => 1
                    //settings for flip-over
                ],
                'fade-in' => [
                    'loop_speed' => 1
                ],
                'tile-transition' => [
                    'loop_speed' => 1
                    //settings for tile-transition
                ],
                'animate' => [
                    'loop_speed' => 1,
                    'delay' => 1,
                    'animateIn' => 'fadeIn',
                    'animateOut' => 'fadeOut'
                ]
            ]
        ];

        if(!isset($settings->globalPageSettings) || empty($settings->globalPageSettings)){
            $settings->globalPageSettings = json_decode(json_encode($defaultSettingsForGlobal));
        }else{
            $settings->globalPageSettings = json_decode($settings->globalPageSettings);

            //page count must be the same as  transition input count
            if(count($settings->pageSettings) != count($settings->globalPageSettings->transition->input)){
                foreach($settings->pageSettings as $key => $page){
                    $settings->globalPageSettings->transition->input[$key] =  [
                        'id' => 'none',
                        'action' => 'next',
                        'event' => 'click',
                        'page' => $page->id,
                        'validate' => true //wether or not to run validation (based on HTML5)
                    ];
                }
            }
        }

        //Field Settings
        if(!isset($settings->fieldsFormsBuilder) || empty($settings->fieldsFormsBuilder)){
            $settings->fieldsFormsBuilder = [];
        } else {
            $fieldsFormsBuilder = json_decode($settings->fieldsFormsBuilder);
            $settings->optionIds = [];
            if(!empty($fieldsFormsBuilder)){
                foreach($fieldsFormsBuilder as $value){
                    if(in_array($value->type,["select", "radio", "checkbox"])){
                        foreach($value->options as $k => $item){
                            //if item is empty, use the key/index value instead
                            $v = empty(trim($item)) ? $k : $item;
                            $settings->optionIds[$value->id][$v] = $value->id .'_'. str_replace([' '],['_'],$v);
                        }
                    }
                }
            }
            $settings->fieldsFormsBuilder = $fieldsFormsBuilder;
        }

        $settings->optionsDelimeter = "####XYZ";
    }

    public final static function testFormBuilder($templateId)
    {
        return in_array($templateId,[92,130,131,132]);
    }

    public final static function processCustomIcons($settings, $params)
    {
        if (!isset($settings->fieldsFormsBuilder)) {
            return $settings;
        }

        # Please don't ask why lol
        $rawFormFields = json_decode(json_encode($settings->fieldsFormsBuilder),true);

        if (!isset($rawFormFields)) {
            return $settings;
        }

        $customIconFields = array_filter($rawFormFields, function($field){
            return $field['type'] == 'custom';
        });

        if (!count($customIconFields)) {
            return $settings;
        }

        $iconsArray['icons'] = [];

        foreach($customIconFields as $key => $field){
            if(isset($field['buttonLink'])){

                //TODO: Bug with switching button types and switching and subsequently not saving
                if($field['buttonType']=='calevent' && !isset($field['eventTitle'])){
                    $defaultCustomValues = collect(self::dynamicFormFields())->firstWhere('id', 'custom');
                    $field = $defaultCustomValues['defaults']['calevent'];
                }

                //Parse default links
                $iconsArray['icons'][$key] = ['link' => $field['buttonLink'], 'id' => $field['buttonType']];

                //Parse buttonLink for undetected device OS (uses Web URL)
                if($field['buttonType'] == 'download'){
                    $iconsArray['icons'][$key] = ['link' => $field['buttonLink'], 'id' => 'web'];
                }

                //Parse calevent data
                if($field['buttonType']=='calevent'){
                    $iconsArray['icons'][$key]['title'] = $field['eventTitle'];
                    $iconsArray['icons'][$key]['startDate'] = $field['eventStart'];
                    $iconsArray['icons'][$key]['endDate'] = $field['eventEnd'];
                    $iconsArray['icons'][$key]['details'] = $field['eventDetails'];
                    $iconsArray['icons'][$key]['allday'] = $field['eventAllDay'] ?? false;
                }
            }
        }

        if(empty($iconsArray['icons'])){
            return $settings;
        }

        LandingSettings::replacePlaceholders($iconsArray, $params);

        foreach($iconsArray['icons'] as $key => $icon){
            $rawFormFields[$key]['buttonLink'] = $icon['link'];
            if(isset($icon['calendars'])){
                $rawFormFields[$key]['calendars'] = $icon['calendars'];
            }
        }

        $settings->fieldsFormsBuilder = json_decode(json_encode($rawFormFields));

        return $settings;
    }

    public static final function listingAds(){
        $listingAds = [
            5 => '4d530125504028b87c4e294a9fc691d8'  ,// Near Me Listing - Pharmacy
            1 => 'fb9131245a403ca3419579e255676ea6'  ,// Near Me Listing - Fast food
            8 => 'b348a8295f434d8fe70db55c5bf4fe6e'  ,// Near Me Listing - ATM
            4 => 'f1b818265c4457e67af1003c131a2695'  ,// Near Me Listing - restaurants
            11 => 'eebb142750476613c7a0154aee8e2e96',// Near Me Listing - Coffee shop
            3 => '0d2a1b295d417d7842b5bd5af461d516'  ,// Near Me Listing - Grocery
            6 => 'b70e40205b4a820fcfe766804ad183a6'  ,// Near Me Listing - Hardware
            10 => '008249225d4c9e6500df2e4538d4c56f' ,// Near Me Listing - Fashion
            9 => '4c3a66265b5c05d3004092ec188733c6'  ,// Near Me Listing - Fuel
            12 => '5c024b2359571e083058ce16c3a0470d'  ,// Near Me Listing - Car dealer
            13 => '9191ab2b525126708e856b4fbfde1f36'  ,// Near Me Listing - Gym
            37 => '067a9a29555f3118fe7039df12f230d8'  ,// Near Me Listing - Bottle store
            12 => '1c3f5b2f7a64347ce07403f752446abd'  ,// Near Me Listing - Car Delears
            20 => '3c4d103c190e5bdbe82afda000cb639c', //Near Me Listing - Service Centers
            21 => '6f52453f1a0d6509bb7cc587a432f9d5', // Near Me Listing - Fitment Center
            119 => '0be65338190272e90f4c8d61de36bb86', // Near Me Listing - Kids Activities
            15 => '35760248571912eedd94f70d89fb6add', //Near Me Listing - Bars
            16 => 'd7c1a74c5d0c8eb090c2fa15995e4eb5', //Near Me Listing - Beauty
            59 => 'bd28d142580d958eb63de05b0e4b40b4', //Near Me Listing - Electronic
            18 => 'cb7a554d54120e74eef0d40ffbda9d10', //Near Me Listing - Night Life
            101 => '95eeb84b5219372096afc3eed4dc8916', //Near Me Listing - Mobile Operator
            123 => '4f1dd749592f1227f64e559a7f583de1', //Near Me Listing - Tourist Attractions
            120 => "9be6854d541728c4838cf447f467ac49", //Near Me Listing - Events
            56 => "5a2d734c6d5e3cd5256e020f414484fa", //Near Me Listing - Convenience Store

        ];
        return $listingAds;
    }


    public final static function widgetSettings($settings, $locs,$isPreview,$campaignId,$geoip,$geoip_loc,$zoneId,$brandLocationId){
        $locs = explode(",",CookieController::getCookieForZone($locs));
        $typeLoc = $geoip;
        $values = [];
        $settings->zamato_trending = [];

        $cityId = $_GET['city_id'] ?? '';

        //REVIEW: Sometimes the $settings->enableCategories is empty and then we have an issue so which categories should we pull??
        $enabledCategories = is_array($settings->enableCategories) ? $settings->enableCategories :  explode(",", $settings->enableCategories);

        $enabledSpecialCategories = array_intersect($enabledCategories, WidgetCategories::$specialCategories);
        // $enabledWidgetCategories = WidgetCategories::whereIn('id', $enabledCategories)->with('serving_times')->get();

        $saveData = [
            'default'=>[
                'set_group'=>uniqid(),
                'campaignId'=>$campaignId,
                'zoneId'=>$zoneId,
                'date'=>date("Y-m-d"),
                'location_type'=>$geoip,
                'created_at'=>date("Y-m-d H:i:s"),
            ],
            'saveData'=>[]
        ];

        if($isPreview){

            foreach($enabledCategories as $categoryId){
                if(in_array($categoryId, WidgetCategories::$specialCategories)) continue;
                $values[] = [
                    "brandLocationId"=>0,
                    "distance"=>rand(10,999)."m",
                    "category_id"=>$categoryId,
                ];
            }

            $cityids = [64,11060,78,172,409,65,75,171,10657,11058];
            $cityId = $cityids[array_rand($cityids,1)];
        }else{
            foreach($enabledWidgetCategories as $enabledCategory){
                if(!$enabledCategory->shouldBeServed($enabledCategory->serving_times)){
                    $categIndex = array_search($enabledCategory->id, $enabledCategories);
                    unset($enabledCategories[$categIndex]);
                }
            }

                $values = array_map(function($value) use($settings,$geoip,$geoip_loc,$values,&$saveData, $enabledCategories){
                    $values = explode("vv",$value);
                    $distance = $values[1] ?? 1;
                    if(BannerCreative::formatDistance($values[1]) == "m"){
                        $distance = ($distance * 1000)."m";
                    }else{
                        $distance = $distance."km";
                    }


                    $rowData = $saveData['default'];
                    $rowData['category_id'] = $values[2];
                    $rowData['locationId'] = $values[0];
                    $saveData['saveData'][] = $rowData;

                    if(in_array($geoip,$geoip_loc)){
                        $distance = 0;
                    }

                    if(!in_array($values[2], array_values($enabledCategories))) return false;
                    if(in_array($values[2], WidgetCategories::$specialCategories)) return false;

                    return [
                        "brandLocationId"=>$values[0],
                        "distance"=>$distance,
                        "category_id"=>$values[2]
                    ];
                },$locs);

                if( (isset($_GET['_loctype']) && !in_array($_GET['_loctype'],['shared','device','fine'])) ){

                    $locations= Cache::remember('widget_categories_geoip'.$campaignId, 60, function () use($campaignId){
                        return \App\WidgetCategories::getCampaignLinkedCategories($campaignId);
                    });
                    $values = [];
                    foreach($enabledCategories as $temp){
                        $values[] = [
                            "brandLocationId"=>$brandLocationId,
                            "distance"=>"",
                            "category_id"=>$temp,
                        ];
                    }
                }
        }

        //Check if weather is selected
        if(in_array("999999", array_values($enabledSpecialCategories))){
            $weatherConditions = WidgetCategories::WEATHER_MAPPING;

            $distance = "";
            $currenthour = date("H");

            if(!$isPreview){ // not preview


                if(in_array($geoip,$geoip_loc)){
                    $weatherData= Cache::remember('nearme_weather_preview', self::$nearmeWeatherCachePeriod,function () {
                        $lat = -34.0070329;
                        $lon = 18.4596472;
                        return WidgetController::getWeatherDetails($lat,$lon);
                    });

                    $icon= 'sun';

                    $description = $settings->widgetLanguage == 'Afrikaans' ? 'Deel asseblief u ligging' : 'Please share your location';
                    $iconUrl = $currenthour < 18 ? "/templates/pin/template91/sun.svg" : "/templates/pin/template91/night/sun.svg";
                }else{
                    $latLon = isset($_GET['_ul']) ? $_GET['_ul'] : $_GET['_bl'];
                    $latLon = explode(",", $latLon);

                    $weatherData = WidgetController::getWeatherDetails($latLon[0],$latLon[1]);

                    $icon = $weatherConditions[$weatherData['current']->weather[0]->icon]['vicinity_icon'];

                    $distance = $weatherData['current']->temp - 273;
                    $description = $weatherData['current']->weather[0]->description;
                    $iconUrl = $currenthour < 18 ?  "/templates/pin/template91/".$icon.".svg" : "/templates/pin/template91/night/".$icon.".svg";
                }

            }else{ //preview

                $weatherData= Cache::remember('nearme_weather_preview', self::$nearmeWeatherCachePeriod,function () {
                    $lat = -34.0070329;
                    $lon = 18.4596472;
                    return WidgetController::getWeatherDetails($lat,$lon);
                });


                $icon = $weatherConditions[array_keys($weatherConditions)[rand(0,8)]];
                $description = $icon['description'];
                $icon = $icon['vicinity_icon'];

                $iconUrl = $currenthour < 18 ? "/templates/pin/template91/".$icon.".svg" : "/templates/pin/template91/night/".$icon.".svg";

                $distance = $weatherData['current']->temp - 273;
                $weatherData['name'] = rand(0,1) ? 'Wynberg' : 'Parkwood';
            }

            $nolocation = ($settings->widgetLanguage == 'Afrikaans' || $zoneId == 2343) ?  'My ligging' : 'My Location';

            $city = $weatherData['name'] ?? $nolocation;

            //Build forecast here;
            $forecast = [];
            if(!empty($weatherData['daily'])){
                foreach($weatherData['daily'] as $dailyForecast){
                    $iconCode = WidgetCategories::WEATHER_MAPPING[$dailyForecast->weather[0]->icon]['vicinity_icon'];

                    if(date('Y-m-d') ==  date('Y-m-d', $dailyForecast->dt)){
                        $forecast['current'] = [
                            'id' => $dailyForecast->weather[0]->id,
                            'description' => $dailyForecast->weather[0]->description,
                        ];
                        continue;
                    } //Skip today

                    //Icon URL
                    $iconCode = WidgetCategories::WEATHER_MAPPING[$dailyForecast->weather[0]->icon]['vicinity_icon'];
                    $customIconUrl = $currenthour < 18 ? "/templates/pin/template91/dropdown/$iconCode.svg" : "/templates/pin/template91/dropdown/night/$iconCode.svg";
                    // REVIEW: should the current time have an effect on forecast and icons?

                    $forecast[] = [
                        'day' => date('D', $dailyForecast->dt),
                        'temp' => [
                            'min' => round($dailyForecast->temp->min -273),
                            'max' => round($dailyForecast->temp->max -273),
                            'day' => round($dailyForecast->temp->day -273),
                            'morn' => round($dailyForecast->temp->morn -273),
                            'night' => round($dailyForecast->temp->night -273),
                            'eve' => round($dailyForecast->temp->eve -273),
                        ],
                        'feelsLike' => [
                            'day' => round($dailyForecast->feels_like->day -273),
                            'morn' => round($dailyForecast->feels_like->morn -273),
                            'night' => round($dailyForecast->feels_like->night -273),
                            'eve' => round($dailyForecast->feels_like->eve -273),
                        ],
                        'weather'=>[
                            'id' => $dailyForecast->weather[0]->id,
                            'mainText' => $dailyForecast->weather[0]->main,
                            'description' => $dailyForecast->weather[0]->description,
                            'iconCode' => $dailyForecast->weather[0]->icon,
                        ],
                        'iconUrl' => $customIconUrl,
                    ];
                }
            }

            $weatherIcon = [
                "brandLocationId"=>0,
                "distance"=>round($distance),
                "category_id"=>'999999',
                "weatherSettings"=>$weatherData,
                "weatherForecast" => $forecast,
                "icon"=>$iconUrl,
                "description"=>$description,
                "iconCode"=>$icon,
                "city"=>$city,
                "widthClass" => isset($settings->widthClass) && $settings->widthClass == 'fullWidth' ?  'fullWidth' : null,
            ];

            if($settings->weatherPosition == "first"){
                array_unshift($values,$weatherIcon);
            }
            if($settings->weatherPosition == "last"){
                $values[] = $weatherIcon;
            }
            if($settings->weatherPosition == "middle"){
                array_splice( $values, floor(count($values)) / 2, 0, [$weatherIcon] );
            }
            if($settings->weatherPosition == "random"){
                array_splice( $values, array_rand($values,1), 0, [$weatherIcon] );
            }
        }

        //Check if zomato is selected
        if(in_array("42", array_values($enabledSpecialCategories))){
            $icon = [
                "brandLocationId"=>0,
                "distance"=>"",
                "category_id"=>'42',
            ];
            array_splice( $values, array_rand($values,1), 0, [$icon] );

            $settings->zamato_trending = \App\WidgetCategories::getZomatoRestaurants($isPreview, $cityId);
        }

        // check if daily deals is selected
        if(in_array("121", array_values($enabledSpecialCategories))){
            $distance = "";
            $iconCode = 'dailydeals';
            $iconUrl = "/templates/pin/template91/dailydeals.gif";

            $dailyDealsIcon = [
                "brandLocationId"=>0,
                 "distance"=>0,
                "category_id"=> 121,
                "icon"=> $iconUrl,
                "description"=>"Daily Deals Api",
                "iconCode"=> $iconCode,
            ];

            if(!$isPreview) {
                $latLon = isset($_GET['_ul']) ? $_GET['_ul'] : $_GET['_bl'];
                $latLon = explode(",", $latLon);
                $dailyDealsIcon['lat'] = $latLon[0];
                $dailyDealsIcon['lon'] = $latLon[1];
            }

            array_push($values, $dailyDealsIcon);
        }
        if(in_array("96", array_values($enabledSpecialCategories))){
            $distance = 0;

            $propertyIcon = [
                "brandLocationId"=>0,
                "distance"=>$distance,
                "category_id"=> 96,
                "description"=>"Property",

            ];

            if(!$isPreview) {
                $latLon = isset($_GET['_ul']) ? $_GET['_ul'] : $_GET['_bl'];
                $latLon = explode(",", $latLon);
                $propertyIcon['lat'] = $latLon[0];
                $propertyIcon['lon'] = $latLon[1];
            }


            array_push($values, $propertyIcon);
        }

        //Check if traffic is selected and enable it
        if(in_array("118", array_values($enabledSpecialCategories))){
            $distance = "";
            $iconCode = 'traffic';
            $iconUrl = "/templates/pin/template91/traffic.svg";

            $wazeIcon = [
                "brandLocationId"=>0,
                "distance"=>0,
                "category_id"=>'118',
                "icon"=> $iconUrl,
                "description"=>"",
                "iconCode"=> $iconCode,
            ];

            if(!$isPreview) {
                $latLon = isset($_GET['_ul']) ? $_GET['_ul'] : $_GET['_bl'];
                $latLon = explode(",", $latLon);
                $wazeIcon['lat'] = $latLon[0];
                $wazeIcon['lon'] = $latLon[1];
            }

            array_push($values, $wazeIcon);
        }

        if(!$isPreview){

            for($i = 0;$i < count($saveData['saveData']);$i++){
                $saveData['saveData'][$i]['position'] = $i;
                $saveData['saveData'][$i]['position']++;
            }
            $insertData = [];
            $counterPos = 1;
            foreach ($values as $key => $value) {
                $item = $saveData['saveData'][0];
                $item['category_id'] = $value['category_id'];
                $item['locationId'] = $value['brandLocationId'];
                $item['position'] =$counterPos++;
                $insertData[] = $item;
            }
            if( isset($_GET['_loctype']) && in_array($_GET['_loctype'],['shared','device','fine'])){
                try {
                    DB::table('widget_location_tracker_current_'.date('Y_m_d'))->insert($insertData);
                } catch (\Exception $e) {

                }

            }
        }

        if(!$isPreview && isset($_GET['slots']) && !empty($_GET['slots'])){
            $data = explode("%vv%",CookieController::getCookieForZone($_GET['slots']));
            if(count($data) > 3 && !empty($data[3])){
                $distance = $data[1] ?? 1;
                if(BannerCreative::formatDistance($data[1]) == "m"){
                    $distance = ($distance * 1000)."m";
                }else{
                    $distance = $distance."km";
                }
                $banner = "https://static.vic-m.co/banners/";
                $banner .= str_replace("0banner","",$data[4]);

                $tag = "https://ad2.vic-m.co/adserver/delivery/track.php";
                $tag .= "?cb=".rand(100,99999);
                $tag .= "&type=impression";
                $tag .= "&adserver=1";
                $tag .= "&zoneId=".$data[2];
                $tag .= "&vicinityTag=".$_GET['_vicmid'] ?? '';
                $tag .= "&campaignId=".$data[3];
                $tag .= "&locationId=".$data[0];
                $tag .= "&loc_type=".$_GET['_loctype'] ?? 'geoip';

                $icon = [
                    "brandLocationId" => $data[0],
                    "distance" => $distance,
                    "category_id" => "ad",
                    "type" => "impression",
                    "track" => $tag,
                    "adserver" => "1",
                    "zoneId" => $data[2],
                    "vicinityTag" => $_GET['_vicmid'] ?? '',
                    "campaignId" => $data[3],
                    "locationId" => $data[0],
                    "loc_type" =>  $_GET['_loctype'] ?? 'geoip',
                    "banner"=> $banner
                ];
                array_splice( $values, 3, 0, [$icon] );
            }
        }

        /** Day Vs Night Category Feature Update */
        $currentHour = (int) date('H');

        $dayTimeParting = [
            'day'=>[
                'icon_ids'=>[3,6,10,11,12,16,17,20,21],
                'hours'=>[6,7,8,9,10,11,12,13,14,15,16,17],
            ],
            'night'=>[
                'icon_ids'=>[15,18],
                'hours'=>[18,19,20,21,22,23,0,1,2,3,4,5],
            ],
        ];

        // CALLBACK: UNSETS icons that need to be removed from $values for DAY/NIGHT
        $removeIcons = function($period) use ($dayTimeParting, $values)
        {
            foreach($dayTimeParting[$period]['icon_ids'] as $icons) {
                $idx = array_search($icons, array_column($values, 'category_id'));

                // Take care when array_search finds nothing returns false (interpreted as 0)
                if (! $idx || $idx == false) {
                    continue;
                }

                unset($values[$idx], $idx, $icons);

                 // Array should be re-indexed in the loop here.
                $values = array_values($values);
            }

            // Remove an empty/null array elements [array_filter without callback]
            // and re-index array sequentially, i.e. 0, 1, 2,... [array_values without callback]
            return array_values(array_filter($values));
        };

        // SHOW day [6AM - 5PM) icons.  DAY: remove night icons
        if (in_array($currentHour, $dayTimeParting['day']['hours'])) {
            $values = $removeIcons('night');
        // SHOW night [6PM - 5AM) icons. NIGHT: remove day icons
        } else if (in_array($currentHour, $dayTimeParting['night']['hours'])) {
            $values = $removeIcons('day');
        }

        $settings->widget_strip_settings = array_filter($values);
        $settings->locsType = $typeLoc;

        return $settings;
    }

    final public static function caurouselImages($settings, $banner = null)
    {
        if (!isset($settings->carousel_images)) {
            return $settings;
        }

        if (is_string($settings->carousel_images) && strtolower(substr($settings->carousel_images, strlen($settings->carousel_images) - 3)) == "svg") {
            if ($banner) {
                if ($settings->carousel_images == "/templates/pin/template42/Svg.svg") {
                    $path = "/templates/default-pins/".$banner->width."x".$banner->height.".png";
                    $make[] = ["path"=>$path,"id"=>"default-image-".rand(1000, 9990)];
                }
                $make[] = ["path"=>$banner->imageURL,"id"=>"default-image-".rand(1000, 9990)];
            } else {
                $make[] = ["path"=>$settings->carousel_images,"id"=>"default-image-".rand(1000, 9990)];
            }


            $settings->carousel_images = json_encode($make);
        }

        if (is_array($settings->carousel_images)) {
            $settings->carousel_images = json_encode($settings->carousel_images);
        }

        $settings->carousel_images = json_decode($settings->carousel_images);
        return $settings;
    }

    final public static function mergeWithDefaultSettings($templateId, $settings)
    {
        $defaultSettings = TemplatePinSettings::getDefaultSettings($templateId, ['format'=>'obj']);
        $defaultNames = [];

        foreach ($defaultSettings as $key => $value) {
            $found = false;
            if (isset($settings->{$value->field}) && !empty($settings->{$value->field})) {
                $found = true;
            } else {
                if (isset($settings->{$value->field}) && isset($value->settings['allow_empty']) && $value->settings['allow_empty']) {
                    $found = true;
                }
            }
            if (!$found) {
                $settings->{$value->field} = $value->value;
            }

            //if templateId == 92, $value field page-settings
            //deserialise page global settings
            //check against default settings
            //for each page settings check
        }


        return $settings;
    }

    final public static function getDefaultSettings($templateId = null, $options = [])
    {
        // $settings = [];
        $settings = [
            1 => TemplatePinSettings::getTemplate1Settings(),
            2 => TemplatePinSettings::getTemplate2Settings(),
            5 => TemplatePinSettings::getTemplate5Settings(),
            6 => TemplatePinSettings::getTemplate6Settings(),
            7 => TemplatePinSettings::getTemplate7Settings(),
            8 => TemplatePinSettings::getTemplate8Settings(),
            9 => TemplatePinSettings::getTemplate9Settings(),
            10 => TemplatePinSettings::getTemplate10Settings(),
            11 => TemplatePinSettings::getTemplate11Settings(),
            12 => TemplatePinSettings::getTemplate12Settings(),
            13 => TemplatePinSettings::getTemplate13Settings(),
            14 => TemplatePinSettings::getTemplate14Settings(),
            15 => TemplatePinSettings::getTemplate15Settings(),
            16 => TemplatePinSettings::getTemplate16Settings(),
            17 => TemplatePinSettings::getTemplate17Settings(),
            18 => TemplatePinSettings::getCubeZooSettings(18),
            19 => TemplatePinSettings::getCubeZooSettings(19),
            20 => TemplatePinSettings::getCubeZooSettings(20),
            21 => TemplatePinSettings::getCubeZooSettings(21),
            22 => TemplatePinSettings::getCubeZooSettings(22),
            23 => TemplatePinSettings::getCubeZooSettings(23),
            24 => TemplatePinSettings::getCubeZooSettings(24),
            25 => TemplatePinSettings::getTemplate25Settings(), //25
            26 => TemplatePinSettings::getTemplate25Settings(), //25
            27 => TemplatePinSettings::getTemplate27Settings(),
            28 => TemplatePinSettings::getTemplate28Settings(),
            29 => TemplatePinSettings::getTemplate29Settings(),
            30 => TemplatePinSettings::getTemplate30Settings(),
            31 => TemplatePinSettings::getTemplate31Settings(),
            32 => TemplatePinSettings::getTemplate32Settings(),
            33 => TemplatePinSettings::getTemplate33Settings(),
            34 => TemplatePinSettings::getTemplate34Settings(),
            35 => TemplatePinSettings::getTemplate35Settings(),
            36 => TemplatePinSettings::getTemplate36Settings(),
            37 => TemplatePinSettings::getTemplate37Settings(),
            38 => TemplatePinSettings::getTemplate38Settings(),
            39 => TemplatePinSettings::getTemplate39Settings(),
            40 => TemplatePinSettings::getTemplate16Settings(),
            41 => TemplatePinSettings::getTemplate41Settings(),
            42 => TemplatePinSettings::getTemplate42Settings(),
            43 => TemplatePinSettings::getTemplate43Settings(),
            44 => TemplatePinSettings::getTemplate44Settings(),
            48 => TemplatePinSettings::getTemplate48Settings(),
            49 => TemplatePinSettings::getTemplate49Settings(),
            50 => TemplatePinSettings::getTemplate49Settings(),
            51 => TemplatePinSettings::getTemplate51Settings(),
            52 => TemplatePinSettings::getTemplate51Settings(),
            53 => TemplatePinSettings::getTemplate53Settings(),
            54 => TemplatePinSettings::getTemplate54Settings(),
            55 => TemplatePinSettings::getTemplate55Settings(),
            56 => TemplatePinSettings::getTemplate55Settings(56),
            57 => TemplatePinSettings::getTemplate57Settings(),
            58 => TemplatePinSettings::getTemplate58Settings(),
            59 => TemplatePinSettings::getTemplate59Settings(),
            60 => TemplatePinSettings::getTemplate53Settings(60),
            62 => TemplatePinSettings::getTemplate62Settings(),
            63 => TemplatePinSettings::getTemplate63Settings(),
            64 => TemplatePinSettings::getTemplate64Settings(),
            65 => TemplatePinSettings::getTemplate65Settings(),
            66 => TemplatePinSettings::getTemplate66Settings(),
            67 => TemplatePinSettings::getTemplate67Settings(),
            68 => TemplatePinSettings::getTemplate68Settings(), //  68      Flip-Box: 320X100 300X250
            69 => TemplatePinSettings::getTemplate69Settings(), //  69      Flip-Over: 300X25 300X250
            70 => TemplatePinSettings::getTemplate70Settings(), //  70      Tile Transition (Advanced):
            72 => TemplatePinSettings::getTemplate72Settings(), //  72      Fade-In: 300X50 320X50
            75 => TemplatePinSettings::getTemplate75Settings(), //  75      Multi-Flip: 300X50 320X50
            76 => TemplatePinSettings::getTemplate59Settings(), //  76      Carousel    300X50 320X50 320X100
            77 => TemplatePinSettings::getTemplate70Settings(), //  77      Tile Transition (Advanced): 300X250    300X250    2018-11-27 18:05:13
            79 => TemplatePinSettings::getTemplate72Settings(), //  79      Fade-In: 300X250    300X250    2018-11-27 18:09:49
            80 => TemplatePinSettings::getTemplate75Settings(), //  80      Multi-Flip: 300X250    300X250    2018-11-27 18:13:39
            81 => TemplatePinSettings::getTemplate69Settings(), //  81      Flip-Over: 300X50 320X50 320X100    300X50 320X50 320X100    2018-11-30 14:26:12
            82 => TemplatePinSettings::getTemplate82Settings(), //  82      Bottom Cube Rotate: 300X50 320X50 320X100    300X50 320X50 320X100    2018-12-11 17:00:39
            83 => TemplatePinSettings::getTemplate83Settings(), //  83      Left Corner Peek and Drop: 300X50 320X50 320X100    300X50 320X50 320X100    2018-12-11 17:32:17
            84 => TemplatePinSettings::getTemplate5Settings(), //  84      Fly-in Bottom: 300X50 320X50 320X100    300X50
            85 => TemplatePinSettings::getTemplate7Settings(), //  85      Left Corner Peek: 300X50 320X50 320X100
            86 => TemplatePinSettings::getTemplate86Settings(), //  86      Development - Curtain Drop: 300X250    300X250
            87 => TemplatePinSettings::getTemplate13Settings(), //  87      Folding Effect: 300X250    300X250    2019-02-15 11
            88 => TemplatePinSettings::getTemplate32Settings(), //  88      Fade Out_Fly: 300X250    300X250    2019-02-17 16
            89 => TemplatePinSettings::getTemplate42Settings(), //  89      Fade-Up (Fade In):
            90 => TemplatePinSettings::getTemplate29Settings(), //  90      Fade-Up Top (Left Corner
            91 => TemplatePinSettings::getTemplate91Settings(), //  91      Near Me Widget (DO
            92 => TemplatePinSettings::getTemplate92Settings(), //  92      Banner Studio    300X300 300X250
            93 => TemplatePinSettings::getTemplate93Settings(), //  93      Mahindra Lead Form    300X600
            94 => TemplatePinSettings::getTemplate94Settings(), //  94      Cash Crusaders Hot Clearance
            95 => TemplatePinSettings::getTemplate49Settings(), //  95      Multi-Slices: 300X600 300X480 //<------------------------------------------>
            96 => TemplatePinSettings::getTemplate51Settings(), //  96      Multi-Slides: 300X600 300X480
            97 => TemplatePinSettings::getTemplate69Settings(), //  97      Flip-Over: 300X600 300X480
            98 => TemplatePinSettings::getTemplate72Settings(), //  98      Fade-In: 300X600 300X480
            99 => TemplatePinSettings::getTemplate70Settings(), //  99      Tile Transition (Advanced):
            100 => TemplatePinSettings::getTemplate7Settings(), //  100  Left Corner Peek
            102 => TemplatePinSettings::getTemplate75Settings(), //  102  Multi-Flip: 300X600
            103 => TemplatePinSettings::getTemplate68Settings(), //  103  Flip-Box: 300X600
            104 => TemplatePinSettings::getTemplate16Settings(), //  104  Tile Transition (Simple
            105 => TemplatePinSettings::getTemplate11Settings(), //  105  Bottom Cube Rotate
            106 => TemplatePinSettings::getTemplate30Settings(), //  106  Left Corner Peek
            107 => TemplatePinSettings::getTemplate5Settings(), //  107  Fly-in Bottom:
            108 => TemplatePinSettings::getTemplate42Settings(), //  108  Fade-Up (Fade In
            109 => TemplatePinSettings::getTemplate29Settings(), //  109  Fade-Up (Left Corner
            110 => TemplatePinSettings::getTemplate32Settings(), // 110  Fade Out_Fly: 300X600
            111 => TemplatePinSettings::getTemplate86Settings(), // 111  Development - Curtain Drop
            112 => TemplatePinSettings::getTemplate13Settings(), // 112  Folding Effect: 300X600
            114 => TemplatePinSettings::getTemplate51Settings(), // 114  Multi-Slides: 300x600 //files correct
            115 => TemplatePinSettings::getTemplate69Settings(), // 115  Flip-Over 300x600 //files correct
            116 => TemplatePinSettings::getTemplate72Settings(), // 116  Fade-In: 300X600 // files correct
            117 => TemplatePinSettings::getTemplate70Settings(), // 117  Tile Transition (Advanced): 300X600
            118 => TemplatePinSettings::getTemplate7Settings(), // 118  Left Corner Peek: 300X600
            119 => TemplatePinSettings::getTemplate75Settings(), // 119  Multi-Flip: 300X600
            120 => TemplatePinSettings::getTemplate16Settings(), // 120  Tile Transition (Simple)
            121 => TemplatePinSettings::getTemplate30Settings(), // 121  Bottom Cube Rotate: 300X600
            122 => TemplatePinSettings::getTemplate30Settings(), // 122  Left Corner Peek and Drop
            123 => TemplatePinSettings::getTemplate5Settings(), // 123  Fly-in Bottom: 300X600
            124 => TemplatePinSettings::getTemplate42Settings(), // 124  Fade-Up (Fade In): 300X600
            125 => TemplatePinSettings::getTemplate29Settings(), // 125  Fade-Up (Left Corner Pop)
            126 => TemplatePinSettings::getTemplate32Settings(), // 126  Fade Out_Fly 300x600
            127 => TemplatePinSettings::getTemplate86Settings(), // 127  Development - Curtain Drop 300x600 // Tested
            128 => TemplatePinSettings::getTemplate13Settings(), // 128  Folding Effect 300x600 //
            129 => TemplatePinSettings::getTemplate49Settings(), // 129  Multi-Slices: 300x600 //Files correct
            130 => TemplatePinSettings::getTemplate92Settings(),
            131 => TemplatePinSettings::getTemplate92Settings(),
            132 => TemplatePinSettings::getTemplate92Settings(),
            137 => TemplatePinSettings::getTemplate137Settings(), // New fold for 300X50 320X50 320X100
            138 => TemplatePinSettings::getTemplate137Settings(), // New fold for 300X250
            139 => TemplatePinSettings::getTemplate137Settings(), // New fold for 320X480
            140 => TemplatePinSettings::getTemplate137Settings(), // New fold for 300x600
            144 => TemplatePinSettings::getTemplate144Settings(), // Swing 300x250
            143 => TemplatePinSettings::getTemplate16Settings(), //testing template
            145 => TemplatePinSettings::getTemplate145Settings(), // 300x250 Position of interactive swipe animation
            146 => TemplatePinSettings::getTemplate146Settings(), // 300x250 Position of interactive swipe animation
            147 => TemplatePinSettings::getTemplate147Settings(), // 300x250 Position of interactive swipe animation
            149 => TemplatePinSettings::getTemplate149Settings(), // steers sample
            150 => TemplatePinSettings::getTemplate150Settings(), // 3D Cube Rotate
            151 => TemplatePinSettings::getTemplate151Settings(), //
            152 => TemplatePinSettings::getTemplate70Settings(), // 360x360 Tile Transition
            153 => TemplatePinSettings::getTemplate49Settings(), // 360x360 Multi Slices
            154 => TemplatePinSettings::getTemplate154Settings(),

        ];


        $fields = [];
        $groups = self::getFieldGroups();
        foreach ($groups as $data) {
            $fields[$data["id"]] = $data["fields"] ?? [];
        }

        foreach ($settings as $key => $templateSettings) {
            $tempVars = [];
            $useStaticPin = false;
            foreach ($templateSettings as $index => $setting) {
                $name = "other";
                foreach ($fields as $i => $data) {
                  if (in_array($setting["name"], $data)) {
                    $name = $i;
                    break;
                  }
                }
                $settings[$key][$index]["groupId"] = $name;
                if ($name == "text-message") {
                    $useStaticPin = true;
                    $data = $settings[$key][$index];
                    $data["label"] = "GeoIP/WiFi ".$data["label"];
                    $data["name"] = $data["name"]."_geoip";
                    if(!self::testWidget($templateId)){
                        $data["value"] = "";
                    }
                    $data["is_geoip"] = true;
                    $tempVars[] = $data;
                    $settings[$key][$index]["label"] = "Fine ".$settings[$key][$index]["label"];

                }
            }

            //Upper, Lower or Capital letter case
            $data = [
                "label"=>"Display Message Case",
                "name"=>"text_transform",
                "type"=>"text_transform",
                "value"=>"",
                "groupId"=>"text-message"
            ];
            $sampleData = [];
            $sampleData[] = $data;
            $settings[$key] = array_merge($settings[$key], $sampleData);

            if ($useStaticPin) {
                $data = [
                    "label"=>"Turn Off GeoIP/WiFi Display Message",
                    "name"=>"banner_static_geoip",
                    "value"=>false,
                    "type"=>"radio",
                    "is_geoip"=>true,
                    "groupId"=>"text-message"
                ];
                $sampleData = [];
                $sampleData[] = $data;
                $settings[$key] = array_merge($settings[$key], $sampleData);
            }

            $settings[$key] = array_merge($settings[$key], $tempVars);
        }


        if (isset($options['format']) && $options['format'] == 'obj') {
            $temp = [];
            foreach ($settings as $template_id => $settingsTemplate) {
                foreach ($settingsTemplate as $setting) {
                    $object = new stdClass();
                    $object->field = $setting['name'];
                    $object->value =  $setting['value'];
                    $object->settings = $setting;
                    $temp[$template_id][] = $object;
                }
            }
            $settings = $temp;
        }

        if ($templateId) {
            return isset($settings[$templateId]) ? $settings[$templateId] : [];
        }

        return $settings;
    }

    public static function setDistanceMessage($settings, $geoip, $geoip_loc, $setDistanceMessage)
    {
        if (isset($settings->multipleDistanceKey)) {
            $settings->multipleDistanceKey = unserialize($settings->multipleDistanceKey);
            $settings->multipleDistanceKey = array_unique($settings->multipleDistanceKey);

            rsort($settings->multipleDistanceKey);
            $word = str_replace(".", "", $setDistanceMessage);
            $word = preg_replace("/\d/", "", $word);
            $words = str_replace($word, "", $setDistanceMessage);
            if (!in_array($word, ["m","yd"])) {
                if (!is_numeric($word)) {
                    $words = 1;
                }
                $words *= 1000;
            }
            $word = $settings->multipleDistanceKey[0];
            foreach ($settings->multipleDistanceKey as $value) {
                if ($words < $value) {
                    $word = $value;
                }
            }

            foreach (self::getFieldGroups() as $rows) {
                foreach ($rows["fields"] as $value) {
                    if (!Campaign::hasGeoIPLocation($geoip)) {
                        if (isset($settings->{$value}) && isset($settings->{$value."_".$word})) {
                            $settings->{$value} = $settings->{$value."_".$word};
                        }
                    }
                }
            }
        }

        if (in_array($geoip, $geoip_loc)) {
            foreach ($settings as $key => $value) {
                if (strpos($key, "_geoip")) {
                    $settings->{str_replace("_geoip", "", $key)} = $value;
                }
            }
        }

        return $settings;
    }

    public static function updateTemplates($data)
    {
        $id = $data["selectedtemplate"];
        $templateCtrl = new TemplatePinsController();
        foreach ($templateCtrl->getCampaignSavedLinked($id, true, "array") as $campaign) {
            if (isset($campaign["banners"])) {
                foreach ($campaign["banners"] as $banner) {
                    Cache::forget(TemplatePinsController::$cacheKeyPrefix.$data["pin_templates_id"].$banner->id);
                    DB::table("template_pins_settings")->where("banner_creative_id", $banner->id)->delete();
                    DB::statement('insert into template_pins_settings(template_pins_id,banner_creative_id,field,value) select template_pins_id,"'.$banner->id.'",field,value from template_pins_settings_saved where template_id_saved = '.$id.';');
                }
            }
        }
    }

    public static function fillPlaceholders($settings,$locationId){
        $htmls = collect($settings->fieldsFormsBuilder)->where('type','html');
        foreach($htmls as $key=>$htmlBanner){
            $htmlBanner->codeReplaced = $htmlBanner->code;
            if(strpos($htmlBanner->code,'%%LB_CLIENT_ID%%')){
                if(strpos($htmlBanner->code,'%%LB_LOCATION_ID%%')){
                    $locationDetails = BrandLocation::select("locationBankId","locationBankClientId")->where("brand_locations.id",$locationId)->join("brands","brands.id","=","brand_locations.brandId")->first();
                    if(!$locationDetails || !$locationDetails->locationBankClientId || !$locationDetails->locationBankId){
                        $locationDetails = new StdClass();
                        $locationDetails->locationBankClientId = "8dca5bbb-f571-46d5-a65b-cdd5cc6d4953";
                        $locationDetails->locationBankId = "8e7d7cb1-e7aa-4581-ba29-8d725d7eea78";
                    }

                    $htmlBanner->codeReplaced = str_replace('%%LB_CLIENT_ID%%', $locationDetails->locationBankClientId, $htmlBanner->codeReplaced);
                    $htmlBanner->codeReplaced = str_replace('%%LB_LOCATION_ID%%', $locationDetails->locationBankId, $htmlBanner->codeReplaced);
                }
            }

            //Replace general macros
            $htmlBanner->codeReplaced = str_replace('%%RANDOM%%', microtime(true).rand(0, 100), $htmlBanner->codeReplaced);
            $htmlBanner->codeReplaced = str_replace('[timestamp]', microtime(true), $htmlBanner->codeReplaced);
        }
        return $settings;
    }

    public static function replacePlaceHolders($settings, $locationDetails, $measurement)
    {
        foreach ($settings as $key => $value) {
            if (is_array($value)) {
                continue;
            }
            $chains = explode('||', $value);
            if (count($chains) > 1) {
                $defaultMsg = $chains[count($chains) - 1];
                for ($i =0; $i <= count($chains); $i++) {
                    $value = $chains[$i];
                    $value = self::searchAndReplacePlaceholder($value, $locationDetails, $measurement);
                    if (!empty($value)) {
                        $settings->{$key} = $value;
                        break;
                    }
                }
                if (empty($value)) {
                    $settings->{$key} = $defaultMsg;
                }
            } else {
                $settings->{$key} = self::searchAndReplacePlaceholder($value, $locationDetails, $measurement);
            }
        }

        return $settings;
    }

    public static function searchAndReplacePlaceholder($value, $locationDetails, $measurement)
    {
        //Note: $distance = $measurement, so in some parts of the system they may still be refered to as distance
        $placeholders = BrandLocation::getMappedPlaceholders();
        $placeholdersValues = [];
        if(strpos($value,'%%DISTANCE%%') !== false){
            if(strpos("km",strtolower($measurement))){
                $distance = str_replace("km","",strtolower($measurement));
                if((int) $distance > 25){
                    $value = "";
                }

            }
        }
        foreach ($placeholders as $placeholder) {
            if (isset($placeholder['field'])) {
                $name = $locationDetails->{$placeholder['field']} ?? "";
                $value = str_replace($placeholder['placeholder'], $name, $value);
            } else { //Only for the measurement place holder
                $value =  str_replace($placeholder['placeholder'], $measurement, $value);
            }
        }

        return $value;
    }

    public static function formatByField($settings)
    {
        $object = new stdClass();
        foreach ($settings as $setting) {
            $object->{$setting->field} = $setting->value;
        }
        return $object;
    }

    public static function formatDisplayMessageCasing($settings)
    {
        $fnTransform = function($text_transform, $text){
            switch($text_transform){
                case 'uppercase':{
                    return strtoupper($text);
                }break;
                case 'lowercase':{
                    return strtolower($text);
                }break;
                case 'capitalize':{
                    return ucwords(strtolower($text));
                }break;
            }
            return $text;
        };

        $fnApplyTransform = function($text_transform, $settings, $fnTransform, $keyModifiyer=''){
            if(isset($text_transform) && !empty($text_transform)){
                $distanceKeys = [
                    "distance_value_text",
                    "distance_value_text_geoip",
                    "from_location",
                    "from_location_geoip",
                ];
                foreach($distanceKeys as $key){
                    $settingKey = "{$key}{$keyModifiyer}";
                    if(isset($settings->{$settingKey})){
                        $settings->{$settingKey} = $fnTransform($text_transform, $settings->{$settingKey});
                    }
                }
            }
        };

        //Apply to carousel image groups for (carousel template)
        if(isset($settings->carousel_images)){
            foreach($settings->carousel_images as $image){
                $text_transform = isset($settings->{"text_transform~{$image->id}"}) ? ( empty($settings->{"text_transform~{$image->id}"}) ? ($settings->text_transform ?? false) : $settings->{"text_transform~{$image->id}"} )  :  ($settings->text_transform ?? false);
                $fnApplyTransform($text_transform, $settings, $fnTransform, "~{$image->id}");
            }
        }

        //Apply to global settings (for other templates)
        if(isset($settings->text_transform)){
            $fnApplyTransform($settings->text_transform, $settings, $fnTransform);
        }

        return $settings;
    }

    public static function createBannerDefaults($banner_id)
    {
        TemplatePinSettings::where('banner_creative_id', $banner_id)->delete();

        $template_id = TemplatePinSettings::DEFAULT_STATIC_TEMPLATE;
        $defaultStaticTemplateFunction = "getTemplate{$template_id}Settings";
        $defaultStaticTemplateSettings = TemplatePinSettings::$defaultStaticTemplateFunction();

        $data = [];
        foreach ($defaultStaticTemplateSettings as $setting) {
            $data[] = [
                'template_pins_id' => $template_id,
                'banner_creative_id' => $banner_id,
                'field'=>$setting['name'],
                'value'=>$setting['value']
            ];
        }
        TemplatePinSettings::insert($data);
    }

    public static function uploadCustomisablePin($imageName, $pinSettings, $templateId)
    {

        try {
            $x = isset($pinSettings['x']) ? $pinSettings['x'] : 40;
            $y = isset($pinSettings['y']) ? $pinSettings['y'] : 50;

            $imageSvgXml = DB::table('template_customisable_pins')->where('template_id', $templateId)->first();

            if (!isset($imageSvgXml) || empty($imageSvgXml)) {
                //Excluding template 83 - Left Corner Peek Drop. SVG Pin is saved directely in a Database.
                if (($templateId >= 68 || $templateId != 83 || $templateId != 84 || $templateId != 87) || in_array($templateId, [5, 10, 16, 29, 31, 39, 40, 49, 50, 51, 52, 59])) {

                    $pinSettings['distanceWidth'] = $pinSettings['width'];
                    $pinSettings['distanceHeight'] = $pinSettings['height'];
                    $pinSettings['distanceTop'] = 0;
                    $pinSettings['distanceLeft'] = 0;

                    if($templateId == 29 || $templateId == 42 || $templateId == 89 || $templateId == 90 || $templateId == 124 || $templateId == 125 || $templateId == 109){
                        $pinSettings['distancebackgroundColor'] = 'Transparent';
                    }

                    if($templateId == 31 ) {
                        $r = 75;

                        if ($pinSettings['height'] == 50) {
                            $pinSettings['distanceWidth'] = 35;
                            $pinSettings['distanceHeight'] = 35;
                            $pinSettings['distanceTop'] = 0;
                            $pinSettings['distanceLeft'] = 0;
                            $y = 75;
                        }
                        if ($pinSettings['height'] == 100) {
                            $pinSettings['distanceWidth'] = 80;
                            $pinSettings['distanceHeight'] = 80;
                            $pinSettings['distanceTop'] = 0;
                            $pinSettings['distanceLeft'] = 0;
                            $y = 75;
                            $r = 75;
                        }

                    }

                    // Leaderboards
                    if ($pinSettings['height'] <= 100) {
                        if ($pinSettings['height'] <= 100) {
                            $y = 75;
                        }
                        $r = 75;
                    } else {
                        $r = 50;

                        if ($pinSettings['height'] == 250) {
                            $r = 75;
                            $pinSettings['distanceTop'] = 0;
                            $y = 75;
                            $x = 40;
                            $pinSettings['distanceHeight'] = 250;
                        }
                        if ($pinSettings['height'] == 480) {
                                $pinSettings['distanceTop'] = -50;
                            $r = 75;
                            $x = 50;
                            $pinSettings['distanceLeft'] = 30;
                        }
                        if ($pinSettings['height'] == 600) {


                            $pinSettings['distanceTop'] = -100;
                            $r = 75;
                            $x = 50;
                            $pinSettings['distanceLeft'] = 30;
                        }

                        if($templateId == 86){

                            if ($pinSettings['height'] == 250) {
                                $r = 75;
                                $pinSettings['distanceTop'] = 40;
                                $y = 75;
                                $x = 40;
                                $pinSettings['distanceHeight'] = 250;
                            }
                            if ($pinSettings['height'] == 480) {
                                $pinSettings['distanceTop'] = -10;
                                $r = 75;
                                $x = 50;
                                $pinSettings['distanceLeft'] = 30;
                            }
                            if ($pinSettings['height'] == 600) {
                                $pinSettings['distanceTop'] = -60;
                                $r = 75;
                                $x = 50;
                                $pinSettings['distanceLeft'] = 30;
                            }
                        }
                        if ($templateId == 122 || $templateId == 106) {
                            $pinSettings['distanceWidth'] = 45;
                            $pinSettings['distanceHeight'] = 45;
                            $pinSettings['distanceTop'] = 0;
                            $pinSettings['distanceLeft'] = 0;
                            $y = 75;
                        }

                    }

                    $imageSvgXml = DB::table('template_customisable_pins')->where('default_yn', true)->get();
                    $imageSvgXml = $imageSvgXml[1];
                } else {
                    $imageSvgXml = DB::table('template_customisable_pins')->where('default_yn', true)->first();
                }
                if ($templateId == 100) {
                    $imageSvgXml = DB::table('template_customisable_pins')->where('template_id', 118)->first();
                }
            }

            $imageSvgXml = $imageSvgXml->image;

            if($templateId == 7 || $templateId == 11 || $templateId == 13 || $templateId == 30 || $templateId == 83 || $templateId == 85 || $templateId == 87 || $templateId == 105){
                $pinSettings['distanceWidth'] = $pinSettings['width'] ?? 300;
                $pinSettings['distanceHeight'] = $pinSettings['height'] ?? 250;
                $pinSettings['distanceTop'] = 0;
                $pinSettings['distanceLeft'] = 0;
            }

            //Fly-in Bottom pin requires transparent background
            if($templateId == 5 || $templateId == 84){
                $pinSettings['distancebackgroundColor'] = 'Transparent';
                $pinSettings['distanceWidth'] = $pinSettings['width'] ?? 300;
                $pinSettings['distanceHeight'] = $pinSettings['height'] ?? 250;
                $pinSettings['distanceTop'] = 0;
                $pinSettings['distanceLeft'] = 0;
            }

            isset($r) ? $r : $r = 75;

            if($templateId == 145){
                $pinSettings['distancebackgroundColor'] = 'Transparent';
            }

            $imageSvgXml = str_ireplace(
                [
                    '{$x}', '{$y}', '{$distanceW}', '{$distanceH}', '{$distanceTop}', '{$distanceLeft}',
                    '{$distancebackgroundColor}', '{$distanceTopColor}', '{$distanceInnerCircleColor}', '{$distanceBottomColor}', '{$r}'
                ],
                [
                    $x, $y, $pinSettings['distanceWidth'], $pinSettings['distanceHeight'], $pinSettings['distanceTop'], $pinSettings['distanceLeft'],
                    $pinSettings['distancebackgroundColor'], $pinSettings['distanceTopColor'],
                    $pinSettings['distanceInnerCircleColor'], $pinSettings['distanceBottomColor'], $r
                ],
                $imageSvgXml
            );

        } catch (Exception $e) {
            // throw new Exception('Failed to create customised pin: ' . $e->getMessage(), -1, $e);
        }

        try {
            $storage = env("STORAGE_DRIVER");

            $filePath = 'templates/pin/template' . $templateId . '/custom/' . $imageName;

            if ($storage == 's3') {
                $fileUrl = 'https://static.vic-m.co/' . $filePath;

                if (\Storage::disk('s3')->exists($filePath)) {
                    \Storage::disk('s3')->delete($filePath);
                }

                // Ask Devin which method are we using
                //~ \Storage::disk('s3')->put($filePath, file_get_contents($imageSvgXml));
                \Storage::disk('s3')->put($filePath, $imageSvgXml);
            } elseif ($storage == 'local') {
                $filePath = '/templates/pin/template' . $templateId . '/custom/' . $imageName;

                $dir = public_path('/templates/pin/template' . $templateId . '/custom/');
                if(!File::exists($dir)) {
                    File::makeDirectory($dir, 0755, true, true);
                }

                $fileUrl = "http://127.0.0.1:8000".$filePath;

                //dd($filePath);
                // if (strpos(php_uname('s'), 'Windows') !== false) {
                //     $filePath = '/storage/templates/pin/template' . $templateId . '/custom/' . $imageName;
                //     if (\Storage::disk('public_windows')->exists($filePath)) {
                //         \Storage::disk('public_windows')->delete($filePath);
                //     }
                //
                //     \Storage::disk('public_windows')->put($filePath, $imageSvgXml);
                // }
                //
                //
                // if (\Storage::disk('local')->exists($filePath)) {
                //     \Storage::disk('local')->delete($filePath);
                // }
                // \Storage::disk('local')->put("public/" . $filePath, $imageSvgXml);
                file_put_contents(public_path($filePath),$imageSvgXml);
            } else {
                throw new Exception('Undefined storage driver');
            }
        } catch (Exception $e) {
            throw new Exception('Failed to upload customised pin', -1, $e);
        }

        return $fileUrl;
    }

    public static function uploadImagePin($imageName, $imageFile, $templateId)
    {
        try {
            $storage  = env('STORAGE_DRIVER');
            $filePath = 'templates/pin/template'.$templateId.'/'.$imageName;

            if ($storage=='s3') {
                $fileUrl  = 'https://static.vic-m.co/'.$filePath;
                if (\Storage::disk('s3')->exists($filePath)) {
                    \Storage::disk('s3')->delete($filePath);
                }

                \Storage::disk('s3')->put($filePath, file_get_contents($imageFile));
            } elseif ($storage=='local') {
                $filePath = 'public/templates/pin/template' . $templateId . '/' . $imageName;

                $fileUrl  = url($filePath);
                if (\Storage::disk('local')->exists($filePath)) {
                    \Storage::disk('local')->delete($filePath);
                }
                file_put_contents($filePath,file_get_contents($imageFile));
                // \Storage::disk('local')->put($filePath, file_get_contents($imageFile));
            } else {
                throw new Exception('Undefined storage driver');
            }
        } catch (Exception $e) {
            throw new Exception('Failed to upload customised pin', -1, $e);
        }

        return $fileUrl;
    }
}
