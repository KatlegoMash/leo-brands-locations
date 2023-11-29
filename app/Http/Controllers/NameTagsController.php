<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\DB;
use App\Campaign;
use App\NameTags;
use App\Placement;
use App\CampaignPublisherNameTags;
use App\CampaignNameTags;
use App\PlacementNameTags;
use App\CampaignPlacementExclusion;
use App\LocationNameTags;
use App\BrandLocation;
use Illuminate\Support\Facades\Input;
use phpDocumentor\Reflection\Types\Collection;
use Illuminate\Http\Request;
use stdClass;

class NameTagsController extends Controller
{
    public function getIndex() {

        return view('tag-management.index');

    }

    public function getDefaultTags() {

        $obj = new stdClass;

        $tags = NameTags::orderBy('tag')->get();

        foreach($tags as &$tag) {
            $tag->placementCount = DB::select("SELECT count(1) as count FROM placement_name_tags JOIN placements ON placement_name_tags.zoneId = placements.zoneId WHERE placements.deleted_at IS NULL AND placement_name_tags.deleted_at IS NULL AND tag_id = ".(string)$tag->id)[0]->count;
            $tag->locationCount = DB::select("SELECT count(1) as count FROM location_name_tags JOIN brand_locations ON location_name_tags.locationId = brand_locations.id WHERE brand_locations.deleted_at is null AND location_name_tags.deleted_at IS NULL AND tag_id = ".(string)$tag->id)[0]->count;
            $tag->campaignCount = DB::select("SELECT count(1) as count FROM campaign_name_tags JOIN campaigns_details ON campaign_name_tags.campaignId = campaigns_details.campaignId WHERE campaigns_details.deleted_at IS NULL AND campaign_name_tags.deleted_at IS NULL AND tag_id = ".(string)$tag->id)[0]->count;
        }

        $obj->data = $tags;

        $obj = json_encode($obj);

        return $obj;

    }

    public function getTags() {
        $tags = NameTags::orderBy('tag')->get();

        return $tags;
    }

    public function getDistinctPlacementTags() {
        return NameTags::whereHas('placement_tags')->orderby('tag')->distinct()->get();
    }

    public function postSaveAndApplyTag(Request $request) {

        $tagName = $request->input('tagName');
        $locationIds = $request->input('locationIds');

        $tag = NameTags::firstOrCreate(['tag' => $tagName]);

        $tag->location_tags()->syncWithoutDetaching($locationIds);

        return response("success", 200);
    }

    public function getCampaignTags2()
    {
        $obj = new stdClass;

        $data = DB::table('name_tags')
                ->where('name_tags.deleted_at', null)
                ->join('campaign_name_tags', 'name_tags.id', '=', 'campaign_name_tags.tag_id')
                ->groupBy('name_tags.id')
                ->get();

        foreach($data as $item) {
            $item->count = CampaignNameTags::where('tag_id', $item->tag_id)->get()->count();
        }

        $obj->data = $data;

        $obj = json_encode($obj);

        return $obj;
    }

    public function getPlacementTags2() {

        $obj = new stdClass;

        $data = DB::table('name_tags')
                ->where('name_tags.deleted_at', null)
                ->join('placement_name_tags', 'name_tags.id', '=', 'placement_name_tags.tag_id')
                ->groupBy('name_tags.id')
                ->get();

        foreach($data as $item) {
            $item->count = PlacementNameTags::where('tag_id', $item->tag_id)->get()->count();
        }

        $obj->data = $data;

        $obj = json_encode($obj);

        return $obj;

    }

    public function getLocationTags2() {

        $data = DB::table('name_tags')
                ->where('name_tags.deleted_at', null)
                ->join('location_name_tags', 'name_tags.id', '=', 'location_name_tags.tag_id')
                ->orderBy('tag')
                ->get();

        return $data;

    }

    public function getLinkedLocations($tagId) {

        $locationIds = LocationNameTags::where('tag_id', $tagId)->get()->pluck('locationId');

        $locations = BrandLocation::whereIn('id', $locationIds)->get();

        $data = [];

        $data["data"] = $locations;

        return $data;

    }

    public function getLinkedCampaigns($tagId) {

        $campaignIds = CampaignNameTags::where('tag_id', $tagId)->get()->pluck('campaignId');

        $campaigns = Campaign::whereIn('campaignId', $campaignIds)->get();

        $data = [];

        $data["data"] = $campaigns;

        return $data;

    }

