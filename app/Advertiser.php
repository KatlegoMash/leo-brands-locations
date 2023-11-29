<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class Advertiser extends Model
{
    use SoftDeletes;

    /**
     * Fields mapping:
     * advertiserName = advertiserName
     * contactPerson = contactName
     * contactPhone = n/a
     * contactEmail = emailAddress
     */
    protected $fillable = ['contactName','contactPhone','emailAddress'];
    protected $guarded  = ['id', 'agencyId', 'advertiserId' ,'advertiserName'];
    protected $hidden   = ['agencyId','username', 'password', 'comments','_token',
        'created_at','updated_at','deleted_at'];

    private $rules = [
            'advertiserName' => 'required|min:3|max:128',
            'contactName'    => 'required|min:2|max:128',
            'contactPhone'   => 'numeric',
            'emailAddress'   => 'required|email',
            'addressLine1'   => 'min:5|max:128',
            'city'           => 'min:3|max:128',
            'currency'       => 'required|min:2|max:24',
            'accountName'    => 'min:3|max:124',
            'accountPhone'   => 'numeric',
            'accountEmail'   => 'email',
            'postalCode'     => 'min:4',
            'country'        => 'required',
            'currency'       => 'required',
            'clientType'     => '',
            'province'       => ''
    ];

    public function validate($input)
    {
        return Validator::make($input, $this->rules);
    }

    public function brands()
    {
        return $this->hasMany('App\Brand', 'advertiserId', 'advertiserId');
    }

    public function contact_link()
    {
        return $this->hasMany('App\ContactLink');
    }

    public function contact_info()
    {
        return $this->hasMany('App\ContactInfo');
    }

    public function locations()
    {
      return $this->hasMany('App\BrandLocation');
    }

    public static function boot()
    {
        parent::boot();

        Advertiser::creating(function ($advertiser) {
            $newId = DB::table("advertisers")->orderBy("advertiserId", "desc")->first();
            $advertiser->advertiserId = $newId->advertiserId + 1;
            return true;
        });
    }

    public function Delete(array $options = [])
    {
        $brands = $this->brands;

        foreach ($brands as $brand) {
            $locations = $brand->locations();
            $locations->Delete();
            $brand->Delete();
        }

        parent::Delete($options);
    }
}
