<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Rules\Location as LocationRule;
use App\Rules\Phone as PhoneRule;
use App\Rules\PhoneLength as PhoneLengthRule;
use App\Rules\CountryCode as CountryCodeRule;
use App\Rules\RatingRule;
use Exception;
use Auth;
use Illuminate\Database\QueryException;

class BrandLocation extends Model
{
    use SoftDeletes;

    protected $table = 'brand_locations';

    protected $with = [
        'brand:id,brandName,visit_score'
    ];

    protected $hidden   = ['_token'];
    protected $guarded  = ['id'];

    private $rules = [
        'locationName' => 'required|min:3|max:128',
        'latitude' => 'required',
        'longitude' => 'required',
        'storeName' => 'required|min:3|max:128',
        'storeCode' => '',
        'addressLine1' => 'required',
        'addressLine2' => '',
        'postalZipCode' => '',
        'city' => 'required',
        'countryCode' => 'required|min:2|max:2',
        'homePage' => 'min:3',
        'maxGeofence' => 'required|numeric',
        'phone' => 'required|max:50',
        // 'rating' => 'required'
    ];

    public static function getMappedPlaceholders()
    {

        $placeholders = [
            [
                'field' => 'addressLine1',
                'placeholder' => '%%ADDRESS1%%',
                'name' => 'Address Line 1',
                'must_have_default_message' => true,
                'examples' => [
                    [
                        'example' => '%%ADDRESS1%%||My default text',
                        'description' => ''
                    ],
                    [
                        'example' => '%%ADDRESS1%%||%%ADDRESS2%%||My default text',
                        'description' => ''
                    ],
                ]

            ],
            [
                'field' => 'addressLine2',
                'placeholder' => '%%ADDRESS2%%',
                'name' => 'Address Line 2',
                'must_have_default_message' => true,
                'examples' => [
                    [
                        'example' => '%%ADDRESS2%%||My default text',
                        'description' => ''
                    ]
                ]
            ],
            [
                'field' => 'locationName',
                'placeholder' => '%%SUBURB%%',
                'name' => 'Suburb',
                'must_have_default_message' => true,
            ],
            [
                'field' => 'city',
                'placeholder' => '%%CITY%%',
                'name' => 'City',
                'must_have_default_message' => true,
                'examples' => [
                    [
                        'example' => '%%CITY%%||My default text',
                        'description' => ''
                    ]
                ]
            ],
            [
                'field' => 'storeName',
                'placeholder' => '%%STORENAME%%',
                'name' => 'Store Name',
                'must_have_default_message' => false,
            ],
            [
                'field' => 'customMsg',
                'placeholder' => '%%CUSTOMLOCMSG%%',
                'name' => 'Location Custom Message',
                'must_have_default_message' => true,
                'examples' => [
                    [
                        'example' => '%%CUSTOMLOCMSG%%||My default text',
                        'description' => 'This means it will first check for a custom message if one exists, if that does not exist then it will try suburb else it will display the custom default text typed at the end.'
                    ],
                    [
                        'example' => '%%CUSTOMLOCMSG%%||%%ADDRESS1%%||%%ADDRESS2%%||My default text',
                        'description' => 'This means it will first check for a custom message if one exists, if that does not exist then it will try address1, if that does not exist then it will try address 2, else it will display the custom default text typed at the end.'
                    ],
                    [
                        'example' => '%%CUSTOMLOCMSG%%||%%SUBURB%%||My default text',
                        'description' => 'This means it will first check for a custom message if one exists, if that does not exist then it will try suburb else it will display the custom default text typed at the end.'
                    ]
                ]
            ],
            [
                'placeholder' => '%%DISTANCE%%',
                'name' => 'Distance',
                'must_have_default_message' => false,
            ],
        ];
        return $placeholders;
    }

    public static function boot()
    {
        BrandLocation::deleting(function ($brand_location) {
            if (CampaignLocation::where('brandLocationId', $brand_location->id)->delete() > 0) {
                return true;
            }
            return true;
        });
    }

