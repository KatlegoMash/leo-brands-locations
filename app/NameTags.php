<?php

namespace App;

use Exception;
use App\CampaignNameTags;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NameTags extends Model
{
    use SoftDeletes;

    protected $table = 'name_tags';

    protected $fillable = [
        "tag"
    ];

    protected $hidden = [
        "created_at",
        "updated_at",
        "deleted_at"
    ];

    private static $relatedTables = [
        'campaigns' => ['table' => 'campaign_name_tags', 'idColumn' => 'campaignId', 'type' => 'campaign'],
        'placements' => ['table' => 'placement_name_tags', 'idColumn' => 'zoneId', 'type' => 'placement'],
        'locations' => ['table' => 'location_name_tags', 'idColumn' => 'locationId', 'type' => 'location'],
        // 'banners' => ['table' => 'banner_name_tags', 'idColumn' => 'bannerId', 'type' => 'banner'] // Future table maybe
    ];

    public static function saveTag($tag)
    {
        $date = date('Y-m-d H:i:s');

        $updated = DB::table('name_tags')
            ->where('tag', trim($tag['tag']))
            ->update(['updated_at' => $date, 'deleted_at' => null]);

        if(!$updated){
            $tag['id'] = DB::table('name_tags')->insertGetId(
                [
                    'tag' => trim($tag['tag']),
                    'created_at' => $date,
                    'updated_at' => $date,
                    'deleted_at' => null
                ]
            );
        } else {
            $t = DB::table('name_tags')->where('tag', trim($tag['tag']))->first();

            if($t){
                $tag['id'] = $t->id;
            }
        }

        return $tag;
    }

    public static function saveCampaignTags($campaignId, $tags)
    {
        return self::saveRelatedTags('campaign_name_tags', 'campaignId', $campaignId, $tags);
    }

    public static function savePlacementTags($zoneId, $tags)
    {
        return self::saveRelatedTags('placement_name_tags', 'zoneId', $zoneId, $tags);
    }

    public static function saveLocationTags($brandLocationId, $tags)
    {
        return self::saveRelatedTags('location_name_tags', 'locationId', $brandLocationId, $tags);
    }

    private static function saveRelatedTags($table, $idColumn, $id, $tags)
    {
        $date = date('Y-m-d H:i:s');

        DB::beginTransaction();
        try{
            DB::table($table)->where($idColumn, $id)->update(['deleted_at' => $date]);

            foreach($tags as $tag){
                if($tag['id'] <= 0){
                    $tag = self::saveTag($tag);
                }
                $updated = DB::table($table)
                    ->where($idColumn, $id)
                    ->where('tag_id', $tag['id'])
                    ->update(['updated_at' => $date, 'deleted_at' => null]);
                if(!$updated){
                    $insert = [
                        $idColumn => $id,
                        'tag_id' => $tag['id'],
                        'created_at' => $date,
                        'updated_at' => $date,
                        'deleted_at' => null
                    ];
                    DB::table($table)->insert($insert);
                }
            }

            DB::commit();
        }catch(Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public static function getCampaignTags($id)
    {
        return self::getLinkedTags('campaigns', $id);
    }

    public static function getPlacementTags($id)
    {
        return self::getLinkedTags('placements', $id);
    }

    public static function getLocationTags($id) 
    {
        return self::getLinkedTags('locations', $id);
    }

    public static function getNameTags($type)
    {
        if ($type == 'placements') {
            $placementTags=NameTags::whereHas('placement_tags')->orderby('tag')->distinct()->get();
            return $placementTags;
        }
        else if ($type == 'campaigns') {
            $campaignTags = NameTags::whereHas('campaign_tags')->orderby('tag')->distinct()->get();
             return $campaignTags; 
                
        }
        else if ($type == 'locations') {
            $locationTags = NameTags::whereHas('location_tags')->orderby('tag')->distinct()->get();
            return $locationTags;
        }
    }

    public static function getRelatedFromTags($tags, $type = 'all')
    {
        $tagIds = array_map(function($tag){
            return $tag['id'] ?? $tag->id;
        }, $tags);

        $tables = self::$relatedTables;

        $fnLinkedTagsQuery = function(array $tableMeta, array $tagIds){
            return DB::table($tableMeta['table'])
                ->select("{$tableMeta['table']}.{$tableMeta['idColumn']}", DB::raw("'{$tableMeta['type']}' AS type"))
                ->join('name_tags', 'name_tags.id', '=', "{$tableMeta['table']}.tag_id")
                ->whereIn('tag_id', $tagIds)
                ->whereNull('name_tags.deleted_at')
                ->whereNull("{$tableMeta['table']}.deleted_at");
        };

        if( isset($tables[$type]) ){
            $linkedTags = call_user_func($fnLinkedTagsQuery, $tables[$type], $tagIds);
        } else {
            $table = array_pop($tables);
            $linkedTags = call_user_func($fnLinkedTagsQuery, $table, $tagIds);
            foreach($tables as $table){
                $query  = call_user_func($fnLinkedTagsQuery, $table, $tagIds);
                $linkedTags = $linkedTags->union($query);
            }
        }

        return $linkedTags->get();
    }

    public static function getLinkedTags($type='all', $id=null)
    {
        if($type=='all' && isset($id)){
            throw new Exception('If an ID is given please specify the related type.');
        }

        $tables = self::$relatedTables;

        $fnLinkedTagsQuery = function(array $tableMeta, $id=null){
            $query = DB::table('name_tags')
                ->select('name_tags.id','name_tags.tag')
                ->distinct()
                ->join("{$tableMeta['table']}", 'name_tags.id', '=', "{$tableMeta['table']}.tag_id")
                ->whereNull('name_tags.deleted_at')
                ->whereNull("{$tableMeta['table']}.deleted_at");

            if(isset($id)){
                $query = $query->where("{$tableMeta['table']}.{$tableMeta['idColumn']}", $id);
            }

            return $query;
        };

        if( isset($tables[$type]) ){
            $linkedTags = call_user_func($fnLinkedTagsQuery, $tables[$type], $id);
        } else {
            $table = array_pop($tables);
            $linkedTags = call_user_func($fnLinkedTagsQuery, $table);
            foreach($tables as $table){
                $query = call_user_func($fnLinkedTagsQuery, $table);
                $linkedTags = $linkedTags->union($query);
            }
        }
        return $linkedTags->get();
    }

    public function campaign_tags()
    {
        return $this->hasMany(CampaignNameTags::class,'tag_id');
    }
    
    public function placement_tags()
    {
        return $this->hasMany(PlacementNameTags::class,'tag_id');
    }

    public function location_tags() {
        return $this->belongsToMany('App\BrandLocation', 'location_name_tags', 'tag_id', 'locationId')->withTimestamps();
    }
   
}
