<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CampaignLocation extends Model
{
    protected $table    = "campaign_location";
    protected $guarded  = ['id','campaignId','brandLocationId','address1','address2','suburb','city','customMsg'];
    //~ protected $hidden   = array('campaignId');

    /**
     * Define the 1:1 relationship between a BrandLocation and a CampaignLocation,
     * because they are really the same instance only made unique by the campaign.
     */
    public function brand_location()
    {
        return $this->hasOne('App\BrandLocation', 'id', 'brandLocationId');
    }

    public function getTableName()
    {
        return $this->table;
    }

    public function campaign()
    {
        return $this->belongsTo('App\Campaign', 'campaignId', 'campaignId');
    }


    /** Add a location and save for the Link
     */
    public function addLocation($campaign_id, $brand_location_id)
    {
        $campaignLocation = CampaignLocation::firstOrNew(['campaignId'=>$campaign_id,'brandLocationId'=>$brand_location_id]);
        $campaignLocation->campaignId = $campaign_id;
        $campaignLocation->brandLocationId = $brand_location_id;


        //copy maxGeoFence
        $geo = BrandLocation::find($brand_location_id)->maxGeofence;
        $campaignLocation->maxGeofence = $geo;

        $campaignLocation->save();
    }

    /** Pass an array of brand location ids
     */
    public function addLocations($campaign_id, $location_ids)
    {
        foreach ($location_ids as $location_id) {
            $this->addLocation($campaign_id, $location_id);
        }
    }

    public function addUrl($url, $id = null)
    {
        if (!$id) {
            if (!$this->id) {
                return false;
            }
            $cl = CampaignLocation::find($this->id);
        } else {
            $cl = CampaignLocation::find($id);
        }

        $cl->url = $url;

        return $cl->save();
    }

    /**
     * Deprecated
     * @param unknown $id
     * @return boolean
     */
    public static function getURL($id)
    {
        //check if we have a URL on campaign location
        $location = CampaignLocation::find($id);

        if (isset($location)) {
            return $location->getPreferredWebUrl();
        }

        return false;
    }

    /**
     * Get the preferred URL to use from either the campaign, location or brandlocation
     * @param CampaignLocation $location
     * @return string|NULL
     */
    public function getPreferredWebUrl()
    {
        if (isset($this->url) && false === empty($this->url)) {
            $the_url = trim($this->url);
        } else {
            // Below line also checks for != 'None' because we store 'None' in the case where a landing page is being used but the web action is not
            if (isset($this->campaign->campaignUrl) && false === empty($this->campaign->campaignUrl) && $this->campaign->campaignUrl != 'None') {
                $the_url = trim($this->campaign->campaignUrl);
            } else {
                if (isset($this->brand_location->homePage) && false === empty($this->brand_location->homePage)) {
                    $the_url = trim($this->brand_location->homePage);
                }
            }
        }

        if (isset($the_url) && false === empty($the_url)) {
            $parsed_url  = parse_url($the_url);

            $the_url = str_replace('%%RANDOM%%', time().rand(0, 100), $the_url);
            $the_url = str_replace('[timestamp]', time(), $the_url);

            if (false == isset($parsed_url['scheme'])) {
                return "http://{$the_url}";
            }

            return $the_url;
        }

        return null;
    }

    public function getGeofence()
    {
        if (empty($this->maxGeofence) || false == isset($this->maxGeofence)) {
            return $this->brand_location->maxGeofence;
        }

        return $this->maxGeofence;
    }

    public static function formatLocationName($campaignName, $locationName)
    {
        //TODO: bug fix: The campaign name might have a location as well for example 'Cape Town' so the location will end up blank
        $name = str_ireplace(explode('~',str_replace(['_',' ','-',',','.','(',')'],'~',$campaignName)),'',$locationName);
        return trim($name);
    }
}
