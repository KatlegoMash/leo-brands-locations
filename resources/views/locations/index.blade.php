@extends('master')
@section('header')
<span>Locations</span>
@stop
@section('content')
<style>
	#infoPanel {
		margin-top: 20px;
	}
  #mapCanvas, .mapCanvas {
	padding: 0;
	width:100% !important;
	height: 700px !important;
	border: 1px solid #F7F7F7;
	margin-bottom: 10px;
	margin-top: 10px;
  }

  #closeCategory {
    margin-top: -30px;
  }
  .ui-accordion-content {
      height: auto !important;
  }

  .newLocationClass {
      display: block;
      float: right;
  }

  .marginAuto {
        display: flex;
        justify-content: center;
        align-items: center;
  }

    #newLocationTable_filter {
        float: right;
        text-align: left !important;
    }

    .dt-buttons {
        margin-top:30px;
        margin-left:10px;
    }

</style>
<div class="col-lg-12">
	@php
		$totals_saved = 0;
		$totals_errors = 0;
		if(Session::has('validRows') && count(Session::get('validRows')) > 0){
			$validRows = Session::get('validRows');
			$totals_saved = count($validRows);
		}
		if(Session::has('ErrorsValidation')){
			$validationErrors = Session::get('ErrorsValidation');
			$totals_errors = count($validationErrors);
		}
		if ($totals_errors && $totals_saved) {
			echo "<h3>Proccessed: ".($totals_errors + $totals_saved)."&nbsp;&nbsp; Saved: $totals_saved &nbsp;&nbsp;Errors: $totals_errors</h3>";
		}
	@endphp

	@if(isset($validationErrors))
	<div id="bulk-alert-error-box" class="alert alert-danger alert-dismissable" >
		<?php
		foreach ($validationErrors as $row => $validationErrorRow) {
			$validationErrorRow = json_decode($validationErrorRow);
			foreach ($validationErrorRow as $validationError) {
				foreach ($validationError as $one) {
					echo $row ? "<span>Row <strong>$row</strong>: $one<br/>" : "<span>$one<br/>";
				}
			}
		}
		?>
	</div>
	@endif

	@if(isset($validRows) && count($validRows) > 0)
	<div id="alert-success-box" class="alert alert-success alert-dismissable" >
		<?php
		$validationErrors = $validRows;
		foreach ($validationErrors as $row => $location) {
			echo "<span>Row <strong>$row</strong>: {$location['locationName']} Saved!</span><br/>";
		}
		?>
	</div>
	@endif
    <div id="alert-error-box" class="alert alert-danger" style="display: none">
	</div>
	@if(Session::has('validForm') && Session::get('validForm') == 'success')
		<div id="alert-success-box" class="alert alert-success alert-dismissable" >
			<?php $count = count(Session::get('validRows'))?>
			@if($count == 1)
				Your location has been saved! <span class="fa fa-check"></span>
			@else
				All locations have been saved! <span class="fa fa-check"></span>
			@endif
		</div>
	@endif

	<div class="panel panel-primary">
        
        @include('locations.dataTable.modal')

		<!-- /.panel-body -->
		<div class="panel-body" id="locationPage" style="display: none; margin-bottom: 15px">

			<div class="col-lg-6">
				<div class="form-group @if ($errors->has('locationName')) has-error @endif">
	                {!!Form::label('brandName', 'Brand Name');!!}
	                {!!Form::text('brandName', '', array('class' => 'form-control required', 'id' => 'brandName', 'disabled' => 'disabled'));!!}
	            </div>
				{!!Form::open(array('url' => 'locations/index', 'method' => 'get'))!!}
	            <div class="form-group @if ($errors->has('locationName')) has-error @endif">
	                {!!Form::label('locationName', 'Location Name');!!}
	                {!!Form::text('locationName', '', array('class' => 'form-control required', 'id' => 'locationName'));!!}
	                {!!Form::hidden('brandId', '', array('id' => 'brandId'));!!}
	            </div>
	            <div class="form-group @if ($errors->has('latitude')) has-error @endif">
	                {!!Form::label('latitude', 'Latitude');!!}
	                {!!Form::text('latitude', '', array('class' => 'form-control required','id' => 'latitude'));!!}
	            </div>
	            <div class="form-group @if ($errors->has('longitude')) has-error @endif">
	                {!!Form::label('longitude', 'Longitude');!!}
	                {!!Form::text('longitude', '', array('class' => 'form-control required','id' => 'longitude'));!!}
	            </div>
	            <div class="form-group @if ($errors->has('storeName')) has-error @endif">
	                {!!Form::label('storeName', 'Store Name');!!}
	                {!!Form::text('storeName', '', array('class' => 'form-control required', 'id' => 'storeName'));!!}
	            </div>
	            <div class="form-group @if ($errors->has('storeCode')) has-error @endif">
	                {!!Form::label('storeCode', 'Store Code');!!}
	                {!!Form::text('storeCode', '', array('class' => 'form-control required','id' => 'storeCode'));!!}
	            </div>
	            <div class="form-group @if ($errors->has('addressLine1')) has-error @endif">
	                {!!Form::label('addressLine1', 'Address Line 1');!!}
	                {!!Form::text('addressLine1', '', array('class' => 'form-control required','id' => 'addressLine1'));!!}
				</div>
				<div class="form-group @if ($errors->has('locationBankId')) has-error @endif">
					{!!Form::label('locationBankId', 'Location Bank Id');!!}
					{!!Form::text('locationBankId', '', array('class' => 'form-control required', 'placeholder' => 'Location Bank Id'));!!}
				</div>
	        </div>

	        <div class="col-lg-6">
	            <div class="form-group @if ($errors->has('addressLine2')) has-error @endif">
	                {!!Form::label('addressLine2', 'Address Line 2');!!}
	                {!!Form::text('addressLine2', '', array('class' => 'form-control required','id' => 'addressLine2'));!!}
	            </div>
	            <div class="form-group @if ($errors->has('postalZipCode')) has-error @endif">
	                {!!Form::label('postalZipCode', 'Postal Zip Code');!!}
	                {!!Form::text('postalZipCode', '', array('class' => 'form-control required','id' => 'postalZipCode'));!!}
	            </div>
	            <div class="form-group @if ($errors->has('city')) has-error @endif">
	                {!!Form::label('city', 'City');!!}
	                {!!Form::text('city', '', array('class' => 'form-control required', 'id' => 'city'));!!}
	            </div>
	            <div class="form-group @if ($errors->has('countryCode')) has-error @endif">
					{!!Form::label('countryCode', 'Country');!!}
					{!!Form::select('countryCode', DB::table('country')->pluck('country','code'), null, array('class' => 'form-control selectpicker required', 'id' => 'countryCode', 'data-live-search' => 'true', 'data-live-search-placeholder'=>'Search', 'autocorrect'=>'off'));!!}
				</div>
	            <div class="form-group @if ($errors->has('homePage')) has-error @endif">
	                {!!Form::label('homePage', 'Home Page');!!}
	                {!!Form::text('homePage', '', array('class' => 'form-control required', 'id' => 'homePage'));!!}
	            </div>
	            <div class="form-group @if ($errors->has('maxGeofence')) has-error @endif">
	                {!!Form::label('maxGeofence', 'Geo fence in meters');!!}
	                {!!Form::text('maxGeofence', '', array('class' => 'form-control required','id' => 'maxGeofence'));!!}
	            </div>
	            <div class="form-group @if ($errors->has('phone')) has-error @endif">
	                {!!Form::label('phone', 'Phone number');!!}
	                {!!Form::text('phone', '', array('class' => 'form-control required','id' => 'phone'));!!}
	            </div>
	        </div>
			
			<div class="col-lg-12 px-0">
				<input type="hidden" id="passDynamicFields"/>
				<div id="dynamicFields" class=""></div>
			</div>
			{!! Form::close() !!}

			<div class="col-lg-12 px-0">
				<hr style="height:1px;border:none;color:#333;background-color:#333;">
				<p style="color: #337ab7;">Drag and Drop the pin to your New Location and Click Confirm Button</p>
			</div>

			<div id="infoPanel" class="alert alert-info col-lg-12">
				<b>Marker status:</b>
				<div id="markerStatus"><i>Click and drag the marker.</i></div>
				<b>Current position:</b>

				<div id="info"></div>
				<b>Closest matching address:</b>
				<div id="address"></div>
				<div><button type="submit" name="submit" id="confirm" class="btn btn-primary" >Confirm Coordinates</button></div>
				<br>
			</div>

			<div class="col-lg-12 px-0">
				
				<button type="button" class="btn btn-success" id="update">Save Changes</button>
				<button type="button" class="btn btn-danger" id="cancelUpdate">Cancel</button>
			</div>

			<div class="col-lg-12 px-0">
				<div id="mapCanvas" class="mapCanvas"></div>
			</div>
		</div>
	</div>
	<!-- /.panel -->