    public function validate($input, $id = null)
    {
        if (isset($input['brandId']) && $id) {
            $this->rules['locationName'] = $this->rules['locationName'] . "|unique:brand_locations,locationName,$id,id,BrandId," . $input['brandId'] . ",deleted_at,NULL";
        } elseif (isset($input['brandId'])) {
            $this->rules['locationName'] = $this->rules['locationName'] . "|unique:brand_locations,locationName,NULL,id,BrandId," . $input['brandId'] . ",deleted_at,NULL";
        }

        $messages = [
            'locationName.unique' => 'The location name already exists: '.$input['locationName']
        ];

        // if(Auth::user()->role->id != 4) {
        //   $this->rules['latitude'] = new LocationRule;
        //   $this->rules['longitude'] = new LocationRule;
        // }
        $this->rules['rating'] = new RatingRule;

        $this->rules['phone'] = new PhoneRule;
        $this->rules['countryCode'] = new CountryCodeRule;
        return Validator::make($input, $this->rules, $messages);
    }

    public function validateUpdate($input)
    {
      // if(Auth::user()->role->id != 4) {
      //   $this->rules['latitude'] = new LocationRule;
      //   $this->rules['longitude'] = new LocationRule;
      // }

        $this->rules['phone'] = [new PhoneRule,new PhoneLengthRule];
        $this->rules['countryCode'] = new CountryCodeRule;
        $this->rules['rating'] = new RatingRule;

        return Validator::make($input, $this->rules);
    }

    public function brand()
    {
        return $this->belongsTo('App\Brand', 'brandId', 'id');
    }

    public function widget()
    {
        return $this->hasMany('App\WidgetCategories');
    }

    public function dynamic_fields()
    {
        return $this->hasMany('App\BrandLocationAdditionalInfo');
    }

    public function brand_location_category()
    {
        return $this->belongsToMany(
            WidgetCategories::class, 'widget_categories_brand_location', 'brand_location_id', 'categoriesId'
        );
    }

    public function widget_categories()
    {
        return $this->hasMany('App\StoredWidgetCategories');
    }

    public function widgetCategories()
    {
        return $this->belongsToMany('App\StoredWidgetCategories', 'widget_categories_brand_location', 'brand_location_id', 'categoriesId');
    }

    public function polygon()
    {
        return $this->hasMany('App\PolygonPoint', 'brandLocationId', 'id');
    }

    public function advertiser()
    {
        return $this->belongsTo('App\Advertiser');
    }

    public function operatingHours($returnQuery=false)
    {
        $operatingHours = DB::table('brand_locations_operating_hours')
            ->join('brand_locations', 'brand_locations_operating_hours.brandLocationId', '=', 'brand_locations.id')
            ->select('brand_locations_operating_hours.*')
            ->where('brand_locations.deleted_at', null)
            ->where('brand_locations_operating_hours.deleted_at', null)
            ->where('brand_locations.id', $this->id);

        if(!$returnQuery){
            $operatingHours = $operatingHours->get();
        }

        return $operatingHours;
    }

    public function saveOperatingHours($inputOperatingHours)
    {
        DB::beginTransaction();
        try{
            $this->operatingHours(true)->delete();

            foreach($inputOperatingHours as $operatingHours){
                $storeHours = [
                    "brandLocationId" => $this->id,
                    "closed_yn" => $operatingHours['closed'],
                    "day_type" => $operatingHours['day'],
                    "created_at" => date('Y-m-d H:i:s')
                ];
                if(!$operatingHours['closed']){
                    $storeHours["start_time"] = $operatingHours['startTime'];
                    $storeHours["end_time"] = $operatingHours['endTime'];
                }
                if($operatingHours['day']=='Exception'){
                    $storeHours['exception_startdate'] = $operatingHours['startDate'];
                    $storeHours['exception_enddate'] = $operatingHours['endDate'];
                }
                DB::table('brand_locations_operating_hours')->insert($storeHours);
            }
            DB::commit();
        }catch(QueryException $q){
            DB::rollBack();
            throw $q;
        }
    }

