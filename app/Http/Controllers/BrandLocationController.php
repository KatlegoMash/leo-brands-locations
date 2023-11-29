<?php

namespace App\Http\Controllers;

use App\Brand;
use Exception;
use Carbon\Carbon;
use App\Advertiser;
use App\PolygonPoint;
use App\ContentCategory;
use App\BrandLocation;
use App\CampaignLocation;
use App\WidgetCategories;
use App\CategoryAttributes;
use App\TemplatePinSettings;
use App\NameTags;
use App\LocationNameTags;
use App\StoredWidgetCategories;
use App\BrandLocationAdditionalInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;

use App\Classes\ReadExcel;

class BrandLocationController extends Controller
{
    public function getSelectedBrands($campignId=null)
    {

      $campaignId = Input::input('campaignId');
      $brands = DB::table('campaign_location')
              ->select('brands.id as brandId' ,'brands.brandName as brandName', DB::raw('COUNT(*) as num_locations'))
              ->join('brand_locations', 'brand_locations.id', '=', 'campaign_location.brandLocationId')
              ->join('brands', 'brands.id', '=', 'brandId')
              ->where('campaignId', $campignId)
              ->groupBy('brand_locations.brandId')
              ->get()
              ->toArray();

              return view('campaigns.index')->with('brands', $brands);
    }

    //GET locations/
    public function getIndex()
    {

        $categories = WidgetCategories::whereNotIn('id', array_values(WidgetCategories::$specialCategories))->get();
        $brand = "";

        if(Input::has("brandId")){
            $brand = Brand::with("advertiser")->find(Input::get("brandId"));
        }

        //$brands = Brand::orderBy('brandName','ASC')->get();


        return view('locations.index')->withLocations([])->withCategories($categories)->withBrand($brand);
    }

    public function getContentCategories(){
        $cats = (new ContentCategory)->largeIds()->with('childrenCategories')->get();
        return $cats;
    }

    public function getAra(){
        $brandIds = [1958, 1959, 1956, 1753];

        $locationId = Input::get('locationId');
        $brandId = Input::get('brandId');

        $compliant = BrandLocation::whereDoesntHave('ara_places');
        $nonCompliant = BrandLocation::whereHas('ara_places');

        if(!empty($locationId)){
            $compliant->where('id', $locationId);
            $nonCompliant->where('id', $locationId);
        }

        if(!empty($brandId)){
            $compliant->where('brandId', $brandId);
            $nonCompliant->where('brandId', $brandId);
        }else{
            $compliant->whereIn('brandId', $brandIds);
            $nonCompliant->whereIn('brandId', $brandIds);
        }

        $compliant->with('brand', 'ara_places');
        $nonCompliant->with('brand', 'ara_places');

        return view('locations.ara')->with([
            'compliant_brand_locations' => $compliant->get(),
            'non_compliant_brand_locations' => $nonCompliant->get(),
        ]);
    }
    /**
     * GET location/ooh-index
     */
    public function getOohIndex()
    {
       $age = CategoryAttributes::select('category_attributes.*')
       ->join('categories', 'category_attributes.category_id' ,'=','categories.id')
       ->where('categories.name', 'Age')
       ->orderBy('category_attributes.name','ASC')
       ->get();

        $income  = CategoryAttributes::select('category_attributes.*')
       ->join('categories', 'category_attributes.category_id' ,'=','categories.id')
       ->where('categories.name', 'Gross Monthly Household Income')
        ->orderBy('category_attributes.name','ASC')->get();

        return view('ooh.index')->with('age', $age)->with('income', $income);
    }