</div>
<!-- /.col-lg-12 -->

@include('locations.categories.modal')

@include('locations.tags.modal')

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Confirm Remove Location</h4>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete the location(s)?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal" id="remove-location">Remove</button>
      </div>
    </div>
  </div>
</div>
<!-- /#Modal -->
<!-- Modal -->
<div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myupdateModalLabel">Update Locations</h4>
      </div>
      <div class="modal-body">
			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-primary">
						<div class="panel-heading">Update Locations</div>
						<div class="panel-body">
							{!! Form::open(array('url' => 'locations/import', 'method' => 'post', 'files' => true)) !!}
							<div class="form-group">
								<em class="text-primary text-left "><strong>Download Sample:</strong></em>
								<a id="locationsSampleExcel" href="{!!asset('downloads/LocationsTestUpdate.xlsx');!!}">LocationsTestUpdate.xlsx</a>
							</div> {!!Form::label('sheet', 'Select SpreadSheet to upload');!!}<br>
							<div class="btn btn-default btn-file excel-container">
							{!!Form::file('spreadsheet',array('id'=>'spreadsheet-input'));!!} </div>
							<br><br>
							<div class="form-group">
								<em class="text-primary text-left "><strong>32Mb Maximum file size</strong></em>
							</div>
							<input type="hidden" name="update" value="true">
							<input type="hidden" id="selected_ids" name="selected_ids" value="">
							<input type="hidden" id="brandIdFields" name="brandId" value="">
							<hr> {!!Form::submit('Update',array('class' => 'btn btn-success','id'=>'upload-submit-btn'));!!}
							<!-- <a class="btn btn-danger"
							href="#">Cancel</a>  -->
							<button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
							{!!Form::close()!!}
						</div>
					</div>
				</div>
			</div>
		</div>
    </div>
  </div>
