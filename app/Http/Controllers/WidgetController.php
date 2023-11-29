<?php

namespace App\Http\Controllers;

use App\WidgetCategories;
use App\ContentCategory;
use App\StoredWidgetCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use App\Services\WidgetCategoryMappingService;
use App\GooglePlaceType;
use App\User;
use App\WidgetCategoriesContentCategories;

class WidgetController extends Controller
{
    protected static $widget_management_disabled = true;


    public function postLinkedCategories (Request $request)
    {
      $brandLocID = $request->get('id');
      $linkedData = DB::table('widget_categories_brand_location')
      ->join('brand_locations','widget_categories_brand_location.brand_location_id','=','brand_locations.id')
      ->whereIn('brand_location_id', $brandLocID)
      ->get();

      return Response::json($linkedData);
    }

    public function postUnlinkCategories(Request $request)
    {
      $categories = $request->input('categoryIds');
      $locations = $request->input('locationIds');

      if($categories == 0){
        die();
      }
      StoredWidgetCategories::whereIn("categoriesId",$categories)
                              ->whereIn("brand_location_id",$locations)
                              ->delete();

      return Response::json($categories);
    }

    public function postAddCategories(Request $request)
    {
      $categories = explode(",",$request->input('categoryIds'));
      $locations = explode(",",$request->input('locationIds'));

      $insertData = [];

        foreach($locations as $location){
          foreach($categories as $category){
            if($category == 0){
              die();
            }
            $insertData[] = [
              "categoriesId"=>(int) $category,
              "brand_location_id"=>(int) $location,
            ];
          }
        }


        StoredWidgetCategories::whereIn("brand_location_id",$locations)->delete();
        StoredWidgetCategories::insert($insertData);
        return Response::json($insertData);

    }

    public static function getWeatherDetails($lat,$lon){

        $client = new Client();

        $weatherDetails = $client->request('GET', "http://pro.openweathermap.org/data/2.5/onecall?lat=".$lat."&lon=".$lon."&exclude=minutely"."&appid=2c3be00f2c4a4ff37c58896f134ade64");
        $weatherData = json_decode($weatherDetails->getBody()->getContents());

        $apiData = collect($weatherData);

        return $apiData;
    }

    public function postUpdateCategoryGeofence($id, Request $request) {
      $category = WidgetCategories::find($id);
      $category->geofence = $request->input("geofence");
      $category->save();
    }

    public function getIndex()
    {
        $widgetCategories = WidgetCategories::withCount('linkedLocations')->with("serving_times")->get();
        return view('widget-category.index', ['widgetCategories' => $widgetCategories]);
    }

    public function getMapping()
    {
        $data['empty_map'] = WidgetCategoryMappingService::getEmptyAssocArray(
            WidgetCategoryMappingService::getWidgetCategoriesWithIcons()
        );

        $data['google_places'] = GooglePlaceType::get()->pluck('name', 'id')->toArray();
        $data['map_widget_categories'] = WidgetCategories::has('google_place_types')->get();

        return view('widget-category.mapping', $data);
    }

    public function postMappingUpdate(Request $request)
    {
        if (!in_array(Auth::id(), User::$mappingPageUserIds)) return redirect()->back()->with('message', "User not authorised to make changes");

        $validator = Validator::make($request->all(), [
            'categoriesId' => 'required|integer',
            'placetypeIds' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->messages()->first());
            return redirect()->back()->withInput();
        }

        WidgetCategories::with('google_place_types')->find($request->get('categoriesId'))
            ->google_place_types()
            ->sync($request->get('placetypeIds'));

        DB::table('google_place_type_widget_category')
            ->where('widget_category_id', (int) $request->get('categoriesId'))
            ->where('google_place_type_id', $request->get('placetypeIds')[0])
            ->update(['preferred' => true]);

        return redirect()->back()->with('message', "Update was successful");
    }

    public function postRemoveMapping(Request $request)
    {
        if (!in_array(Auth::id(), User::$mappingPageUserIds)) return redirect()->back()->with('message', "User not authorised to make changes");

        $validator = Validator::make($request->all(), [
            'categoriesId' => 'required|array'
        ]);
   
        if ($validator->fails()) {
            Session::flash('error', $validator->messages()->first());
            return redirect()->back()->withInput();
        }

        foreach($request->get('categoriesId') as $categoryId){
            WidgetCategories::with('google_place_types')->find($categoryId)
            ->google_place_types()
            ->sync([]);
        }

        return redirect()->back()->with('message', "Mapping Successfully removed");
    }