    /**
 * GET location/brand-overlap
 */
    public function anyBrandOverlap(Request $request)
{
    if ($request->isMethod('post')) {
        if ($request->filter_radio == 'categories'){

            $request->validate(
                [
                    'categoryId' => 'required',
                    'campaignId' => 'required',
                    'end_date' => 'required',
                    'start_date' => 'required',
                ],
                [
                    'start_date.required' => 'Please select the dates',
                    'end_date.min' => 'Please select the dates',
                    'categoryId.required' => 'Please select the category',
                    'campaignId.required' => 'Please select the campaign'
                ]
            );


        }elseif($request->filter_radio =='brands'){
            $request->validate(
                [
                    'brandId' => 'required',
                    'end_date' => 'required',
                    'start_date' => 'required',
                ],
                [
                    'start_date.required' => 'Please select the dates',
                    'end_date.min' => 'Please select the dates',
                    'brandId.required' => 'Please select the brand'
                ]
            );
        }elseif($request->filter_radio =='campaigns'){
            $request->validate(
                [
                    'campaignId' => 'required',
                    'end_date' => 'required',
                    'start_date' => 'required',
                ],
                [
                    'start_date.required' => 'Please select the dates',
                    'end_date.min' => 'Please select the dates',
                    'campaignId.required' => 'Please select the campaign',
                ]
            );
        }

        $information = 1;
        $brandIds = $request->brandId;

        if ($request->brandlocationId == null) {
            $brandLocationIds = BrandLocation::whereIn('brandId', $request->brandId)->pluck('id');
        } else {
            $brandLocationIds = BrandLocation::whereIn('id', $request->brandlocationId)->pluck('id');
        }
        if (!$brandLocationIds->isEmpty()) {

            $brand_overlaps = DB::select(
                "select b.brandName as brandName,sum(overlap) as overlap,sum(unique_users) as uniqueUsers,sum(unique_overlap_users) as uniqueOvarlapUsers
                    from brand_overlap bo
                    join brand_locations bl on bl.id = bo.locationId
                    join brands b on b.id = bo.brand_overlapid
                    WHERE bo.locationId IN (" . str_replace(array('[', ']'), '', $brandLocationIds) . ")
                    AND (bo.date BETWEEN '" . Carbon::parse($request->start_date)->format('Y-m-d') . "' AND '" . Carbon::parse($request->end_date)->format('Y-m-d') . "')
                    GROUP BY b.brandName"
            );
            $brand_overlap_searchs = DB::select(
                "select wc.categoriesName as categoriesName ,sum(overlap) as overlap,sum(unique_users)  as uniqueUsers,sum(widget_users) as widgetOvarlapUsers
                    from brand_overlap_search bo
                    join brand_locations bl on bl.id = bo.locationId
                    join widget_categories_brand_location wcbl  on wcbl.brand_location_id = bo.locationId
                    join widget_categories wc on wc.id = wcbl.categoriesId
                    WHERE bo.locationId IN (" . str_replace(array('[', ']'), '', $brandLocationIds) . ")
                    AND (bo.date BETWEEN '" . Carbon::parse($request->start_date)->format('Y-m-d') . "' AND '" . Carbon::parse($request->end_date)->format('Y-m-d') . "')
                    GROUP BY wc.categoriesName"
            );
            $click_actions = DB::select(
                "select cc.name as name, sum(ca.driveTo) as driveTo, sum(ca.walkTo) as walkTo, sum(ca.web) as  web, sum(ca.call) as callTo
                    from click_actions ca
                    join campaign_categories camc on camc.campaignId = ca.campaignId
                    join content_category cc on cc.id = camc.categoryId
                    WHERE ca.locationId IN (" . str_replace(array('[', ']'), '', $brandLocationIds) . ")
                    AND (ca.created_at BETWEEN '" . Carbon::parse($request->start_date)->format('Y-m-d') . "' AND '" . Carbon::parse($request->end_date)->format('Y-m-d') . "')
                    group by cc.name;"
            );
        } else {
            $brand_overlaps = [];
            $brand_overlap_searchs = [];
            $click_actions = [];
            $brandLocationIds = [];
        }

        return view('brand-overlap.index', compact('information', 'brand_overlaps', 'brand_overlap_searchs', 'brandIds','brandLocationIds','click_actions'));
    }
    if ($request->isMethod('get')) {
        $information = 0;
        $brand_overlaps = [];
        $brand_overlap_searchs = [];
        $click_actions = [];
        $brandIds = [];
        $brandLocationIds = [];
        return view('brand-overlap.index', compact('information', 'brand_overlaps', 'brand_overlap_searchs','brandIds','brandLocationIds', 'click_actions'));
    }
}

    //GET locations/edit
    public function getEdit()
    {
        return view('locations.edit')->with('method', 'post');
    }

    //POST locations/new
    public function postNew($auto=false)
    {
        $input = Input::except('advertiserId', 'addAndNewLocation', 'addLocation');

        $location = BrandLocation::firstOrNew($input);
        $validator = $location->validate($input);

        if ($validator->passes()) {
            try {
                $location->save();
            } catch (Exception $e) {
                Input::flash();
                return  view('locations.edit')->with('method', 'post')->withErrors($validator->messages());
            }
        } else {
            Input::flash();
            return view('locations.edit')->with('method', 'post')->withErrors($validator->messages());
        }

        if ($auto == true) {
            return $location->id;
        }

        if (Input::has('addAndNewLocation')) {
            $redirect = redirect('locations/edit?brandId='.Input::get('brandId').'&advertiserId='.Input::get('advertiserId'))->with('validForm', 'success');
        } else {
            $redirect = redirect('locations?brandId='.Input::get('brandId').'&advertiserId='.Input::get('advertiserId'))->with('validForm', 'success')->with('validRows', $location);
        }

        return $redirect;
    }

    //POST locations/update/123
    public function postUpdate($id)
    {
        //Validate
        $input = Input::except('advertiserId', 'ajax');
        $location = BrandLocation::find($id);

        $validator = $location->validateUpdate($input, $id);

        if ($validator->passes()) {
            try {
                $location->fill($input);
                $location->save();
                $dynamicFields = Input::get('dynamicFields');

                if(sizeof($dynamicFields)){
                    foreach($dynamicFields as $dynamicField){
                        BrandLocationAdditionalInfo::where('field_name', $dynamicField['fieldName'])->update([
                            'field_value' => $dynamicField['fieldValue']
                        ]);
                    }
                }

                Cache::forget(md5(TemplatePinsController::$cacheKeyPrefix.$id));
            } catch (Exception $e) {
                return Response::json($validator->messages());
            }
        } else {
            Input::flash();
            if (Input::get('ajax') == true) {
                return response()->json($validator->messages());
            }
        }

        return '';
    }

