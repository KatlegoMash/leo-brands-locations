<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class Brand extends Model
{
    use SoftDeletes;
    
    protected $guarded = ['id'];

    private $rules = [
            'advertiserId'  => 'required',
            'brandName'     => 'required|min:3|max:64',
            'billingName'   => 'required|min:3|max:128',
            'billingEmail'  => 'required|email',
            'billingTel'    => 'required|numeric'
    ];

    public function validate($input)
    {
        // Please chat with Mdu if you want to enable this
        // $this->rules['brandName'] = $this->rules['brandName']."|unique:brands,brandName,NULL,id,deleted_at,NULL";
        return Validator::make($input, $this->rules);
    }

    public function validateUpdate($input)
    {
        return Validator::make($input, $this->rules);
    }

    public function advertiser()
    {
        return $this->belongsTo('App\Advertiser', 'advertiserId', 'advertiserId');
    }

    public function locations()
    {
        return $this->hasMany('App\BrandLocation', 'brandId');
    }

    public function campaigns()
    {
        return $this->hasMany('App\Campaign', 'brandId');
    }

    public function contact_link()
    {
        return $this->hasMany('App\ContactLink');
    }

    public function contact_info()
    {
        return $this->hasMany('App\ContactInfo');
    }

/*    public function locationBankData(){
        return $this->hasManyThrough(LocationBankData::class, LocationBankBrandsSearchId::class, 'brandId', 'brand_id', 'id', 'brandId');
    }*/

    public function locationBankSearchId(){
        return $this->hasOne(LocationBankBrandsSearchId::class, 'brandId', 'id');
    }

    public function content_categories()
    {
        return $this->belongsToMany(ContentCategory::class, 'brand_content_categories');
    }

    public function contacts($returnQuery=false)
    {
        //REVIEW -> why was this not a simple relationship? what's the relationship between this and brands
        $contacts = DB::table('contact_link')
            ->join('brands', 'contact_link.parentId', '=', 'brands.id')
            ->join('contact_info', function ($join) {
                $join->on('contact_link.contactInfoId', '=', 'contact_info.id');
            })
            ->select(['contact_link.type', 'contact_info.*'])
            ->where('brands.deleted_at', null)
            ->where('contact_link.parentType', 'Brands')
            ->where('brands.id', $this->id);

        if(!$returnQuery){
            $contacts = $contacts->get();
        }

        return $contacts;
    }

    public static function createOrUpdateBrand(Brand $brand, array $brandInput)
    {
        $billingContact = collect($brandInput['contacts'])->firstWhere('type','billing');

        $validator = $brand->exists ? 'validateUpdate' : 'validate';
        $valid = $brand->$validator([
            'brandName' => $brandInput['name'] ?? '',
            'brandUrl' => $brandInput['brandUrl'] ?? '',
            'advertiserId' => $brandInput['advertiserId'] ?? '',
            'billingName' => $billingContact['name'] ?? '',
            'billingEmail' => $billingContact['email'] ?? '',
            'billingTel' => $billingContact['phone'] ?? '',
        ]);

        if($valid->passes()){
            $brand->brandName = $brandInput['name'];
            $brand->brandUrl = $brandInput['brandUrl'];
            $brand->geofence = isset($brandInput['geofence']) ? $brandInput['geofence'] : 5000;
            $brand->maximum_capacity = isset($brandInput['maximum_capacity']) ? $brandInput['maximum_capacity'] : 0;
            $brand->advertiserId = $brandInput['advertiserId'];
            $brand->visits_yn = 0;
            $brand->use_nearme_yn = 0;

            if (isset($brandInput['visits_yn'])) {
                $brand->visits_yn = $brandInput['visits_yn'];
            }

            if (isset($brandInput['use_nearme_yn'])) {
                $brand->use_nearme_yn = $brandInput['use_nearme_yn'];
            }
            
            if(isset($brandInput['clientId'])){
                $brand->locationBankClientId = $brandInput['clientId'];
            }
            
            $brand->save();

            //Put contacts
            try{

                $contacts = collect($brandInput['contacts']);
                $billing = $contacts->firstWhere('type','billing');
                $reporting = $contacts->firstWhere('type','reporting');
                $creative = $contacts->firstWhere('type','creatives');

                if(!$reporting){
                    $reporting = $billing;
                    $reporting['type'] = 'reporting';
                    $contacts->push($reporting);
                }

                if(!$creative){
                    $creative = $billing;
                    $creative['type'] = 'creatives';
                    $contacts->push($creative);
                }

                foreach($contacts as $contact){
                    $contactInfo = ContactInfo::firstOrCreate([
                        'contactFirstName' => $contact['name'], 
                        'contactEmail' => $contact['email'], 
                        'contactPhone' => $contact['phone']
                    ]);

                    $contactLink = ContactLink::firstOrCreate([
                        'parentType' => 'Brands', 
                        'type' => $contact['type'], 
                        'parentId' => $brand->id, 
                        'contactInfoId' => $contactInfo->id
                    ]);
                }
            } catch(QueryException $q){
                throw $q;
            }

            return true;
        }

        return $valid->errors();
    }

    public static final function VisitBrands(){
        $brands = self::orderBy('brandName')->where('visits_yn',true)->get();
        
        return $brands;
    }

    public function delete()
    {
        $brandLocations = BrandLocation::where('brandId', $this->id)->where('deleted_at', null)->get();

        if($brandLocations->isNotEmpty()){
            foreach($brandLocations as $brandLocation){
                $brandLocation->delete();
            }
        }

        return parent::delete();
    }
}