    public function getLinkedPlacements($tagId) {

        $zoneIds = PlacementNameTags::where('tag_id', $tagId)->get()->pluck('zoneId');

        $placements = Placement::whereIn('zoneId', $zoneIds)->get();

        $data = [];

        $data["data"] = $placements;

        return $data;

    }

    public function postUnlinkLocation(Request $request) {

        $locationIds = $request->get('locationIds');

        $tagId = $request->get('tagId');

        try {
            LocationNameTags::where('tag_id', $tagId)->whereIn('locationId', $locationIds)->delete();
        } catch (Exception $e) {
            return response('Unable to Unlink Location(s)', 500);
        }

        return response('Location(s) Unlinked', 200);

    }

    public function postUnlinkCampaign(Request $request) {

        $campaignIds = $request->get('campaignIds');

        $tagId = $request->get('tagId');

        try {
            CampaignNameTags::where('tag_id', $tagId)->whereIn('campaignId', $campaignIds)->delete();
        } catch (Exception $e) {
            return response('Unable to Unlink Campaign(s)', 500);
        }

        return response('Campaign(s) Unlinked', 200);
    }

    public function postUnlinkPlacement() {

        $zoneIds = Input::get('zoneIds');

        $tagId = Input::get('tagId');

        try {
            PlacementNameTags::where('tag_id', $tagId)->whereIn('zoneId', $zoneIds)->delete();
        } catch (Exception $e) {
            return response('Unable to Unlink Placement(s)', 500);
        }

        return response('Placement(s) Unlinked', 200);
    }

    public function postDeleteTag(Request $request) {

        $tagId = $request->get('tagId');

        //is tagId is not a number then return an error.
        if (!is_numeric($tagId)) {
            return response("Error with the tagId sent to be deleted.", 400);
        }

        //check tag exists
        $tag = NameTags::where('id', $tagId)->first();

        if ($tag == null) {
            return response("Error, the tagId sent to be deleted does not exist.", 400);
        }

        $statements = [
            "SELECT count(*) as count FROM placement_name_tags WHERE deleted_at is null AND tag_id = ".(string)$tagId, 
            "SELECT count(*) as count FROM location_name_tags WHERE deleted_at is null AND tag_id = ".(string)$tagId,
            "SELECT count(*) as count FROM campaign_name_tags WHERE deleted_at is null AND tag_id = ".(string)$tagId
        ];

        $noLinks = true;

        //check for any un deleted links
        foreach($statements as $statement) {
            $count = DB::select($statement)[0]->count;
            if ($count > 0) {
                $noLinks = false;
                break;
            }
        }

        //if a link is found do not delete
        if ($noLinks == false) {
            return response("Error, there still seems to be something linked to this Label.", 500);
        }

        //finall delete the tag.
        DB::table('name_tags')->where('id', $tagId)->delete();

        return response("Tag Deleted", 200);
    }


    public function getLinkedTags($campaignId) {

        $linkedTags = CampaignPublisherNameTags::where('campaignId', $campaignId)->get();

        $tagArr = [];
        foreach($linkedTags as $item) {
            $tagArr[] = $item->tag_id;
        }

        $tags = NameTags::whereIn('id', $tagArr)->orderBy('tag')->get();

        return $tags;
    }

    public function postCampaignsPlacementsExclude($campaignId) {

        $placementIds = Input::get('checkedPlacements');


        CampaignPlacementExclusion::where('campaignId', $campaignId)->delete();
        $count = 0;
        foreach ($placementIds as $item) {
            $tmp = new CampaignPlacementExclusion;
            $tmp->campaignId = $campaignId;
            $tmp->zoneId = $item;
            $tmp->save();
            $count++;
        }

        return $count;
    }

    public function postTagsPlacements() {

        $tags = Input::get('filters');

        $zoneIds = PlacementNameTags::whereIn('tag_id', $tags)->get();

        $zoneIdArr = [];
        foreach($zoneIds as $item) {
            array_push($zoneIdArr, $item->zoneId);
        }

        $placements = Placement::whereIn('zoneId', $zoneIdArr)->get()->keyBy("zoneId");

        return $placements;

    }

    public function postTagsPlacementsExcluded() {

        $campaignId = Input::get('campaignId');

        $excluded = CampaignPlacementExclusion::where('campaignId', $campaignId)->get()->keyBy("zoneId");

        return $excluded;

    }