    public function deleteOperatingHours()
    {
        $hours = $this->operatingHours();
        if($hours->isNotEmpty()){
            $hours->each(function($v){
                DB::table('brand_locations_operating_hours')->where('brandLocationId', $this->id)->update([
                    'deleted_at' => date('Y-m-d H:i:s')
                ]);
            });
        }
    }

    public function delete()
    {
        CampaignLocation::where('brandLocationId', $this->id)->delete();

        // if($campaignsLinked->isEmpty()){
        //     // foreach($this->polygon as $point){
        //     //     $point->delete();
        //     // }
        //     // $this->deleteOperatingHours();
        // } else {
        //     // throw new Exception('Could not delete the location. It is linked to campaigns.');
        // }

        return parent::delete();
    }

    /**
     * Compute the distance from this location to the given position
     * @param BrandLocation $location
     * @return Number
     */
    public function getDistanceFromPosition(array $user_location)
    {
        $radiusOfEarth = 6371000; // Earth's radius in meters.

        $diffLatitude  = $this->getLatitude()  - deg2rad($user_location['latitude']);
        $diffLongitude = $this->getLongitude() - deg2rad($user_location['longitude']);

        $a =    sin($diffLatitude / 2)                      * sin($diffLatitude / 2)    +
            cos(deg2rad($user_location['latitude']))    * cos($this->getLatitude()) *
            sin($diffLongitude / 2)         * sin($diffLongitude / 2);

        $c = 2 * asin(sqrt($a));
        $distance = $radiusOfEarth * $c;
        return $distance; //distance of locations
    }

    /**
     * Get a list of nearby brands based in proximity to the users location, in kilometers.
     * TODO All where campaigns are active or all?
     * TODO Return brands or campaings?
     * @param array $user_location
     * @return array|NULL
     */

    public static function nearbyBrands(array $user_location)
    {
        $locations = BrandLocation::with('brand')->get();

        if ($locations) {
            $distances = [];
            foreach ($locations as $location) {
                $_distance = $location->getDistanceFromPosition($user_location);
                $distances[$location->id] = [
                    'distance'   => round($_distance / 1000, 1),
                    'locationId' => $location->id
                ];
            }

            //sort the distances, then chunk the top 10
            if (asort($distances)) {
                $distances = array_chunk($distances, 10, true);
            }

            return current($distances);
        }

        return null;
    }

    function getLatitude()
    {
        return deg2rad($this->latitude);
    }

    function getLongitude()
    {
        return deg2rad($this->longitude);
    }

    function getCoordinates()
    {
        return sprintf('%s,%s', $this->latitude, $this->longitude);
    }

    function getFullAddress()
    {
        $address = [];
        $address[] = $this->addressLine1;

        if ($this->addressLine2) {
            $address[] = $this->addressLine2;
        }

        if ($this->city) {
            $address[] = $this->city;
        }

        if ($this->postalZipCode) {
            $address[] = $this->postalZipCode;
        }

        return implode(', ', $address);
    }

    public function phoneNumberRegex() {

        if(isset($this->phone)) {
            return preg_match('/(?<!.)((\+27|0)+\d{9}|\*[0-9]+(\*[0-9]+)*\#)(?!.)/', $this->phone);
        } else {
            return 0;
        }

    }