    //DELETE locations/location/123
    public function deleteLocation($id = null)
    {
        $ids = explode(',', Input::input('ids'));
        $campaignsLinked = CampaignLocation::whereIn("brandLocationId", $ids)->with("campaign")->get();
        $campaigns = [];

        if (count($campaignsLinked)) {
            foreach ($campaignsLinked as $value) {
                if(isset($value->campaign)){
                    if($value->campaign->state == 'active' && is_null($value->campaign->deleted_at)){
                        $campaigns[] = [
                            "id"=>$value->campaign->campaignId,
                            "name"=>$value->campaign->campaignName,
                        ];
                    }
                }
            }
        }

        if(sizeof($campaigns)){
            return response()->json(compact("campaigns"));
        }

        if (!isset($id)) {

            $ids = explode(',', Input::input('ids'));
            // permanently deletes
            // https://stackoverflow.com/questions/29350359/can-not-soft-delete-multiple-rows-using-query-builder
            // BrandLocation::find($ids)->delete();

            foreach($ids as $id){
                $location = BrandLocation::find($id);
                $location->delete();
            }

        } else {
            $location = BrandLocation::find($id);
            $location->delete();
        }

        return response()->json(['message' => 'Successfully deleted location(s)!',"campaigns"=>[]]);
    }