    public function postTagsCampaigns($campaignId) {

        $selectedTags = Input::get("tags");
        $tags = NameTags::all();
        $checkedArr = [];
        $tagsCount = [];

        $checked = CampaignPublisherNameTags::where('campaignId', $campaignId)->get();

        $publisherTags = PlacementNameTags::all();

        $filters = $selectedTags;


        $tags = $tags->filter(function ($value, $key) use ($filters){
            $id = $value->id;
            if (in_array($id, $filters)) {
                return $value;
            }
        })->values();

        foreach($publisherTags as $item) {
            if (array_key_exists($item->tag_id, $tagsCount)) {
                $tagsCount[$item->tag_id] += 1;
            } else {
                $tagsCount[$item->tag_id] = 1;
            }
        }

        foreach ($checked as $item) {
            $checkedArr[] = $item->tag_id;
        }

        $obj = [];

        $obj["tags"] = $tags;
        $obj["checked"] = $checkedArr;
        $obj["tagsCounter"] = $tagsCount;

        return $obj;


    }

    public function postCampaignsTags($campaignId, Request $request){

        $tagIds = $request->input('checkedTagIds');
        $allTags = $request->input('tagIds');


        if ($tagIds != null) {

            CampaignPublisherNameTags::where('campaignId', $campaignId)
            ->whereIn('tag_id', $allTags)
            ->delete();

            foreach ($tagIds as $item) {
                $campaignNameTag = new CampaignPublisherNameTags;
                $campaignNameTag->campaignId = $campaignId;
                $campaignNameTag->tag_id = $item;
                $campaignNameTag->save();
            }
        } else {
            CampaignPublisherNameTags::where('campaignId', $campaignId)
            ->whereIn('tag_id', $allTags)
            ->delete();
        }


        $count = CampaignPublisherNameTags::where("campaignId", $campaignId)->get();

        $count = count($count);
        return $count;
    }


    public function anyPlacement($zoneId)
    {
        $placement = Placement::where('zoneId', $zoneId)->first();

        if($placement){
            if (request()->isMethod('post')) {
                $saver = "savePlacementTags";
                $tags = NameTags::{$saver}($zoneId, request()->all());
            }

            $getter = "getPlacementTags";
            $tags = NameTags::{$getter}($zoneId);

            return response()->json([
                'tags' => $tags
            ]);
        }

        return response()->json([
            'error' => 'No placement found!',
            'tags' => []
        ]);
    }

    public function anyLocation($brandLocationId)
    {
        $location = BrandLocation::where('id', $brandLocationId)->first();

        if($location){
            if (request()->isMethod('post')) {
                $saver = "saveLocationTags";
                $tags = NameTags::{$saver}($brandLocationId, request()->all());
            }

            $getter = "getLocationTags";
            $tags = NameTags::{$getter}($brandLocationId);

            return response()->json([
                'tags' => $tags
            ]);
        }

        return response()->json([
            'error' => 'No placement found!',
            'tags' => []
        ]);
    }

    public function anyCampaign($campaignId)
    {
        $campaign = Campaign::where('campaignId', $campaignId)->first();

        if($campaign){
            if (request()->isMethod('post')) {
                $saver = "saveCampaignTags";
                $tags = NameTags::{$saver}($campaignId, request()->all());
            }

            $getter = "getCampaignTags";
            $tags = NameTags::{$getter}($campaignId);

            return response()->json([
                'tags' => $tags
            ]);
        }


        return response()->json([
            'error' => 'No campaign found!',
            'tags' => []
        ]);
    }

    public function postCreateTag(Request $request) {

        $tagName = $request->get('tagName');

        if ($tagName == null || $tagName == '') {
            return response("Error, no Label Name submitted.", 400);
        }

        $tag = new NameTags;
        $tag->tag = $tagName;
        $tag->save();

        return response("Label successfully created", 200);

    }

    public function postEditTag(Request $request) {

        $tagName = $request->get('tagName');
        $id = $request->get('id');

        if ($tagName == null || $tagName == '') {
            return response("Error, no Label Name submitted.", 400);
        }

        if ($id == null || $id == '') {
            return response("Error, Label ID issue. Please speak to tech team.", 400);
        }

        $nameTag = NameTags::where('id', $id)->update(["tag" => $tagName]);

        return response("Succesfully renamed Label!", 200);

    }

    public function postCreatePlacementTag(Request $request) {

        $zoneIdArr = $request->get('zoneIdArr');
        $name = $request->get('tagName');

        if ($zoneIdArr == null || $name == null) {
            return "Request error, missing data.";
        }

        $response = PlacementNameTags::savePlacementsTagsMultiple($zoneIdArr, $name, 0);

        return $response;

    }

