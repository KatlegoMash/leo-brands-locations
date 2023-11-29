<div class="panel-heading">
    Locations
</div>
<div class="panel-body" id="loader">
    @include("loading-square",array("name"=>"cover-no-click","display"=>"block"))
</div>
<div class="panel-body" id="locationList" style="display: none;">
    <div class="row" id="brandLocationsVueComponent" style="margin: 10px;">
        <div class="row">
            <div class="col-3">
                <h6>Brand:</h6>
            </div>
            <div class="col-3">
                <h6>Location Name:</h6>
            </div>
            <div class="col-3">
                <h6>Province:</h6>
            </div>
            <div class="col-3">
                <h6>Label:</h6>
            </div>
        </div>
        <div class="row">
            <div class="col-3">


                <vue-tags-input
                    v-model="brandTag"
                    :tags="brands"
                    :autocomplete-items="filteredBrandTags"
                    :add-only-from-autocomplete="true"
                    :placeholder="brandPlaceholder"
                    autocomplete-min-length="0"
                    @tags-changed="newTags => brands = newTags"
                />
            </div>
            <div class="col-3">

                <vue-tags-input
                    v-model="locationTag"
                    :tags="locationTags"
                    :placeholder="locationPlaceholder"
                    @tags-changed="newTags => locationTags = newTags"
                />
            </div>
            <div class="col-3">

                <vue-tags-input
                    v-model="provinceTag"
                    :tags="provinceTags"
                    :autocomplete-items="filteredProvinceTags"
                    :add-only-from-autocomplete="true"
                    :placeholder="provincePlaceholder"
                    autocomplete-min-length="0"
                    @tags-changed="newTags => provinceTags = newTags"
                />
            </div>
            <div class="col-3">

                <vue-tags-input
                    v-model="labelTag"
                    :tags="labelTags"
                    :autocomplete-items="filteredLabelTags"
                    :add-only-from-autocomplete="true"
                    :placeholder="labelPlaceholder"
                    autocomplete-min-length="0"
                    @tags-changed="newTags => labelTags = newTags"
                />
            </div>
        </div>
        <div id="secondaryHeaders" class="row" style="display:none;">
            <div class="col-3">
                <h6>City:</h6>
            </div>
            <div class="col-3">
                <h6>Suburb:</h6>
            </div>
            <div class="col-3">
                <h6>Demographic Data:</h6>
            </div>
        </div>
        <div class="row">
            <div class="col-3">
                <vue-tags-input
                    v-show="cityHidden"
                    v-model="cityTag"
                    :tags="cityTags"
                    :autocomplete-items="filteredCityTags"
                    :add-only-from-autocomplete="true"
                    :placeholder="cityPlaceholder"
                    autocomplete-min-length="0"
                    @tags-changed="newTags => cityTags = newTags"
                />
            </div>
            <div class="col-3">
                <vue-tags-input
                    v-show="suburbHidden"
                    v-model="suburbTag"
                    :tags="suburbTags"
                    :autocomplete-items="filteredSuburbTags"
                    :add-only-from-autocomplete="true"
                    :placeholder="suburbPlaceholder"
                    autocomplete-min-length="0"
                    @tags-changed="newTags => suburbTags = newTags"
                />
            </div>
            <div class="col-3 d-flex align-items-center">
                <button class="btn btn-primary slim" v-show="cityHidden" v-on:click="showDemographics()" id="demographic-toggle"><span class="fa fa-plus"></span></button>
            </div>
        </div>
        <div class="row" v-show="demographicsHidden">
            <div class="col-3">
                <h6>Date:</h6>
            </div>
            <div class="col-3">
                <h6>Race:</h6>
            </div>
            <div class="col-3">
                <h6>Language:</h6>
            </div>
            <div class="col-3">
                <h6>In-Market:</h6>
            </div>
        </div>
        <div class="row" v-show="demographicsHidden">
            <div class="col-3">
            <div id="reportrange" class="form-control form-control-solid">
                <i class="fa fa-calendar"></i>&nbsp;
                <span></span> <i class="fa fa-caret-down"></i>
            </div>
                <!--<div class="form-control form-control-solid d-flex">
                    <input type="text" id="datepickerFrom" name="from" class="w-50 border-0 bg-transparent p-2 text-center">
                    <span>-</span>
                    <input type="text" id="datepickerTo" name="to" class="w-50 border-0 bg-transparent p-2 text-center">
                </div>-->
            </div>
            <div class="col-3">
                <vue-tags-input
                    v-show="demographicsHidden"
                    v-model="raceTag"
                    :autocomplete-items="filteredRaceTags"
                    :add-only-from-autocomplete="true"
                    :tags="raceTags"
                    :placeholder="racePlaceholder"
                    autocomplete-min-length="0"
                    @tags-changed="newTags => raceTags = newTags"
                />
            </div>
            <div class="col-3">
                <vue-tags-input
                    v-show="demographicsHidden"
                    v-model="languageTag"
                    :autocomplete-items="filteredLanguageTags"
                    :add-only-from-autocomplete="true"
                    :tags="languageTags"
                    :placeholder="languagePlaceholder"
                    autocomplete-min-length="0"
                    @tags-changed="newTags => languageTags = newTags"
                />
            </div>
            <div class="col-3">
                <vue-tags-input
                    v-show="demographicsHidden"
                    v-model="inMarketTag"
                    :autocomplete-items="filteredInMarketTags"
                    :add-only-from-autocomplete="true"
                    :tags="inMarketTags"
                    :placeholder="inMarketPlaceholder"
                    autocomplete-min-length="0"
                    @tags-changed="newTags => inMarketTags = newTags"
                />
            </div>
        </div>
        <div class="row" v-show="demographicsHidden">
            <div class="col-2">
                <h6>Ppl/household:</h6>
            </div>
            <div class="col-2">
                <h6>Age:</h6>
            </div>
            <div class="col-2">
                <h6>Income/Mo (R):</h6>
            </div>
            <div class="col-2">
                <h6>Parents:</h6>
            </div>
            <div class="col-2">
                <h6>Children:</h6>
            </div>
            <div class="col-2">
                <h6>Gender:</h6>
            </div>
        </div>
        <div class="row" v-show="demographicsHidden">
            <div class="col-2">
                <div id="slider-range-household"></div><p>
                <input type="text" id="amount-household" readonly style="border:0; color:#f6931f; font-weight:bold;"></p>
            </div>
            <div class="col-2">
                <div id="slider-range-age"></div><p>
                <input type="text" id="amount-age" readonly style="border:0; color:#f6931f; font-weight:bold;"></p>
            </div>
            <div class="col-2">
                <div id="slider-range-income"></div><p>
                <input type="text" id="amount-income" readonly style="border:0; color:#f6931f; font-weight:bold;"></p>
            </div>
            <div class="col-2">
                <div id="slider-range-parents"></div><p>
                <input type="text" id="amount-parents" readonly style="border:0; color:#f6931f; font-weight:bold;"></p>
            </div>
            <div class="col-2">
                <div id="slider-range-children"></div><p>
                <input type="text" id="amount-children" readonly style="border:0; color:#f6931f; font-weight:bold;"></p>
            </div>
            <div class="col-2 gender">
                <a class="btn btn-primary fa fa-times" nohref title="all" name="all"></a>
                <a class="btn fa fa-male" nohref title="male" name="male"></a>
                <a class="btn fa fa-female" nohref title="female" name="female"></a>
            </div>
        </div>
    </div>
    <div class="row" >
        <div class="col-lg-12">
                <table id="newLocationTable" class="display table table-striped table-bordered table-hover dataTable no-footer" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Brand</th>
                            <th>Location Name</th>
                            <th>City</th>
                            <th>Suburb</th>
                            <th>Province</th>
                            <th>Country</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>MaxGeofence</th>
                            <th>Address Line 1</th>
                            <th>Address Line 2</th>
                            <th>Phone</th>
                            <th>Home Page</th>
                            <th>Store Name</th>
                            <th>Store Code</th>
                            <th>Postal Zip Code</th>
                            <th>Country Code</th>
                            <th>Location Bank Id</th>
                            <th>Google Place Id</th>
                            <th>Category</th>
                            <th>Rating</th>
                            <th>Labels</th>
                            <th>Select</th>
                            <th>Visit Score</th>
                            <th>Show More</th>
                        </tr>

                    </thead>
                    <tfoot>
                        <tr>
                            <th>Id</th>
                            <th>Brand</th>
                            <th>Location Name</th>
                            <th>City</th>
                            <th>Suburb</th>
                            <th>Province</th>
                            <th>Country</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>MaxGeofence</th>
                            <th>Address Line 1</th>
                            <th>Address Line 2</th>
                            <th>Phone</th>
                            <th>Home Page</th>
                            <th>Store Name</th>
                            <th>Store Code</th>
                            <th>Postal Zip Code</th>
                            <th>Country Code</th>
                            <th>Location Bank Id</th>
                            <th>Google Place Id</th>
                            <th>Category</th>
                            <th>Rating</th>
                            <th>Labels</th>
                            <th>Select</th>
                            <th>Visit Score</th>
                            <th>Show More</th>
                        </tr>
                    </tfoot>
                </table>
        </div>
    </div>
  <!-- /.row (nested) -->
    <hr>
    @if(Request::path() == "locations")
        <button class="btn btn-primary" id="new-location-id">New Location</button>
        <button type="submit" class="btn btn-secondary" id="edit-location">Edit Location</button>
        <button type="submit" class="btn btn-danger" data-toggle="modal" data-target="#myModal">Remove Location</button>
        <button class="btn btn-primary" onclick="updateLocations()" >Update Location</button>
        <button class="btn btn-warning" onclick="linkCategories()" data-toggle="modal">Categorize Location</button>
    @endif
    <hr>
    <div id="map-container" class="mapCanvas">
        <div id="map"></div>
    </div>
    <div>
        <button class="btn btn-primary" onclick="toggleGeofence()">Toggle Geofence</button>
    </div>

</div>