</div>
<!-- /#Modal -->


<!-- Modal Address Changes-->
<div class="modal fade" id="addressChanges" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Updating Addresses</h4>
      </div>
      <div class="modal-body">
        <p>Address(s) changes has been saved, once you done with updating the location droll down to save changes of this location</p>
      </div>
    </div>
  </div>
</div>
<!-- /#Modal Coordinate Changes-->

<!-- Modal Address Changes-->
<div class="modal fade" id="coordinatesChanges" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Updating Coordinates</h4>
      </div>
      <div class="modal-body">
        <p>Coordinate(s) changes has been saved, once you done with updating the location scroll down to save changes of this location</p>
      </div>
    </div>
  </div>
</div>
<!-- /#Modal Coordinate Changes-->
<style>

	.panel-body {

		margin-bottom: 15px;
	}
	#locations_wrapper .dt-buttons {
		position: absolute!important;
		right: 214px;
		top: 35px;
	}
	.buttons-select-all {
		margin-bottom: 5px !important;
	}
	.search-bar {
		margin-top: 5px;
	}
	.btn-export-update {
		visibility: hidden;

	}
</style>
@stop
@section('script')
@include('locations.categories.js')
@include('locations.tags.js')



{{-- start of the scritps --}}


<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css"/>
<link rel="text/css" href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.18/af-2.3.0/b-1.5.2/b-colvis-1.5.1/b-flash-1.5.2/b-html5-1.5.2/b-print-1.5.2/cr-1.5.0/fc-3.2.5/fh-3.1.4/kt-2.4.0/r-2.2.2/rg-1.0.3/rr-1.2.4/sc-1.5.0/sl-1.2.6/datatables.min.css"/>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.18/af-2.3.0/b-1.5.2/b-colvis-1.5.1/b-flash-1.5.2/b-html5-1.5.2/b-print-1.5.2/cr-1.5.0/fc-3.2.5/fh-3.1.4/kt-2.4.0/r-2.2.2/rg-1.0.3/rr-1.2.4/sc-1.5.0/sl-1.2.6/datatables.min.js"></script>
@if(Config::get('app.debug'))
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCR6McojSiuzxttFPB4rEbN9v_qTG9sD1M"></script>
@else
<script src="https://maps.googleapis.com/maps/api/js?key={!!Config::get('google.maps.key')!!}"></script>
@endif