    //POST locations/import
    public function postImport()
    {
        $locationBankClientId = null;
        $brandId = Input::get('brandId');
        $advertiserId = $brandId ?? Brand::find($brandId)->advertiserId;

        $editPageRedirect  = "locations/edit?brandId={$brandId}&advertiserId={$advertiserId}&page=bulk";
        $indexPageRedirect = "locations?brandId={$brandId}&advertiserId={$advertiserId}";

        if (Input::hasFile('spreadsheet') and Input::file('spreadsheet')->isValid()) {
            $file = Input::file('spreadsheet');
            $path = Input::file('spreadsheet')->getRealPath();

            $extension = Input::file('spreadsheet')->getClientOriginalExtension();

            if (in_array($extension, ['xlsx', 'xltx', 'xls', 'xlsb', 'xlam', 'xlsb'])) {
                $objReader   = new \PHPExcel_Reader_Excel2007();
                $objReader->setLoadSheetsOnly(["Sheet1"]);
                $objReader->setReadDataOnly(true);
                $objPHPExcel = $objReader->load($path);

                $alphabetsHead = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"];
                $getSpreadSheetHead = ["Id", "LocationName", "Latitude", "Longitude", "Geofence", "StoreCode", "StoreName", "AddressLine1", "AddressLine2", "PostalCode", "City", "CountryCode", "Phone", "HomePage", "LocationBankId", "Suburb", "Province", "Country"];
                $importSpreadHead = [];
                $count = 0;
                $locations = [];
                $headers = $objPHPExcel->getActiveSheet()->getRowIterator(1);

                foreach ($headers as $head) {
                    if ($count < 1) {
                        for ($i = 0; $i < 26; $i++) {
                            $importSpreadHead[$i] = $objPHPExcel->getActiveSheet()->getCell("{$alphabetsHead[$i]}" . $head->getRowIndex())->getValue();
                        }

                        $result = array_diff_key($getSpreadSheetHead, $importSpreadHead);
                        $intersection = array_intersect_key($getSpreadSheetHead, $importSpreadHead);

                        $extraColumns = array_diff($importSpreadHead, $getSpreadSheetHead);
                        $extraColumns  = array_filter($extraColumns); //Remove null columns

                        if ($result == null) {
                            $rowStartIdx = 2;
                            $rows = $objPHPExcel->getActiveSheet()->getRowIterator($rowStartIdx); //start at row 2, not 0 indexed

                             foreach ($rows as $row) {
                                $location['locationName']  = trim($objPHPExcel->getActiveSheet()->getCell("B" . $row->getRowIndex())->getValue());

                                $location['latitude']      = doubleval($objPHPExcel->getActiveSheet()->getCell("R" . $row->getRowIndex())->getValue());
                                $location['longitude']     = doubleval($objPHPExcel->getActiveSheet()->getCell("S" . $row->getRowIndex())->getValue());

                                $geoFence = (int) trim($objPHPExcel->getActiveSheet()->getCell("D" . $row->getRowIndex())->getValue()) ?? 0;
                                $location['maxGeofence'] = $geoFence == 0 ? 5000 : $geoFence;

                                $location['storeName']     = trim($objPHPExcel->getActiveSheet()->getCell("N" . $row->getRowIndex())->getValue());
                                $location['storeCode']     = trim($objPHPExcel->getActiveSheet()->getCell("O" . $row->getRowIndex())->getValue());

                                $location['addressLine1'] = trim($objPHPExcel->getActiveSheet()->getCell("E" . $row->getRowIndex())->getValue());
                                $location['addressLine2'] = trim($objPHPExcel->getActiveSheet()->getCell("F" . $row->getRowIndex())->getValue());

                                $location['postalZipCode'] = trim($objPHPExcel->getActiveSheet()->getCell("G" . $row->getRowIndex())->getValue());

                                $city = trim($objPHPExcel->getActiveSheet()->getCell("Z" . $row->getRowIndex())->getValue());
                                if (null == $city || ! $city || ! isset($city)) {
                                    $city = "N/A";
                                }
                                $location['city'] = $city; // = trim($objPHPExcel->getActiveSheet()->getCell("H" . $row->getRowIndex())->getValue());

                                $location['countryCode'] = trim($objPHPExcel->getActiveSheet()->getCell("H" . $row->getRowIndex())->getValue());

                                $location['phone'] = trim($objPHPExcel->getActiveSheet()->getCell("I" . $row->getRowIndex())->getValue());
                                if (isset($location['phone'])) {
                                    $location['phone'] = preg_replace('/\s+/', '', $location['phone']);
                                }

                                $homePage = trim($objPHPExcel->getActiveSheet()->getCell("J" . $row->getRowIndex())->getValue());
                                if (strlen($homePage) < 3) {
                                    $homePage = "https://" . $homePage;
                                }
                                $location['homePage']  = $homePage;

                                if (Input::has('update')) {
                                    $location['id']  = (int) trim($objPHPExcel->getActiveSheet()->getCell("A" . $row->getRowIndex())->getValue());
                                    $location['locationBankId']  = trim($objPHPExcel->getActiveSheet()->getCell("K" . $row->getRowIndex())->getValue());
                                } else {
                                    $location['id']  = trim($objPHPExcel->getActiveSheet()->getCell("A" . $row->getRowIndex())->getValue());
                                    $location['locationBankId']  = trim($objPHPExcel->getActiveSheet()->getCell("K" . $row->getRowIndex())->getValue());
                                }

                                $location['suburb']   = trim($objPHPExcel->getActiveSheet()->getCell("C" . $row->getRowIndex())->getValue());
                                $location['province'] = trim($objPHPExcel->getActiveSheet()->getCell("L" . $row->getRowIndex())->getValue());
                                $location['country']  = trim($objPHPExcel->getActiveSheet()->getCell("Q" . $row->getRowIndex())->getValue());

                                if (
                                    $location['id'] &&
                                    (is_string($brandId) || ! is_int($brandId) || (int) $brandId == 0)
                                ) {
                                    $brandLocation = \App\BrandLocation::with('brand')->find($location['id']);

                                    if ($brandId == null && $brandLocation != null) {
                                        $brandId = $brandLocation->brand->id;
                                    }

                                    if ($advertiserId == null && $brandLocation != null) {
                                        $advertiserId = $brandLocation->brand->advertiserId;
                                    }

                                    if (
                                        property_exists($brandLocation->brand, 'locationBankClientId') &&
                                        ($locationBankClientId == null || empty($location['locationBankId']) || null == $location['locationBankId'])
                                    ) {
                                        if ($locationBankClientId == null && $brandLocation != null) {
                                            $locationBankClientId = $brandLocation->brand->locationBankClientId;
                                        }
                                    }
                                }

                                $location['brandId'] = $brandId ?? $brandId;
                                $location['locationBankId'] = $locationBankClientId ?? $locationBankClientId;

                                $extraColumnData = [];

                                if(sizeof($extraColumns) > 0){
                                    foreach($extraColumns as $key => $extraColumnName){
                                        $extraColumnLetter = $alphabetsHead[$key];
                                        $extraColumnCellData = trim($objPHPExcel->getActiveSheet()->getCell($extraColumnLetter . $row->getRowIndex())->getValue());
                                        if($extraColumnCellData !== ""){
                                            $extraColumnData[$extraColumnName] = $extraColumnCellData;
                                        }

                                    }
                                }

                                $location['extraData'] = $extraColumnData;
                                $locations[] = $location;
                            }

                            // validate
                            $errors = [];
                            $validRows = [];

                            if (Input::has('update')) {
                                // Get valid ID's from the input, we will then compare this array with the
                                // array of ID's we get from the uploaded spreadsheet
                                $valid_location_ids = explode(
                                    ",",
                                    str_replace(["[", "]"], "", Input::get("selected_ids"))
                                );
                                $location_id_column = array_column($locations, 'id');

                                // Returns an empty array if there are no differences between the two sets of ids
                                $diff = array_diff($valid_location_ids, $location_id_column);

                                if (!empty($diff)) {
                                    $msg = [];
                                    $msg['locationIds'] = ["The Location ID's in the upload do not match the locations selected."];
                                    $errors[''] = json_encode($msg);
                                    return redirect($indexPageRedirect)->with('ErrorsValidation',  $errors)->with('validRows', $validRows);
                                }
                            }

                            foreach ($locations as $key => $locationData) {
                                if (empty($locations[$key]["storeName"])) {
                                    continue;
                                }

                                foreach ($locationData as $key2 => $input) {
                                    $locations[$key][$key2] = str_replace(["'", '"', ","], "", $input);
                                }

                                $row = $key + 2;

                                if (Input::has('update')) {
                                    $brand_location = new BrandLocation;
                                    $validator = $brand_location->validateUpdate($locationData, $locationData['id']);
                                    /**
                                     *   Unset this because we never want it updated.
                                     */
                                    unset($locationData['brandId']);

                                    if (count($validator->messages())) {
                                        $errors[$row] = json_encode($validator->messages());
                                    } else {
                                        $locationData['updated_at'] = date('Y-m-d H:i:s');

                                        if(isset($locationData['extraData']) && sizeof($locationData['extraData'])) {
                                            foreach($locationData['extraData'] as $extraDataName => $extraDataValue) {
                                                //Delete previous extra info
                                                BrandLocationAdditionalInfo::where('brand_location_id', $locationData['id'])
                                                    ->where('field_name', $extraDataName)
                                                    ->delete();

                                                if($extraDataName !== 'Id'){
                                                    //Create new BrandLocationAdditionalInfo based on the new spreadsheet column
                                                    $newLocationExtraData = new BrandLocationAdditionalInfo();
                                                    $newLocationExtraData->brand_location_id = $locationData['id'];
                                                    $newLocationExtraData->field_name = $extraDataName;
                                                    $newLocationExtraData->field_value = $extraDataValue;
                                                    $newLocationExtraData->save();
                                                }
                                            }
                                        }
                                        unset($locationData['extraData']); //Remove this so we can update the correct columns
                                        DB::table('brand_locations')->where("id", $locationData['id'])->update($locationData);
                                        $validRows[$row] = $location;
                                    }
                                } else {
                                    $location = new BrandLocation($locationData);
                                    $locationValidation = $location->validate($locationData);

                                    if (count($locationValidation->messages())) {
                                        $errors[$row] = json_encode($locationValidation->messages());
                                    } else {
                                        if($location->save()){
                                            if(isset($locationData['extraData']) && sizeof($locationData['extraData'])){

                                                foreach($locationData['extraData'] as $extraDataName => $extraDataValue){

                                                    BrandLocationAdditionalInfo::where('brand_location_id', $location->id)->where('field_name', $extraDataName)->delete();

                                                    $newLocationExtraData = new BrandLocationAdditionalInfo();
                                                    $newLocationExtraData->brand_location_id = $location->id;
                                                    $newLocationExtraData->field_name = $extraDataName;
                                                    $newLocationExtraData->field_value = $extraDataValue;
                                                    $newLocationExtraData->save();
                                                }
                                            }

                                            $validRows[$row] = $location->toArray();
                                        }

                                    }
                                }
                            }

                            if (count($errors)) {
                                if (Input::has('update')) {
                                    return redirect($indexPageRedirect)->with('ErrorsValidation', $errors)->with('validRows', $validRows);
                                } else {
                                    return redirect($editPageRedirect)->with('ErrorsValidation', $errors)->with('validRows', $validRows);
                                }
                            }
                        }
                    }
                    $count++;
                }

                return redirect($indexPageRedirect)->with('validForm','success')->with('validRows', $validRows);
            } else {
                return redirect("{$editPageRedirect}&alert=Please choose the right spreadsheet ending with xlsx, xltx, xls");
            }
        } else {
            return redirect("{$editPageRedirect}&alert=Please choose the right spreadsheet ending with xlsx, xltx, xls");
        }

        //reset the execution max time
    }

