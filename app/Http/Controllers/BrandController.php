<?php

namespace App\Http\Controllers;

use App\Brand;
use App\MakroFeed;
use App\BrandVisitScore;
use App\WidgetCategories;
use App\BrandContentCategory;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use App\ContentCategory;
use App\Classes\ExtendCollections;


class BrandController extends Controller
{
    public function getBrand($id)
    {
        $brand = Brand::where('id', $id)->first();
        return $brand->contacts(false);
    }

    public function getSelectedBrands($campignId = null)
    {
        $campaignId = Input::input('campaignId');
        $selectedBrands = DB::table('campaign_location')
            ->select('brands.id as brandId', 'brands.brandName as brandName', DB::raw('COUNT(*) as num_locations'))
            ->join('brand_locations', 'brand_locations.id', '=', 'campaign_location.brandLocationId')
            ->join('brands', 'brands.id', '=', 'brandId')
            ->where('campaignId', $campignId)
            ->groupBy('brand_locations.brandId')
            ->get();

        return Response::json($selectedBrands);
    }

    public function getOverlapDataGraph($brandId)
    {

        $brandAudience = DB::select("
            select brandId as id, brandName,group_concat(distinct widget_categories.categoriesName) as sectors from widget_categories_brand_location
            join brand_locations on widget_categories_brand_location.brand_location_id = brand_locations.id
            join brands on brands.id = brand_locations.brandId
            join widget_categories on widget_categories.id =   widget_categories_brand_location.categoriesId
            where brands.deleted_at is null and brandName not like '%Bozza%' and brandId = $brandId
            group by  brandName,categoriesName
        ");

        $collection = [
            ''
        ];

        return view('brands.overlap-data-graph', compact('collection', 'brandAudience'));
    }

    public function getOverlapData($brandId)
    {

        $brandAudience = DB::select("
            select brandId as id, brandName,group_concat(distinct widget_categories.categoriesName) as sectors from widget_categories_brand_location
            join brand_locations on widget_categories_brand_location.brand_location_id = brand_locations.id
            join brands on brands.id = brand_locations.brandId
            join widget_categories on widget_categories.id =   widget_categories_brand_location.categoriesId
            where brands.deleted_at is null and brandName not like '%Bozza%' and brandId = $brandId
            group by  brandName
        ");
        $brandAudience = $brandAudience[0] ?? null;

        return view('brands.overlap-data', compact('brandAudience'));
    }

    //GET brands/edit
    public function getEdit()
    {
        $brand = Brand::with('advertiser')->get();
        return view('brands.edit')->with('method', 'post', 'brand');
    }

    //GET brands/ reusing getIndex to return json and view depending on the request
    public function getIndex(Request $request)
    {
        if($request->expectsJson()){
            return response()->json([
                'error' => null,
                'message' => 'All Active Brands',
                'brands' => Brand::orderBy('brandName', 'ASC')->get(),
            ]);
        }else{
            $content_categories = ContentCategory::largeIds() // local scope in action
                ->with('childrenCategories')
                ->whereNull('parent_id')
                ->get();

            return view('brands.index')->with('content_categories', $content_categories);
        }
    }

    public function getWidgetCategories(){
        $categories = WidgetCategories::orderBy('categoriesName')->get();
        return $categories;
    }

    public function postBrandWidgetCategories(){
        $excludeBrandIds = [1, 1825];
        $brandId = Input::get('brandId'); //Brand Name

        if(in_array($brandId, $excludeBrandIds)) return response()->json([
            "error" => "Excluded Brands",
            "message" => "This brand is exluded from this feature"
        ]);

        $categories = Input::get('categories'); //Widget Categories

        $locations = DB::table('brands')->select('brands.id as originalBrandId','brandName', 'brand_locations.id as brandLocationId', 'brand_locations.locationName')->rightJoin('brand_locations', 'brands.id', '=', 'brand_locations.brandId')->where('brands.id', $brandId)->get();
        $locationIds = $locations->pluck('brandLocationId')->toArray();

        DB::table('widget_categories_brand_location')->whereIn('brand_location_id', $locationIds)->delete();

        if(!empty($categories)){
            foreach($categories as $categoryId){
                $category = WidgetCategories::find($categoryId);
                $category->storedCategories()->attach($locationIds);
            }
        }

        return response()->json([
            "error" => null,
            "message" => "Brand category updated"
        ]);

    }

    public function postBrandContentCategories()
    {
        $selectedBrands = Input::get('brandIds');
        $selectedContentCategries = Input::get('contentCategories');

        $brands = Brand::whereIn('id', $selectedBrands)->with('content_categories')->get();
        $contentCategories = ContentCategory::whereIn('id', $selectedContentCategries)->get();

        if(!$brands || empty($brands)){
            return response()->json([
                'error' => "No Brands Found",
                "message" => "Please select valid brands to classify"
            ]);
        }

        if(!$contentCategories || empty($contentCategories)){
            return response()->json([
                'error' => "No Content Categories Found",
                "message" => "Please select valid content categories to classify brands"
            ]);
        }

        foreach($brands as $brand){
            $brand->content_categories()->sync($contentCategories);
        }

        return response()->json([
            'error' => null,
            'message' => "Brands Classified Successfully"
        ]);

    }

    public function getBrandContentCategories()
    {
        $brandIds = explode(",", Input::get('selectedBrands'));
        $brands = Brand::whereIn('id', $brandIds)->with('content_categories')->get();

        return response()->json([
            "error" => null,
            "message" => "Content Categories Retrieved",
            "brands" => $brands,
        ]);
    }

    //GET brands/
    public function getOverlap()
    {
        $brands = Brand::orderBy('brandName')->get();
        return view('brands.overlap', compact('brands'));
    }

    //POST brands/new
    public function postNew()
    {
        $visits_yn = 0;
        if (Input::get('visits_yn') !== null) {
            $visits_yn = 1;
        }

        $use_nearme_yn = 0;
        if (Input::get('use_nearme_yn') !== null) {
            $use_nearme_yn = 1;
        }

        $broadsign_yn = 0;
        if (Input::get('broadsign_yn') !== null) {
            $broadsign_yn = 1;
        }

        $brand = new Brand;
        $valid = $brand->validate(Input::except('_token'));
        
        $capacity = (Input::get('maximumCapacity')) ? Input::get('maximumCapacity') : 0;
        $geofence = (Input::get('geofence')) ? Input::get('geofence') : 0;

        $brandData = [
            'name' => Input::get('brandName'),
            'brandUrl' => Input::get('brandUrlNew'),
            'visits_yn' => $visits_yn,
            'use_nearme_yn' => $use_nearme_yn,
            'broadsign_yn' => $broadsign_yn,
            'geofence' => $geofence,
            'maximum_capacity' => $capacity,
            'advertiserId' => Input::get('advertiserId'),
            'clientId' => Input::get('locationBankClientId'),
            'contacts' => [
                [
                    'type' => 'billing',
                    'name' => Input::get('billingName'),
                    'email' => Input::get('billingEmail'),
                    'phone' => Input::get('billingTel'),
                ],
                [
                    'type' => 'reporting',
                    'name' => Input::get('reportingName'),
                    'email' => Input::get('reportingEmail'),
                    'phone' => Input::get('reportingTel'),
                ],
                [
                    'type' => 'creatives',
                    'name' => Input::get('creativeName'),
                    'email' => Input::get('creativeEmail'),
                    'phone' => Input::get('creativeTel'),
                ]
            ],
        ];
        
        try {
            Brand::createOrUpdateBrand($brand, $brandData);
        } catch (Exception $e) {
            Input::flash();
            return view('brands.edit')
                //->with('cat', $cat)
                ->with('method', 'post')
                ->withErrors($valid);
        }

        return redirect('brands');
    }

    public function getSimpleBrands() {

        $brands = Brand::where('deleted_at', null)->orderBy('brandName')->get();

        return response()->json($brands);
    }

    //POST brands/json
    public function getJson()
    {
        $grapData = [];

        $dataJoined = DB::table('brands')
            ->select(DB::raw('brands.*,id as brandId,0 as total_locations'))
            // ->leftJoin('brand_locations', 'brand_locations.brandId', '=', 'brands.id')
            ->where('brands.deleted_at',null)
            // ->where('brand_locations.deleted_at',null)
            // ->groupBy('brand_locations.brandId')
            ->get();

        $locations_count = \App\BrandLocation::select(DB::raw('brandId, count(*) as total'))->where('deleted_at', null)->groupBy("brandId")->get()->pluck('total','brandId');

        $campaignBrands = [];

        $sql = "SELECT brands.id,GROUP_CONCAT(DISTINCT categoriesId) as brandSelectedCategories, group_concat(distinct categoriesName) as categoriesName FROM brands join brand_locations on brand_locations.brandId = brands.id join widget_categories_brand_location on widget_categories_brand_location.brand_location_id = brand_locations.id join widget_categories on widget_categories.id = categoriesId  group by brands.id";
        $selectedCategs = DB::select($sql);
       
        $brandSelectedCategs = [];

        foreach($selectedCategs as $brandCategs){
            $brandSelectedCategs[$brandCategs->id]['id'] = $brandCategs->brandSelectedCategories;
            $brandSelectedCategs[$brandCategs->id]['name'] = $brandCategs->categoriesName;
        }

        foreach ($dataJoined as $key => &$value) {
            $fields = [];

            $inMarketBrand = BrandContentCategory::where('brand_id', $value->id)->get();
            $in_Market_Brand_Classification = [];
    
            foreach ($inMarketBrand as $inMarket) {
                $contentCategoryId = $inMarket->content_category_id;
                $inMarketClassification = ContentCategory::where('id', $contentCategoryId)->pluck('name')->toArray();
    
                $in_Market_Brand_Classification[] = implode(', ', $inMarketClassification);
            }
    
            $value->In_Market_Brand_Classification = implode(', ', $in_Market_Brand_Classification);
            if($value->brandName){
                $value->brandData = [
                    'brandName' => $value->brandName,
                    'brandId' => $value->brandId
                ];
            }

            if (count($fields)) {

                $htmlData = count($fields) . " Campaigns Linked: <a onclick='$(\"#data-$key\").toggle()' nohref='' style='cursor:pointer'>Show Campaigns</a>";
                $htmlData .= "<div style='display: none' id='data-{$key}'>";
                $htmlData .= implode('<br/>', array_column($fields, 'campaignName'));
                $htmlData .= "</div>";
                $value->linked_campaigns = $htmlData;
            } else {
                $value->linked_campaigns = 'None';
            }


            $value->location_bank_data = "No data";

            $value->total_locations = $locations_count[$value->id] ?? 0;

            if ($value->visits_yn == 0) {
                $html = '<input name="visits_yn" class="checkCounter form-check-input checkFunc" type="checkbox"/>';
            } else {
                $html = '<input name="visits_yn" class="checkCounter form-check-input checkFunc" type="checkbox" checked/>';
            }
            $value->visits_yn = $html;

            if ($value->use_nearme_yn == 0) {
                $html = '<input name="use_nearme_yn" class="checkCounter form-check-input checkFunc" type="checkbox"/>';
            } else {
                $html = '<input name="use_nearme_yn" class="checkCounter form-check-input checkFunc" type="checkbox" checked/>';
            }

            $value->use_nearme_yn = [
                'html' => $html,
                'value' => $value->use_nearme_yn,
            ];

            if ($value->broadsign_yn == 0) {
                $html = '<input name="broadsign_yn" class="checkCounter form-check-input checkFunc" type="checkbox"/>';
            } else {
                $html = '<input name="broadsign_yn" class="checkCounter form-check-input checkFunc" type="checkbox" checked/>';
            }
            $value->broadsign_yn = $html;

            //This is needed for the client to display on Datatable
            if(isset($brand->advertiser->advertiserName)){
                $value->client = $brand->advertiser->advertiserName ;
            }else{
                $value->client = "Unknown Client";
            }


            $categories = [
                'array' =>[],
                'sortData' => "",
                'names'=>""
            ];

            if(!empty($brandSelectedCategs[$value->id])){
                $categories = [
                    'array' => array_map('intval', explode(',', $brandSelectedCategs[$value->id]['id'])),
                    'sortData' => $brandSelectedCategs[$value->id]['id'],
                    "names"=> $brandSelectedCategs[$value->id]['name']
                ];
            }


            $value->selectedCategories = $categories;
        }

        $brandRecords = [];
        $brand_record = null;

        $brandRecords = array_values($brandRecords);

        $recordsTotal = count($dataJoined);

        $data['draw'] = 1;
        $data['recordsTotal'] = $recordsTotal;
        $data['recordsFiltered'] = $recordsTotal;
        $data['data'] = $dataJoined;

        return response()->json($data);
    }

    public function postUpdate($id)
    {
        $brand = Brand::where('id', $id)->first();

        $visits_yn = 0;
        if (Input::get('visits_yn') == "true") {
            $visits_yn = 1;
        }

        $use_nearme_yn = 0;
        if (Input::get('use_nearme_yn') == "true") {
            $use_nearme_yn = 1;
        }

        $broadsign_yn = 0;
        if (Input::get('broadsign_yn') == "true") {
            $broadsign_yn = 1;
        }

        //This needs to be re-written
        $brandInput = [
            'brandName' => Input::get('brandName'),
            'brandUrl' => Input::get('brandUrlUpdate'),
            'visits_yn' => $visits_yn,
            'use_nearme_yn' => $use_nearme_yn,
            'broadsign_yn' => $broadsign_yn,
            'geofence' => Input::get('geofence'),
            'maximum_capacity' => Input::get('maximum_capacity'),
            'clientId' => Input::get('locationBankClientId'),
            'advertiserId' => $brand->advertiserId,
            'contacts' => [
                [
                    'type' => 'billing',
                    'name' => Input::get('billingContactFirstName'),
                    'email' => Input::get('billingContactEmail'),
                    'phone' => Input::get('billingContactPhone'),
                ],
                [
                    'type' => 'reporting',
                    'name' => Input::get('reportingContactFirstName'),
                    'email' => Input::get('reportingContactEmail'),
                    'phone' => Input::get('reportingContactPhone'),
                ],
                [
                    'type' => 'creatives',
                    'name' => Input::get('creativesContactFirstName'),
                    'email' => Input::get('creativesContactEmail'),
                    'phone' => Input::get('creativesContactPhone'),
                ]
            ],
        ];

        //only update/create if contact billing info exists
        if (Input::get('billingContactFirstName')) {
            Brand::createOrUpdateBrand($brand, $brandInput);
        }else {
            //otherwise update if changes are found in $brandInput array
            $brand->update($brandInput);
        }

        return Response::json([
            'message' => 'update succeeded',
            'success' => true
        ]);
    }

    public function postUpdateCheckBoxes($id)
    {

        $brand = Brand::where('id', $id)->first();
        if($brand){

            DB::table('brands')->where('id', $id)->update([
                Input::get('field') => Input::get('value')
            ]);

            return response()->json([
                'message' => 'Brand successfully updated'
            ]);
        }else{
            return response()->json([
                'error' => "Update unsuccessful - Brand not found."
            ]);
        }
    }

    //DELETE brands/brand/123
    public function deleteBrand($id)
    {
        $brand = Brand::find($id);
        if ($brand) {
            $result = $brand->delete();

            return Response::json([
                'message' => $result ? 'delete succeeded' : 'delete failed',
                'success' => $result
            ], 200);
        }

        return Response::json([
            'message' => 'Not Found',
            'success' => false
        ], 404);
    }

    public function getSearch($q)
    {
        $searchTerms = explode(' ', $q);

        $query = DB::table('brands');

        foreach ($searchTerms as $term) {
            $query->where('brandName', 'LIKE', '%' . $term . '%');
            $query->where('deleted_at', null);
        }

        $results = $query->get();
        return count($results);
    }

    public function getQueueDuplicatesReport()
    {
        if ( \Config::get('app.debug') ) {
            set_time_limit(300); //5 mins
        }

        $data = [
            'userId' => Input::get('userId'),
            'brandIds' => Input::get('brandId')
        ];

        Artisan::call("duplicates:check-duplicates",
        [   "--brandIds"    =>  $data['brandIds'],
            "--userId"      =>  $data['userId']
        ]);

        //Todo: removing event queue approach for now
        //event(new \App\Events\RunDuplicateCheckerScheduler($data));

        return response()->json([
            'error' => null,
            'message' => "Your request has been received and please allow some time for the server to generate and email your report",
        ]);
    }

    public function postBrandsImage(Request $request)
    {
        $brandData = $this->validate($request, [
                'brandLogoId' => 'required',
                'filenames' => 'required',
                'filenames.*' => 'mimes:png,jpeg,jpg'
        ]);
        
        if($request->hasfile('filenames'))
         {
            foreach($request->file('filenames') as $file)
            {
                
                $name = time().'.'.$file->extension();
                $path = public_path("/brands-images");

                $file->move($path, $name);
                
                $host = \Config::get('app.debug') ? "http://127.0.0.1:8000": "https://leo.vic-m.co";
                $dbPath = "{$host}/brands-images/{$name}";

            }
    
            $brandId = $brandData['brandLogoId'];
            Brand::where('id', '=', $brandId)
            ->update(['imageURL' => $dbPath]);

         }
        
        return back()->with('success', 'Your files has been send successfully');
    }

    public function getDoohBrands() {

        $brands = Brand::where('advertiserId', 375)->get();

        return response()->json($brands);
    }

    /**
     *| I think for I am - so say Rene Decartes - and I (the code that follows)
     *| think that we should be abstracted out of this Controller and into a
     *| Service class pertaining to visits for brands and brand-sectors. 
     */
    public function getVisitsSectors()
    {
        //Brand Dropdown
        $brands = BrandVisitScore::select('b.id','b.brandName', 'date')
            ->join('brands as b', 'b.id', 'brandId')
            ->groupBy('b.brandName')
            ->orderBy('b.brandName')
            ->get();

        // Sector Dropdown
        $sectors = BrandVisitScore::select('b.id','name')
            ->join('content_category as b', 'b.id', 'sectorId')
            ->groupBy('b.name')
            ->orderBy('b.name')
            ->get();


        $howFarBack = 'now';
        $months = [];

        // Months logic
        $firstMonthOfYear = date('Y-01-01', strtotime($howFarBack));
        $lastMonthOfYear = date('Y-12-t', strtotime($firstMonthOfYear));


        $currentMonth = $firstMonthOfYear;
        while ($currentMonth <= $lastMonthOfYear) {
            $time = strtotime($currentMonth);
            $months[] = [
                'year' => date('Y', $time),
                'month' => date('m', $time),
                'monthStr' => date('M', $time),
            ];
            $currentMonth = date('Y-m-d', strtotime($currentMonth . ' +1 month'));
        }

        sort($months);

        return view('brands.sectors', compact('brands', 'sectors', 'months'));

    }

    /**
     * 
     */
    public function getSectorBrands(Request $request)
    {
      $sectorId = $request->get('sectorId');

      return response()->json([

        'sector_location_brands' => \App\WidgetCategories::select('id', 'categoriesName')
          ->with([
            'linkedLocations' => function($linkedLocation) {
              return $linkedLocation->select('locationName', 'brandId')->groupBy('brandId');
            }
          ])
        ->find($sectorId)

      ]);
    }

  /**
   * @description AJAX CALL to get graph data
   * @note = Local data could only find sector brands for BrandIds = 1084, 1181
   *          SectorIds = 1,9
   * @param $brandId
   * @param $sectorId
   * @return array
   */
  public function getSectorGraphData(Request $request)
{
    $selectedBrandId = $request->get('brandId');
    $sectorId = $request->get('sectorId');
    //$minDate = $request->get('minDate');
    //$maxDate = $request->get('maxDate');

    // Get the first and last day of the previous month
    //eg "2023-06-01" - "2023-06-30"
    $firstDayOfPreviousMonth    = Carbon::now()->subMonth()->startOfMonth()->toDateString();
    $lastDayOfPreviousMonth     = Carbon::now()->subMonth()->endOfMonth()->toDateString();

    //this would return all scores for the previous month
    $brandVisitScore = BrandVisitScore::where('brandId', $selectedBrandId)
        ->whereBetween('date', [$firstDayOfPreviousMonth, $lastDayOfPreviousMonth])
        ->groupBy('date')
        ->get();

    //send score to graph
    $brandScore = [];
    foreach ($brandVisitScore as $selected) {
        $brandScore[] = [$selected->date, $selected->brandId, $selected->score, $selected->locationName];
    }

    //now get the scores for all other brands EXCLUDING selected brand for the previous month
    $sectorVisitScore = BrandVisitScore::select('*', DB::raw('AVG(score) as averageScore'))
        ->where('sectorId', $sectorId)
        ->where('brandId', '!=', $selectedBrandId)
        ->whereBetween('date', [$firstDayOfPreviousMonth, $lastDayOfPreviousMonth])
        ->groupBy('date')
        ->get();

    //send to graph
    $sectorScore = [];
    foreach ($sectorVisitScore as $score) {
        $date =         $score->date;
        $averageScore = $score->averageScore;

        $sectorScore[] = [
            'date'         => $date,
            'averageScore' => round($averageScore),
        ];
    }

    $selectedBrandName = Brand::where('id', $selectedBrandId)->get();

    $selectedBrand = [];

    foreach($selectedBrandName as $brandName) {
       $selectedBrand[] = [ $brandName->brandName ];
    }

    $results = [];
    $results['data']['brand_score'] = $brandScore;
    $results['data']['sector_score'] = $sectorScore;
    $results['data']['brandName'] = $selectedBrand;
   
    return $results;
}

  private function visitedBrandsBySector($sectorId, $brandId, $date)
  {
    return BrandVisitScore::whereIn(
      'brandId',
      $this->sectorBrandsLink($sectorId, $brandId, $date)['sector_brands']->keys()
        ->filter()->toArray()
    )->get();
  }

  private function sectorBrandsLink($sectorId, $brandId, $minDate=NULL, $maxDate=NULL)
  {
    $minDate = (int) $minDate;

    if ($maxDate == NULL || ! isset($maxDate) && isset($minDate)) {
      $temp = (int) date('U', strtotime($minDate)) + 82800000 * 0.001;
      $maxDate = (int) $temp;
      unset($temp);
    }

    return [
      [ // brand = $hourlyScoreByBrand
        'date' => $minDate,
        'brand_score' => $this->visitScoreByBrand($brandId, (int) $minDate, (int) $maxDate)
      ],
      [ // brands in sector - $hourlyScorePerSector
        'date' => $minDate,
        'sector_score' => $this->visitScoreBySectorBrands($sectorId, $minDate)
      ]
    ];
  }

  private function getSectorBrandIds($sectorId)
  {
    $sectorLinkedCollection = \App\WidgetCategories::query()
      ->select('id', 'categoriesName')
      ->with(['linkedLocations' => function($linkedLocation) {
        return $linkedLocation->select('locationName', 'brandId')->groupBy('brandId');
      }])
      ->where('id', $sectorId)
      ->first();

    return $sectorLinkedCollection->linkedLocations
      ->groupBy('brandId')->keys();
  }

  private function visitScoreBySectorBrands($sectorId, Int $idate=NULL)
  {
    try {
      if (round(log($idate, 10) > 12)) {
        $idate *= 0.001;
      }
    } catch (\Exception $error) {
      info($error);
    }

    $date = is_string($idate) ? date('Y-m-d', strtotime($idate)) : date('Y-m-d', $idate);

    $temp = BrandVisitScore::query()
      ->select('brandId', 'date', 'hour', 'sector_visits_temp')
      ->whereIn('brandId', $this->getSectorBrandIds($sectorId));

    if (NULL != $date && isset($date)) {
      $temp = $temp->where('date', $date);
    }

    $visitScoresByHours = $temp->orderBy('hour')
      ->get()->groupBy('hour');

    unset($temp, $brandIds);

    $hourlyScorePerSector = [];
    foreach(range(0, 23) as $hour) {
      $average = $visitScoresByHours[$hour]->avg('sector_visits_temp');
      $sum = $visitScoresByHours[$hour]->sum('sector_visits_temp');
      $count = $visitScoresByHours[$hour]->count();
      $stdevPerHour = 0.0;

      $std = $visitScoresByHours[$hour]->reduce(function($stdevPerHour, $visitsPerHour) use($average) {
        $numer = $visitsPerHour->visit_score - $average;
        return $stdevPerHour + ($numer ** 2);
      });

      // $hourlyScorePerSector[] = collect([
      //   'avg'   => $average,
      //   'stddev' => sqrt($std / --$count),
      // ]);

      $hourlyScorePerSector[] = $average;

      unset($std, $stdevPerHour, $numer, $average, $count, $hour, $sum);
    }

    $hourlyScorePerSector = collect($hourlyScorePerSector);
    unset($visitScoresByHours);
      
    // $colnMaxValue = $hourlyScorePerSector->max('avg');
    // $hourlyScorePerSector = collect($hourlyScorePerSector)->map(function($hour) use($colnMaxValue) {
    //   return $hour['avg'] * (100.0 / $colnMaxValue);
    // })->toArray();

    return $hourlyScorePerSector;
  }

  private function visitScoreByBrand($brandId, $minDate, $maxDate)
  {
    if (! is_string($minDate) || is_integer($minDate)) {
      $minDate = date('Y-m-d', $minDate);
    } else {
      $minDate = date('Y-m-d', strtotime($minDate));
    }

    if (! is_string($maxDate) || is_integer($maxDate)) {
      if (round(log($maxDate, 10) > 12)) {
        $maxDate *= 0.001;
      }
      $maxDate = date('Y-m-d', $maxDate);
    } else {
      $maxDate = date('Y-m-d', strtotime($maxDate));
    }

    $visitsBrand = \App\VisitsScaled::query()
      ->select('brandId', 'date', 'hour', 'sector_visits_temp')
      ->where('brandId', $brandId)
      ->whereBetween('date', [$minDate, $maxDate])
      ->orderBy('hour')
      ->get()
    ->groupBy('hour');

    $visitsPerHourBrand = $visitsBrand->map(function($visitBrand) {
      return $visitBrand->avg('sector_visits_temp');
    });

    $missingDataKeys = array_diff(
      range(0, 23),
      $visitsPerHourBrand->keys()->values()->toArray()
    );

    $tempArray = $visitsPerHourBrand->toArray();

    foreach(range(0, 23) as $key) {
      if (in_array($key, $missingDataKeys) || ! array_key_exists($key, $tempArray)) {
        $tempArray[$key] = floatval(0.0);
      }

      unset($key);
    }

    ksort($tempArray);
    $visitsPerHourBrand = collect($tempArray);

    unset($tempArray, $key, $missingDataKeys, $visitBrand, $visitsBrand);

    // $colnMaxValue = $visitsPerHourBrand->max();
    // $visitsPerHourBrand = $visitsPerHourBrand->map(function($brand) use($colnMaxValue) {
    //   return $brand * (100 / $colnMaxValue);
    // })->toArray();

    return $visitsPerHourBrand;
  }

  private function visitsScoreBrands()
  {
      return \App\VisitsScaled::with('brand')
          ->get()
          ->pluck('brand.brandName', 'brand.id')
          ->toArray();
  }

  private function visitsScoreBrandsId()
  {
      return array_keys(
          $this->visitsScoreBrands()
      );
  }

public function getVisitsSectorTable($brandId, $sectorId)
{
    $removeSelectedBrand = [$brandId];
    
    $sectorVisitScore = BrandVisitScore::select('*', DB::raw('AVG(score) as average_score'))
    ->where('sectorId', $sectorId)
    ->whereNotIn('brandId', $removeSelectedBrand)
    ->groupBy('brandId')
    ->get();

    $join = [];
    foreach ($sectorVisitScore as $brandScore) {
        $row = [];
        $row['brand'] = Brand::where('id', $brandScore->brandId)->pluck('brandName')->first();
        $row['sector'] = WidgetCategories::where('id', $sectorId)->pluck('categoriesName')->first();
        $row['brand_score'] = round($brandScore->average_score);
        
        $selectedBrand = BrandVisitScore::select('*', DB::raw('AVG(score) as average_score'))
        ->where('brandId', $brandId)
        ->groupBy('brandId')
        ->get();

        foreach ($selectedBrand as $brand) {
            $row['selectedBrandScore'] = round($brand->average_score);
        }
    
        $join[] = $row;
    }
    
    usort($join, function ($a, $b) {
        
        return strcmp($a['brand'], $b['brand']);
    });
    
    $data = [
        'data' => $join,
    ];
    
    return $data; 
}

}