    public function postSetPreferred(Request $request)
    {
        if (!in_array(Auth::id(), User::$mappingPageUserIds)) return response()->json([
            "error" => "Not Authorized",
            "message" => "You are not authorised to make changes on this section"
        ], 400);

        $widgetCategoryId = $request->widgetCategoryId;
        $googleTypeId = $request->googleTypeId;

        DB::table('google_place_type_widget_category')
            ->where('widget_category_id', (int) $widgetCategoryId)
            ->update(['preferred' => false]);

        DB::table('google_place_type_widget_category')
            ->where('widget_category_id', (int) $widgetCategoryId)
            ->where('google_place_type_id', $googleTypeId)
            ->update(['preferred' => true]);

        return response()->json([
            'error' => null,
            "message" => "Preferred Google Type Updated"
        ]);
    }

    public function getWidgetCategoryGoogleTypes($widgetCategoryId)
    {
        $widgetCategory = WidgetCategories::find($widgetCategoryId);
        $typeIds = [];
        if ($widgetCategory) {
            $typeIds = $widgetCategory->google_place_types->pluck('id');
        }

        return response()->json([
            "error" => null,
            "message" => "Selected Types for this category",
            "googleTypesIds" => $typeIds
        ]);
    }

    public function postStoreServingTimes(Request $request)
    {
        if (static::$widget_management_disabled) {
            return response()->json([
                'message' => "Due to bad user experience this endpoint has been temporarily disabled. We apologize for any inconvenience."
            ], 501);
        }

        if (!in_array(Auth::id(), User::$mappingPageUserIds)) return response()->json([
            "error" => "Not Authorized",
            "message" => "You are not authorised to make changes on this section"
        ], 400);

        $day =  $request->dayOfTheWeek;

        if ($day == "everyday") {
            DB::table('widget_categories_serving_times')->where('category_id', $request->categoryId)->where('day', '!=', $day)->delete();
            DB::table('widget_categories_serving_times')->updateOrInsert([
                'category_id' => $request->categoryId,
            ], [
                'category_id' => $request->categoryId,
                'servingType' => $request->time,
                'day' => $day,
                'created_at' => now()
            ]);

            return response()->json([
                'error' => null,
                "message" => "Category Serving Time saved. Category will serve everyday on this time range $request->time"
            ]);
        }

        DB::table('widget_categories_serving_times')->where('category_id', $request->categoryId)->where('day', "everyday")->delete();
        DB::table('widget_categories_serving_times')->updateOrInsert([
            'category_id' => $request->categoryId,
            'day' => $day,
        ], [
            'category_id' => $request->categoryId,
            'servingType' => 'custom',
            'day' => $day,
            "$request->name" => empty($request->time) ?  null : $request->time, 
            'created_at' => now()
        ]);

        return response()->json([
            'error' => null,
            'message' => "Serving times stored"
        ]);
    }

    public function postUseOnNearme(Request $request)
    {
        if (static::$widget_management_disabled) {
            return response()->json([
                'message' => "Due to bad user experience this endpoint has been temporarily disabled. We apologize for any inconvenience."
            ], 501);
        }

        $widgetCategory = WidgetCategories::where(['id' => $request->categoryId])->update([ 'use_on_nearme' => $request->useOnNearme ]);
        return response()->json([
            'error' => null,
            'widgetCategory' => $widgetCategory,
            'use_on_nearme' => $request->useOnNearme,
            'id' => $request->categoryId,
            'message' => "Received changes on use on nearme column"
        ]);
    }

    public function getInMarketMappingView(){
        $widgetCategories = WidgetCategories::get();
        $contentCategories = ContentCategory::get();
        $mapping = WidgetCategoriesContentCategories::get();

        return view('widget-category.widget-content-category-mapping', compact('widgetCategories','contentCategories', 'mapping'));
    }

    public function postInMarketMapping(Request $request)
    {
        $widget_category_id = $request->get('widget-categories');
        $content_category_id = $request->get('content-categories');

        if (!WidgetCategoriesContentCategories::where('widget_category_id', $widget_category_id)->where('content_category_id',$content_category_id)->exists()) {

            WidgetCategoriesContentCategories::insert([
                'widget_category_id' => $widget_category_id,
                'content_category_id' => $content_category_id
            ]);   
        }
            
        return Redirect::back();
    }

    public function deleteInMarketMapping(Request $request){

        WidgetCategoriesContentCategories::find($request->id)->delete();

        return response()->json([
            'error' => null,
            'message' => 'Mapping Removed'
        ]);
    }

}