    //POST locations/kml
    public function postKml(Request $request)
    {
        $this->validate($request,
            ['kmlfile' => 'required|size:32048'],
            ['kmlfile.size' => 'Maximum file size is 32Mb!']
        );

        $files             = $request->file('kmlfile');
        $brandId           = $request->get('brandId');
        $indexPageRedirect = "locations?brandId={$brandId}";

        foreach($files as $file){
            $path = storage_path().DIRECTORY_SEPARATOR;
            $file->move($path,$file->getClientOriginalName());
            $fullpath = $path . '/' . $file->getClientOriginalName();

            $xml        = simplexml_load_file($fullpath);
            $placemarks = $xml->Document->Folder->Placemark;

            for($i = 0; $i < count($placemarks); $i++) {
                $location_name = $placemarks[$i]->name->__toString();

                $coordinates = trim($placemarks[$i]->MultiGeometry->Polygon->outerBoundaryIs->LinearRing->coordinates->__toString());
                $coordinates = explode(" ", $coordinates);

                $location = explode(",",$coordinates[0]);
                $id = BrandLocation::insertGetId([
                    'brandId'       => $brandId,
                    'locationName'  => $location_name,
                    'latitude'      => $location[1],
                    'longitude'     => $location[0],
                    'maxGeofence'   => 5000,
                    'storeName'     => $location_name,
                    'regionCode'    => 'ZA',
                    'countryCode'   => 'ZA',
                    'created_at'    => date("Y-m-d H:i:s")
                ]);

                foreach ($coordinates as $value) {
                    $value = explode(",", $value);
                    if (count($value) > 1) {
                        $insertPoly[] = [
                            'brandLocationId'   => $id,
                            'longitude'         => trim($value[0]),
                            'latitude'          => trim($value[1]),
                            'created_at'        => date("Y-m-d H:i:s"),
                        ];
                    }
                }
                PolygonPoint::insert($insertPoly);
            }
        }

        unlink($fullpath);

        $validRows = [];
        return redirect($indexPageRedirect)->with('validForm', 'success')->with('validRows', $validRows);
    }

    public function getCities()
    {
        $locationsByCity = array_merge(['default' => 'All'], BrandLocation::orderBy('locationName')->pluck('city', 'regionCode')->toArray());

        $recordsTotal = count($locationsByCity);

        $data['draw'] = 1;
        $data['recordsTotal'] = $recordsTotal;
        $data['recordsFiltered'] = $recordsTotal;
        $data['data'] = $locationsByCity;

        return Response::json($data);
    }