    /**
     * Get the phone number formatted as either E164, RFC3966 or National standard
     * READ THE F***N MANUAL: https://github.com/giggsey/libphonenumber-for-php/blob/master/docs/PhoneNumberUtil.md
     * @param string $defaultFormat E164|RFC3966|NATIONAL
     * @param string $defaultCountryCode ZA| ISO 3166-1 two letter country code
     * @return string $number|$error_message
     */
    public function getPhoneNumber($defaultFormat = 'E164', $countryCode = null)
    {
        // Country code may be incorrect because input validation is crappy at the moment
        // If the country code is not ISO 3166-1, then fallback to ZA
        $countryCode = $countryCode ?? $this->countryCode ?? 'ZA';

        $number = false;

        
        if (!isset($this->phone)) {
            return false;
        }

        //if is USSD
        if (preg_match('/^\*[0-9\*#]*[0-9]+[0-9\*#]*#$/', $this->phone)) {
            $number = $this->phone;
        } else {
            $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();

            try {
                $numberProto = $phoneUtil->parse($this->phone, $countryCode);
                $isPossible = $phoneUtil->isPossibleNumber($numberProto);
                $isValid    = $phoneUtil->isValidNumber($numberProto);
                $isValidForRegion = $phoneUtil->isValidNumberForRegion($numberProto, $countryCode);

                //if( ! $isValidForRegion ){
                //	Log::warning('Brand Location Phone is not valid for the region ' . $countryCode);
                //}

                if ($isPossible && $isValid) {
                    //format
                    if ($defaultFormat == 'RFC3966') {
                        $number = $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::RFC3966); //RFC3966	tel:+27-11-805-6789
                    } elseif ($defaultFormat == 'NATIONAL') {
                        $number = str_replace(' ', '', $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::NATIONAL)); // tel:0118056789
                    } elseif ($defaultFormat == 'INTERNATIONAL') {
                        $number = str_replace(' ', '', $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::INTERNATIONAL)); // tel:+27 72 007 784
                    } else {
                        $number = $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::E164); //E164	+27118056789
                    }
                } else {
                    // throw new Exception('Phone number is not a possible number or valid number');
                }
            } catch (\libphonenumber\NumberParseException $e) {
                // throw $e;
                return false;
            }
        }

        if ($this->phone !== false) {
            $number = $defaultFormat == 'RFC3966' ? $number : sprintf('tel:%s', $this->phone);
        }
        // Debug phone for this brand_location id = 945765
        // if ($number !== false) {
        //     $number = $defaultFormat == 'RFC3966' ? $number : sprintf('tel:%s', $number);
        // }

        return $number;
    }

    public function saveKml(string $xml)
    {
        $path = public_path() . "/kml/locations/";
        if (!file_exists($path))
            mkdir($path, 0777, true);
        file_put_contents("$path{$this->id}.xml", $xml);
    }

    public function getKmlUrl()
    {
        $file = "/kml/locations/{$this->id}.xml";
        if ($this->polygon->count() && file_exists(public_path() . $file)) {
            return url($file);
        }
        return false;
    }

    public function getPolygon()
    {
        if (count($this->polygon)) {
            return $this->polygon->map(function ($item) {
                    return [
                        "lng" => (float)$item->longitude,
                        "lat" => (float)$item->latitude
                    ];
            });
        } else {
            return [];
        }

    }

    public function rating() {
        return $this->hasOne(\App\BrandLocationRating::class, 'brand_location_id', 'id')->withDefault(["rating" => null]);
    }

    public function getOperatingHours() {
        return $this->hasMany(\App\BrandLocationsOperatingHours::class, 'brandLocationId', 'id');
    }

    public function nameTags() {
        return $this->belongsToMany('App\NameTags', 'location_name_tags', 'locationId', 'tag_id')->withTimestamps();
    }

    public function ara_places()
    {
        return $this->hasMany(BrandLocationAraPlaces::class);
    }

    public static function isAraCompliant($brandLocation)
    {
        $result = "<span class='badge badge-success'>Compliant</span>";
        
        if(sizeof($brandLocation->ara_places) > 0){
            $result = "<a href='/locations/ara?locationId={$brandLocation->id}' class='badge badge-danger'>Non Compliant</span>";
        }

        return $result;
    } 
}