<script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>

{{-- <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/vue@2.6.14"></script> --}}

{{-- All these are needed for the locations table --}}
<script src="{!!asset('js/locations.js')!!}"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.7.14/dist/vue.js"></script>
<script type="text/javascript" src="https://unpkg.com/@johmun/vue-tags-input/dist/vue-tags-input.js"></script>
{{-- End --}}

<script>
// Map for locationPage Starts here
// Map to update coordinates
var geocoder = new google.maps.Geocoder();
var marker
var addressC
var splitter
var latLng
var map
var addressTwo = [];
var categories = {!!$categories!!};
var clientType = "{{ $brand->advertiser->clientType ?? ''}}";

function geocodePosition(pos) {
  geocoder.geocode({
    latLng: pos
  }, function(responses) {
    if (responses && responses.length > 0) {
      updateMarkerAddress(responses[0].formatted_address);
    } else {
      updateMarkerAddress('Cannot determine address at this location.');
    }
  });
}

function updateMarkerStatus(str) {
  document.getElementById('markerStatus').innerHTML = str;

}

function updateMarkerPosition(latLng) {
  document.getElementById('info').innerHTML = [
    latLng.lat(),
    latLng.lng()
  ].join(', ');
}

function updateMarkerAddress(str) {
  document.getElementById('address').innerHTML = str;
  addressC = str;
}

function updateLocations() {
	var ids = [];
	var rows = table.rows('.selected').data();

	if(rows.length == 0){
		rows = table.rows().data();
	}

	$.each(rows,function(index,row){
		ids.push(row.id);
	})

	$('#selected_ids').val(JSON.stringify(ids)); //store array
	$('#brandIdFields').val($('#brandId').selectpicker('val'));


	if($('#brandId').selectpicker('val') == "default" || ids.length == 0 ) {
		swal("Please select a brand to update")
		return false
	}
	$('#updateModal').modal('toggle');

	$( ".btn-export-update" ).click()

}

function initialize() {

	// var latitudesD = marker.getPosition().lat();
	// var longitudeD = marker.getPosition().lng();

	latLng = new google.maps.LatLng($("#latitude").val(),$("#longitude").val());
	map = new google.maps.Map(document.getElementById('mapCanvas'), {
		zoom: 7,
		center: latLng,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	});

	marker = new google.maps.Marker({
		position: latLng,
		title: 'Current Location',
		map: map,
		draggable: true
	});

	circle = new google.maps.Circle({
		map: map,
		radius: parseFloat($("#maxGeofence").val()),
		strokeColor: '#FF0000',
		fillColor: '#00FF00'
	});

	circle.bindTo('center', marker, 'position');

	//Update current position info.
	updateMarkerPosition(latLng);
	geocodePosition(latLng);

	// Add dragging event listeners.
	google.maps.event.addListener(marker, 'dragstart', function() {
		updateMarkerAddress('Dragging...');
		var updateA = updateMarkerAddress('Dragging...');
	});

	google.maps.event.addListener(marker, 'drag', function() {
		updateMarkerStatus('Dragging...');
		updateMarkerPosition(marker.getPosition());
	});

	google.maps.event.addListener(marker, 'dragend', function() {
		updateMarkerStatus('Drag ended');
		var latitudes = marker.getPosition().lat();
		var longitude = marker.getPosition().lng();
		geocodePosition(marker.getPosition());
	});

}

function isInt(value) {
  return !isNaN(value) &&  parseInt(Number(value)) == value &&  !isNaN(parseInt(value, 10));
}


$(document).ready(function() {

	$('#creates').hide();

    //this is table creation
    table = initializeTable();

	$('#remove-location').click( function () {
		var count = table.rows('.selected').data().length;

		$rows = table.rows('.selected').data();
		var compare = 0;
		var ids = [];

		$.each($rows,function(index,row){
			ids.push(row.id);
		});

		if(ids.length == 0){
			swal("Select at least one location");
			return false;
		}

		$.ajax({
			type: 'DELETE',
			data: {
				ids:ids.join()
			},
			beforeSend: function(){
				showLoader(true);
			},
			url:  "{!!action('BrandLocationController@deleteLocation')!!}",
			success: function(data) {

				if(data.campaigns.length >= 1){
					var msg = "Could not delete the selected locations.\nThey are linked to the following campaigns:\n";
					msg += "Total Linked Campaigns: "+data.campaigns.length;
					msg += "\n";
					for(var i = 0;i < data.campaigns.length;i++){
						msg += "\nName: "+data.campaigns[i].name;
						msg += "\nLink: https://leo.vic-m.co/campaigns?campaignId="+data.campaigns[i].id;
						msg += "\n";
						msg += "\n";
					}
					swal({
						title: "Linked to Active Campaigns",
						text:msg,
						icon: 'warning'
					});
				}else{
					$.each($rows,function(index,row){
						table.row('.selected').remove();
					});
					table.row('.selected').draw( false );


					swal("Location(s) was successfully deleted");
				}
				showLoader(false,$('#locationList'));
			},
			error: function(xhr){
				showLoader(false,$('#locationList'));
			}
		});

	});

	$("#cancelUpdate").click(function(){
		window.location.href = "/locations?brandId="+$('#brandId').val();
	});

	$("#new-location-id").click(function(){
		event.preventDefault()
		var brandId =  $('select[name=brandId]').val();
		var advertiserId = $('select[name=brandId] option:selected').attr('advertiserId');
		var url = '/locations/edit?brandId='+brandId+"&advertiserId="+advertiserId;

		if(brandId != "default"){
			@if(isset($brand->advertiser->clientType) && $brand->advertiser->clientType === "widget")
				window.location.href = url.slice(0,-9);
			@else
				window.location.href = url;
			@endif
		}else{
			swal('Please select a brand');
		}
		return false;
	});

	$("#latlong").on('click', function() {
		$('#latitude').val('');
		$('#latitude').val($('#latitude1').val());
		$('#longitude').val($('#longitude1').val());
		initialize();
	});

	$("#add").on('click', function() {
		$('#addressLine1').val('');
		$('#addressLine1').val($('#addressLine11').val());
		$('#addressLine2').val($('#addressLine22').val());
	});

	$("#confirm").on('click', function() {
		var latitudes = marker.getPosition().lat();
		var longitude = marker.getPosition().lng();

		$('#latitude').val(latitudes );
		$('#longitude').val(longitude );
		splitter = addressC.split(",");
		var addressOne = splitter[0];
		$('#addressLine1').val(addressOne );

		splitter.shift()
		var goGetNewAddress = splitter.toString();
		$('#addressLine2').val(goGetNewAddress );

		swal("Are you sure you want to save the changes?");
	});

	$("#edit-location").on('click', function() {
		var count = table.rows('.selected').data().length;
		if( count == 1 ){
			$row = table.row('.selected').data();

			showLoader(true);

			setTimeout(() => {
				$('#locationName').val( $row.locationName );
				$('#storeName').val( $row.storeName );
				$('#storeCode').val( $row.storeCode );
				$('#city').val( $row.city );
				$('#homePage').val( $row.homePage );
				$('#addressLine1').val( $row.addressLine1 );
				$('#addressLine2').val( $row.addressLine2 );
				$('#postalZipCode').val( $row.postalZipCode );
				$('#storeCode').val( $row.storeCode );
				$('#latitude').val( $row.latitude );
				$('#longitude').val( $row.longitude );
				$('#maxGeofence').val( $row.maxGeofence );
				$('#countryCode').selectpicker( 'val' , $row.countryCode );
				$('#addressLine2').val( $row.addressLine2 );
				$('#postalZipCode').val( $row.postalZipCode );
				$('#storeCode').val( $row.storeCode );
				$('#latitude').val( $row.latitude );
				$('#longitude').val( $row.longitude );
				$('#maxGeofence').val( $row.maxGeofence );
				$('#brandName').val( $row.brandName );
				$('#phone').val( $row.phone )
				$('#brandId').val( $row.brandId );
				$('#locationBankId').val( $row.locationBankId );

				
				if($row.dynamic_fields && $row.dynamic_fields.length > 0){
					let dynamicNames = [];

					$row.dynamic_fields.map((dynamicField) => {
						let {field_name, field_value} = dynamicField
						dynamicNames = [...dynamicNames, field_name]
						let dynamicFormField = 
							`<div class='col-lg-6 px-0'>
								<div class='form-group col-lg-12'>
									<label>${field_name}</label>
									<input type='text' name='${field_name}' id='dynamic_${field_name}' value='${field_value}' class='form-control'/>
								</div>
							</div>` 
						$('#dynamicFields').append(dynamicFormField);
					})

					$('#passDynamicFields').val(dynamicNames)
				}



				initialize();
				showLoader(false,$('#locationPage'));

			}, 800);

		} else {
			swal("Please select one record you wish to update");
			showLoader(false,$('#locationList'));
		}
	});

	$('#update').click( function () {
		$("#alert-error-box").hide();

        $row = table.row('.selected').data();
        locationName = $('#locationName').val();
        storeName = $('#storeName').val();
        storeCode = $('#storeCode').val();
        city = $('#city').val();
        homePage = $('#homePage').val();
        addressLine1 = $('#addressLine1').val();
        addressLine2 = $('#addressLine2').val();
        postalZipCode = $('#postalZipCode').val();
        latitude = $('#latitude').val();
        longitude = $('#longitude').val();
        countryCode = $('#countryCode').val();
        locationName = $('#locationName').val();
        storeName = $('#storeName').val();
        storeCode = $('#storeCode').val();
        maxGeofence = $('#maxGeofence').val();
        phone = $('#phone').val();
        brandId = $('#brandId').val();
        locationBankId = $('#locationBankId').val();

		let dynamicFields = [];

		let passDynamicFields =$('#passDynamicFields').val();
		
		passDynamicFields.split(',').map((dynamicFieldName) => {
			let dynamicFieldValue = $(`#dynamic_${dynamicFieldName}`).val()

			dynamicFields = [...dynamicFields, {
				'fieldName': dynamicFieldName,
				'fieldValue': dynamicFieldValue 
			}]
		})

        if($row.id > 0){

	        $.ajax({
	            type: 'POST',
				url:  "{!!action('BrandLocationController@postUpdate')!!}/"+$row.id,
				beforeSend: function(){
					showLoader(true);
				},
	            data: { addressLine1: addressLine1,city: city, locationBankId: locationBankId, storeName: storeName,storeCode: storeCode,locationName:locationName,maxGeofence:maxGeofence,
	                postalZipCode:postalZipCode,addressLine2:addressLine2,countryCode:countryCode,latitude:latitude,longitude:longitude,homePage:homePage,phone:phone,brandId:brandId, dynamicFields: dynamicFields, ajax:true}
	        }).success(function(data){

				if(Object.keys(data).length > 0){
					$("#alert-error-box").html('').show();

					for(value in data){
						$("#alert-error-box").append(data[value]+"<br/>");
					}

				} else {
					window.location.href = '/locations?brandId='+brandId;
					return false;
				}

				showLoader(false,$('#locationPage'));
				return false;

			}).fail(function(xhr){
				showLoader(false,$('#locationPage'));
			})

			return false;
        }

	});

	$('#maxGeofence').change(function() {
		if (isInt($(this).val())) {
			circle.setRadius(parseInt($(this).val()));
		}
	});

	$('#ToolTables_locations_1').click( function (event) {
		event.stopPropagation();
	});

	$('#map-container').click(function () {
		$('#map-container #map').css("pointer-events", "auto");
	});

	$('#map-container').mouseleave(function() {
		$('#map-container #map').css("pointer-events", "none");
	});

    $("#linkedCategories").accordion()

});

</script>
@stop