    public function getAdvancedLocationSearch(Request $request)
    {
        $data['data'] = [];
        $data = $request->all();


        $requestString = "select s1.*,0 as rating from
        (SELECT brand_locations.*, brands.id as brand_id, brands.brandName
        FROM brand_locations
        JOIN brands on brands.id = brand_locations.brandId ";

        if ($request->has("tags") && $request->get("tags") != []) {
            $requestString = $requestString." LEFT JOIN location_name_tags on brand_locations.id = location_name_tags.locationId";
        }

        $requestString = $requestString." WHERE brand_locations.deleted_at is null";

        foreach($request->all() as $key => $val) {
            if ($key == "brands") {
                $addString = $this->createBrandRequestString($val);
                $requestString = $requestString.$addString;
            } else if ($key == "cities" ) {
                $addString = $this->createCityRequestString($val);
                $requestString = $requestString.$addString;
            } else if ($key == "suburbs" ) {
                $addString = $this->createSuburbRequestString($val);
                $requestString = $requestString.$addString;
            } else if ($key == "provinces" ) {
                $addString = $this->createProvinceRequestString($val);
                $requestString = $requestString.$addString;
            } else if ($key == "names") {
                $addString = $this->createNameRequestString($val);
                $requestString = $requestString.$addString;
            } else if ($key == "tags") {
                $addString = $this->createTagRequestString($val);
                $requestString = $requestString.$addString;
            } else if ($key == "languages") {
                $addString = $this->createLanguageRequestString($val);
                $requestString = $requestString.$addString;
            } else if ($key == "races") {
                $addString = $this->createRaceRequestString($val);
                $requestString = $requestString.$addString;
            } else if ($key == "dates") {
                $addString = $this->createDateRequestString($val);
                $requestString = $requestString.$addString;
            } else if ($key == "in_market") {
                $addString = $this->createInMarketRequestString($val);
                $requestString = $requestString.$addString;
            } else if ($key == "pplHousehold") {
                $addString = $this->createMultislideRequestString($val, 'pplHousehold');
                $requestString = $requestString.$addString;
            } else if ($key == "age") {
                $addString = $this->createMultislideRequestString($val, 'age');
                $requestString = $requestString.$addString;
            } else if ($key == "parents") {
                $addString = $this->createMultislideRequestString($val, 'parents');
                $requestString = $requestString.$addString;
            } else if ($key == "the_children") {
                $addString = $this->createMultislideRequestString($val, 'children');
                $requestString = $requestString.$addString;
            } else if ($key == "income") {
                $addString = $this->createMultislideRequestString($val, 'income');
                $requestString = $requestString.$addString;
            } else if ($key == "gender") {
                $addString = $this->createGenderRequestString($val);
                $requestString = $requestString.$addString;
            } else {
                return response(400, "Incorrect request recieved.");
            }
        }

        $requestString = $requestString." ) s1";

        dd($requestString);
        $locations = DB::select($requestString);

        $locationIds = "(";
        $size = sizeof($locations);

        for ($i=0;$i < $size;$i++) {
            if ($i == $size - 1) {
                $locationIds = $locationIds.(string)$locations[$i]->id.")";
            } else {
                $locationIds = $locationIds.(string)$locations[$i]->id.",";
            }
        }

        //widget categories for locations
        $categories = WidgetCategories::where("deleted_at", null)->get()->keyBy('id');
        $locationCategories = StoredWidgetCategories::all();

        $mapped = [];

        //mappped is a key value array of locationId => [cat1, cat2, cat3]
        foreach($locationCategories as $item) {
            if (isset($mapped[$item->brand_location_id])) {
                $mapped[$item->brand_location_id][] = $categories[$item->categoriesId]->categoriesName;
            } else {
                $mapped[$item->brand_location_id] = [];
                $mapped[$item->brand_location_id][] = $categories[$item->categoriesId]->categoriesName;
            }
        }

        $tags = NameTags::where('deleted_at', null)->get()->keyBy('id');

        if ($locationIds == "(") {
            $tagsMapped = [];
        } else {
            $locationTags = DB::select('SELECT locationId, group_concat(tag_id) as tags FROM location_name_tags WHERE deleted_at IS NULL AND locationId in '.$locationIds.' GROUP BY locationId;');
            $tagsMapped = [];

            foreach($locationTags as $tag) {
                $arr = explode(',', $tag->tags);
                foreach($arr as $item) {
                    if (isset($tags[$item])) {
                        $tagsMapped[$tag->locationId][] = $tags[$item]->tag;
                    } else {
                        continue;
                    }
                }
            }
        }

        foreach($locations as &$brandLocation){

            if(isset($mapped[$brandLocation->id])) {
                $brandLocation->categories = implode(", ",$mapped[$brandLocation->id]);
            } else {
                $brandLocation->categories = "";
            }

            if(isset($tagsMapped[$brandLocation->id])) {
                $brandLocation->tags = implode(", ", $tagsMapped[$brandLocation->id]);
            } else {
                $brandLocation->tags = "none";
            }

        }

        dd($locations);

        foreach ($locations as $location) {
            $location->visitScore = null;
        }

        $data['data'] = $locations;

        return response()->json($data);

    }


    private function createDateRequestString($dates){
        $dates[0] = date("Y/m/d");
        $firstDate = $dates[0];
        $dates[1] = date("Y/m/d");
        $string = " AND brand_locations.created_at <= " . $firstDate . " AND brand_locations.deleted_at = NULL";
        return $string;
    }

    private function createLanguageRequestString($languages) {

        if (sizeof($languages) == 0) {
            return "";
        }

        $string = " AND brand_location_demographics.tag_id in ";

        $tmp = "(";
        $size = sizeof($languages);
        dump($languages);
        for($i = 0; $i < $size; $i++) {
            if ($i == $size - 1) {
                $tmp = $tmp.$languages[$i]["id"].")";
            } else {
                $tmp = $tmp.$languages[$i]["id"].",";
            }
        }
        $string = $string.$tmp;
        $string = '';

        return $string;

    }

    private function createRaceRequestString($races) {

        if (sizeof($races) == 0) {
            return "";
        }
        dump($languages);
        $string = " AND brand_location_demographics.tag_id in ";

        $tmp = "(";
        $size = sizeof($races);
        for($i = 0; $i < $size; $i++) {
            if ($i == $size - 1) {
                $tmp = $tmp.$races[$i]["id"].")";
            } else {
                $tmp = $tmp.$races[$i]["id"].",";
            }
        }
        $string = $string.$tmp;
        $string = '';

        return $string;

    }

    private function createInMarketRequestString($inMarketTags) {

        if (sizeof($inMarketTags) == 0) {
            return "";
        }

        $string = " AND brand_location_demographics.tag_id in ";
        dump($inMarketTags);
        $tmp = "(";
        $size = sizeof($inMarketTags);
        for($i = 0; $i < $size; $i++) {
            if ($i == $size - 1) {
                $tmp = $tmp.$inMarketTags[$i]["id"].")";
            } else {
                $tmp = $tmp.$inMarketTags[$i]["id"].",";
            }
        }
        $string = $string.$tmp;
        $string = '';

        return $string;

    }

    private function createMultislideRequestString($array, $columnName) {

        if (sizeof($array) == 0) {
            return "";
        }

        $string = " AND brand_location_demographics.".$columnName." in (".$array[0].",".$array[1].")";
        dump($array);/*Temporary*/
        $string = '';/*Temporary*/

        return $string;

    }

    private function createGenderRequestString($gender) {

        $string = " AND brand_location_demographics.gender_tendancy = `".$gender."`";
        $string = '';/*Temporary*/
        return $string;

    }

    private function createTagRequestString($tagIds) {

        if (sizeof($tagIds) == 0) {
            return "";
        }

        $string = " AND location_name_tags.tag_id in ";

        $tmp = "(";
        $size = sizeof($tagIds);
        for($i = 0; $i < $size; $i++) {
            if ($i == $size - 1) {
                $tmp = $tmp.$tagIds[$i]["id"].")";
            } else {
                $tmp = $tmp.$tagIds[$i]["id"].",";
            }
        }
        $string = $string.$tmp;
        return $string;

    }

    private function createProvinceRequestString($provinces) {

        if (sizeof($provinces) == 0) {
            return "";
        }

        $string = " AND province in ";

        $tmp = "(";
        $size = sizeof($provinces);
        for($i = 0; $i < $size; $i++) {
            if ($i == $size - 1) {
                $tmp = $tmp."'".$provinces[$i]["text"]."'".")";
            } else {
                $tmp = $tmp."'".$provinces[$i]["text"]."'".",";
            }
        }
        $string = $string.$tmp;

        return $string;

    }

    private function createNameRequestString($names) {

        if (sizeof($names) == 0) {
            return "";
        }

        $string = " AND ";

        $tmp = "(";
        $size = sizeof($names);
        for($i = 0; $i < $size; $i++) {
            if ($i == $size - 1) {
                $tmp = $tmp."locationName LIKE '%".addslashes($names[$i]["text"])."%')";
            } else {
                $tmp = $tmp."locationName LIKE '%".addslashes($names[$i]["text"])."%' OR ";
            }
        }
        $string = $string.$tmp;

        return $string;

    }

    private function createBrandRequestString($brands) {

        if (sizeof($brands) == 0) {
            return "";
        }

        $string = " AND brandId in ";

        $tmp = "(";
        $size = sizeof($brands);
        for($i = 0; $i < $size; $i++) {
            if ($i == $size - 1) {
                $tmp = $tmp.$brands[$i]["id"].")";
            } else {
                $tmp = $tmp.$brands[$i]["id"].",";
            }
        }
        $string = $string.$tmp;

        return $string;

    }

    private function createCityRequestString($cities) {

        if (sizeof($cities) == 0) {
            return "";
        }

        $string = " AND city in ";

        $tmp = "(";
        $size = sizeof($cities);
        for($i = 0; $i < $size; $i++) {
            if ($i == $size - 1) {
                $tmp = $tmp."'".$cities[$i]["text"]."'".")";
            } else {
                $tmp = $tmp."'".$cities[$i]["text"]."'".",";
            }
        }
        $string = $string.$tmp;

        return $string;


    }

    private function createSuburbRequestString($suburbs) {

        if (sizeof($suburbs) == 0) {
            return "";
        }

        $string = " AND suburb in ";

        $tmp = "(";
        $size = sizeof($suburbs);
        for($i = 0; $i < $size; $i++) {
            if ($i == $size - 1) {
                $suburb = $suburbs[$i]["text"];
                $tmp = $tmp."'".$suburb."'".")";
            } else {
                $suburb = $suburbs[$i]["text"];
                $tmp = $tmp."'".$suburb."'".",";
            }
        }
        $string = $string.$tmp;

        return $string;
    }

    public function getLocationCities() {

        $cities = BrandLocation::select('city')->distinct()->orderBy('city')->get();

        return response()->json($cities);

    }

    public function getSuburbs() {

        $suburbs = BrandLocation::select('suburb')->distinct()->orderBy('suburb')->get();

        return response()->json($suburbs);

    }

    public function getProvinces() {

        $provinces = BrandLocation::select('province')->distinct()->orderBy('province')->get();

        return response()->json($provinces);

    }

    public function getJson($brandId, $offset = 0)
    {
        $brandLocations = BrandLocation::with('brand')
        ->with("widget_categories")
        ->with("polygon")
        ->with('dynamic_fields')
        ->with("rating")
            ->where('brandId', $brandId)
            ->where('deleted_at', null)
            ->get();

        $categories = WidgetCategories::pluck("categoriesName","id")->toArray();

        foreach($brandLocations as &$brandLocation){
            $brandcategories = [];

            foreach($brandLocation->widget_categories as $category) {
                $brandcategories[] = $categories[$category->categoriesId];
            }
            $brandLocation->categories = implode(", ",$brandcategories);

            $kmlUrl = $brandLocation->getKmlUrl();
            if($kmlUrl){
                $brandLocation->kmlUrl = $kmlUrl;
            }
        }

        $recordsTotal = count((array) $brandLocations);

        $data['draw'] = 1;
        $data['recordsTotal'] = $recordsTotal;
        $data['recordsFiltered'] = $recordsTotal;
        $data['data'] = $brandLocations;

        return Response::json($data);
    }
    public function postOoh(Request $request)
    {
       $brandId = $request->get('brandId');

        if($brandId== null ){
            $brandId = [1895,1956];
            $brandLocations = BrandLocation::with('brand')
            ->with("widget_categories")
            ->with('dynamic_fields')
            ->whereIn('brandId', $brandId)
            ->where('deleted_at', null)
            ->get();
        }else{
                $brandLocations = BrandLocation::with('brand')
                ->with("widget_categories")
                ->with('dynamic_fields')
                ->whereIn('brandId', $brandId)
                ->where('deleted_at', null)
                ->get();
        }
        $categories = WidgetCategories::pluck("categoriesName","id")->toArray();
        foreach($brandLocations as &$brandLocation){

            if (($brandLocation->dynamic_fields)->isNotEmpty()) {
                for ($i = 0; $i < count($brandLocation->dynamic_fields); $i++) {
                    if ($brandLocation->dynamic_fields[$i]->field_name == "Average_Time_Score" ||
                        $brandLocation->dynamic_fields[$i]->field_name == "Score" ||
                        $brandLocation->dynamic_fields[$i]->field_name == "Vis_Score" ||
                        $brandLocation->dynamic_fields[$i]->field_name == "Eyeball_Score" ||
                        $brandLocation->dynamic_fields[$i]->field_name == "Quality_Score" ||
                        $brandLocation->dynamic_fields[$i]->field_name == "Size_Score" ||
                        $brandLocation->dynamic_fields[$i]->field_name == "Deviation_Score") {

                        $tmp = floatval($brandLocation->dynamic_fields[$i]->field_value);
                        $tmp = round($tmp, 2);
                        $tmp = $tmp * 100;
                        $tmp = strval($tmp)."%";
                        $brandLocation->dynamic_fields[$i]->field_value = $tmp;
                    }

                }
            }else{
                for ($i = 0; $i < 24; $i++) {
                    $brandLocation->dynamic_fields[$i]= ["field_value" => ''];
                }
            }

            $brandcategories = [];

            foreach($brandLocation->widget_categories as $category) {
                $brandcategories[] = $categories[$category->categoriesId];
            }
            $brandLocation->categories = implode(", ",$brandcategories);
        }

        $recordsTotal = count((array) $brandLocations);

        $data['draw'] = 1;
        $data['recordsTotal'] = $recordsTotal;
        $data['recordsFiltered'] = $recordsTotal;
        $data['data'] = $brandLocations;

        return Response::json($data);
    }

    public function getCategories()
    {
        $categories = WidgetCategories::get();
        $recordsTotal = count($categories);

        $data['draw'] = 1;
        $data['recordsTotal'] = $recordsTotal;
        $data['data'] = $categories;

        return Response::json($data);
    }

    public function getStoredCategories()
    {
        $categoriesStored = StoredWidgetCategories::get();

        $data['data'] = $categoriesStored;

        return Response::json($data);
    }

    public function getBrandClient($brandId=null)
    {
        // We limit the results so that it doesn't kill the browser
        //(this will not cause problems because we only using this function to draw locations on map)
        if($brandId){
            $brands = Brand::where('id',$brandId)->orderBy('brandName','ASC')->with('advertiser')->first();
        } else {
            $brands = Brand::with('advertiser')->orderBy('brandName','ASC')->get();
        }

        $recordsTotal = count((array) $brands);

        $data['draw'] = 1;
        $data['recordsTotal'] = $recordsTotal;
        $data['recordsFiltered'] = $recordsTotal;
        $data['data'] = $brands;

        return Response::json($data);
    }

    public function getClientBrands($advertiserId)
    {
        $brands = Advertiser::where('advertiserId', $advertiserId)->with(['brands' => function ($query) {
            return $query->orderBy('brandName', 'ASC');
        }])->first();

        $total = count((array) $brands);

        $data['draw'] = 1;
        $data['total'] = $total;
        $data['totalFiltered'] = $total;
        $data['data'] = $brands;

        return Response::json($data);
    }

    //GET locations/all
    public function getAll()
    {
        $brands = BrandLocation::all();
        $recordsTotal = count($brands);

        $data['draw'] = 1;
        $data['recordsTotal'] = $recordsTotal;
        $data['recordsFiltered'] = $recordsTotal;
        $data['data'] = $brands;

        return Response::json($data);
    }

    public function getCheck($advertiserId)
    {
        return count(Brand::where('AdvertiserId', $advertiserId)->get());
    }
}