    //this will update already linked locations and create new links for new linked locations.
    public function postLinkLocations(Request $request) {

        $tag_id = $request->get('tag_id');

        $locationIdArr = $request->get('locationIdArr');

        if ($tag_id == null || $locationIdArr == null) {
            return "Request error, missing data.";
        }

        //update already created rows.
        $updateLocations = DB::table('location_name_tags')->where('tag_id', $tag_id)->whereIn('locationId', $locationIdArr)->get()->pluck('locationId')->toArray();

        DB::transaction(function () use($locationIdArr, $tag_id, $updateLocations) {

            $insert = [];
            $date = date('Y-m-d H:i:s');
            $updateArr = [];

            foreach($locationIdArr as $item) {
                if (!in_array($item, $updateLocations)) {
                    $insert[] = [
                        "tag_id"=>$tag_id,
                        "locationId"=>$item,
                        'created_at' => $date,
                        'updated_at' => $date,
                        'deleted_at' => null
                    ];
                } else {
                    $updateArr[] = $item;
                }
            }

            DB::table('location_name_tags')->whereIn('locationId', $updateArr)->where('tag_id', $tag_id)->update(['deleted_at' => null, 'updated_at' => $date]);
            DB::table('location_name_tags')->insert($insert);

        });

        return response("Locations succesfully linked", 200);

    }

    public function postLinkPlacements(Request $request) {

        $tag_id = $request->get('tag_id');

        $zoneIdArr = $request->get('zoneIdArr');

        if ($tag_id == null || $zoneIdArr == null) {
            return "Request error, missing data.";
        }

        //update already created rows.
        $updatePlacements = DB::table('placement_name_tags')->where('tag_id', $tag_id)->whereIn('zoneId', $zoneIdArr)->get()->pluck('zoneId')->toArray();

        DB::transaction(function () use($zoneIdArr, $tag_id, $updatePlacements) {

            $insert = [];
            $date = date('Y-m-d H:i:s');
            $updateArr = [];
            
            foreach($zoneIdArr as $item) {
                if (!in_array($item, $updatePlacements)) {
                    $insert[] = [
                        "tag_id"=>$tag_id,
                        "zoneId"=>$item,
                        'created_at' => $date,
                        'updated_at' => $date,
                        'deleted_at' => null
                    ];
                } else {
                    $updateArr[] = $item;
                }
            }

            DB::table('placement_name_tags')->whereIn('zoneId', $updateArr)->where('tag_id', $tag_id)->update(['deleted_at' => null, 'updated_at' => $date]);
            DB::table('placement_name_tags')->insert($insert);

        });

        return response("Placements succesfully linked", 200);
    }

    public function postLinkCampaigns(Request $request) {

        $tag_id = $request->get('tag_id');

        $campaignIdArr = $request->get('campaignIdArr');

        if ($tag_id == null || $campaignIdArr == null) {
            return "Request error, missing data.";
        }

        //update already created rows.
        $updateCampaigns = DB::table('campaign_name_tags')->where('tag_id', $tag_id)->whereIn('campaignId', $campaignIdArr)->get()->pluck('campaignId')->toArray();

        DB::transaction(function () use($campaignIdArr, $tag_id, $updateCampaigns) {

            $insert = [];
            $date = date('Y-m-d H:i:s');
            $updateArr = [];

            foreach($campaignIdArr as $item) {
                if (!in_array($item, $updateCampaigns)) {
                    $insert[] = [
                        "tag_id"=>$tag_id,
                        "campaignId"=>$item,
                        'created_at' => $date,
                        'updated_at' => $date,
                        'deleted_at' => null
                    ];
                } else {
                    $updateArr[] = $item;
                }
                
            }

            DB::table('campaign_name_tags')->whereIn('campaignId', $updateArr)->where('tag_id', $tag_id)->update(['deleted_at' => null, 'updated_at' => $date]);
            DB::table('campaign_name_tags')->insert($insert);

        });

        return response("Campaigns succesfully linked", 200);

    }

    /**
     * Usage:
     * Dashboard => Publisher Statistics Tab
     * Tag Filters Dropdown
     *
     * @return Collection
     */
    public function getPlacementTags()
    {
        return NameTags::getNameTags('placements');
    }

    /**
     * @return Collection
     */
    public function getCampaignTags()
    {
        return NameTags::getNameTags('campaigns');
    }

    public function getBannerTags()
    {
        return NameTags::getNameTags('banner');
    }
}
