@extends('master')
@section('header')
	<span>Locations</span>
@stop
@section('content')
	<style>
	#mapCanvas {
		padding: 0;
		width:100% !important;
		height: 700px !important;
		border: 1px solid #F7F7F7;
		margin-bottom: 10px;
		margin-top: 10px;
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
		<div id="alert-error-box" class="alert alert-danger alert-dismissable" >
			<?php
			foreach ($validationErrors as $row=>$validationErrorRow) {
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
            foreach ($validationErrors as $row=>$location) {
                echo "<span>Row <strong '>$row</strong>: {$location['locationName']} Saved!</span><br/>";
            }
            ?>
		</div>
		@endif

		<ul class="nav nav-tabs">
			<li class="active"><a href="#single-location" data-toggle="tab">Add Location</a>
			</li>
			<li><a href="#upload" data-toggle="tab">Upload Locations</a>
			</li>
		</ul>

		<div class="tab-content">
			<div class="tab-pane fade in active" id="single-location">
				<br/>
				<div class="row">
					<div class="col-lg-12">
						<div class="panel panel-primary">
							<div class="panel-heading">
								Add Location
							</div>
							<div class="panel-body">
								{!!Form::open(array('url' => 'locations/new', 'method' => 'post'))!!}
								<div class="col-lg-6">
									<div class="form-group">
										{!!Form::label('advertiserId', 'Client (Agency):');!!}
										{!!Form::select('advertiserId', \App\Advertiser::orderBy('advertiserName', 'ASC')->pluck('advertiserName', 'advertiserId'),null, array('class'=>'form-control selectpicker',  'data-live-search' => 'true', 'data-live-search-placeholder'=>'Search', 'autocorrect'=>'off'));!!}
									</div>
									<div class="form-group">
										{!!Form::label('brandId', 'Brand (Advertiser):');!!}
										{!!Form::select('brandId', array(),null, array('class'=>'form-control brands-select'));!!}
									</div>
									<div class="form-group @if ($errors->has('locationName')) has-error @endif">
										{!!Form::label('locationName', 'Location Name');!!}
										{!!Form::text('locationName', '', array('class' => 'form-control required', 'placeholder' => 'locationName'));!!}
									</div>
									<div class="form-group @if ($errors->has('latitude')) has-error @endif">
										{!!Form::label('latitude', 'Latitude ( Scroll down set Latitude and Longitude using map)');!!}
										{!!Form::text('latitude', '', array('class' => 'form-control required','placeholder' => 'latitude'));!!}
									</div>
									<div class="form-group @if ($errors->has('longitude')) has-error @endif">
										{!!Form::label('longitude', 'Longitude');!!}
										{!!Form::text('longitude', '', array('class' => 'form-control required','placeholder' => 'longitude'));!!}
									</div>
									<div class="form-group @if ($errors->has('storeName')) has-error @endif">
										{!!Form::label('storeName', 'Store Name');!!}
										{!!Form::text('storeName', '', array('class' => 'form-control required', 'placeholder' => 'storeName'));!!}
									</div>
									<div class="form-group @if ($errors->has('storeCode')) has-error @endif">
										{!!Form::label('storeCode', 'Store Code');!!}
										{!!Form::text('storeCode', '', array('class' => 'form-control required','placeholder' => 'storeCode'));!!}
									</div>
									<div class="form-group @if ($errors->has('addressLine1')) has-error @endif">
										{!!Form::label('addressLine1', 'Address Line 1');!!}
										{!!Form::text('addressLine1', '', array('class' => 'form-control required','placeholder' => 'addressLine1'));!!}
									</div>

								</div>
								<div class="col-lg-6">
									<div class="form-group @if ($errors->has('addressLine2')) has-error @endif">
										{!!Form::label('addressLine2', 'Address Line 2');!!}
										{!!Form::text('addressLine2', '', array('class' => 'form-control required','placeholder' => 'addressLine2'));!!}
									</div>
									<div class="form-group @if ($errors->has('postalZipCode')) has-error @endif">
										{!!Form::label('postalZipCode', 'Postal Zip Code');!!}
										{!!Form::text('postalZipCode', '', array('class' => 'form-control required','placeholder' => 'postalZipCode'));!!}
									</div>
									<div class="form-group @if ($errors->has('city')) has-error @endif">
										{!!Form::label('city', 'City');!!}
										{!!Form::text('city', '', array('class' => 'form-control required', 'placeholder' => 'city'));!!}
									</div>
									<div class="form-group @if ($errors->has('countryCode')) has-error @endif">
										{!!Form::label('countryCode', 'Country');!!}
										{!!Form::select('countryCode', DB::table('country')->pluck('country','code'), null, array('class' => 'form-control selectpicker required', 'id' => 'countryCode', 'data-live-search' => 'true', 'data-live-search-placeholder'=>'Search', 'autocorrect'=>'off'));!!}
									</div>
									<div class="form-group @if ($errors->has('homePage')) has-error @endif">
										{!!Form::label('homePage', 'Home Page');!!}
										{!!Form::text('homePage', '', array('class' => 'form-control required', 'placeholder' => 'homePage'));!!}
									</div>
									<div class="form-group @if ($errors->has('maxGeofence')) has-error @endif">
										{!!Form::label('maxGeofence', 'Geo fence in meters');!!}
										{!!Form::text('maxGeofence', '', array('class' => 'form-control required','placeholder' => 'maxGeofence'));!!}
									</div>
									<div class="form-group @if ($errors->has('phone')) has-error @endif">
										{!!Form::label('phone', 'Phone number');!!}
										{!!Form::text('phone', '', array('class' => 'form-control required','placeholder' => 'Enter phone number e.g 0118886754'));!!}
									</div>
									<div class="form-group @if ($errors->has('locationBankId')) has-error @endif">
										{!!Form::label('locationBankId', 'Location Bank ID');!!}
										{!!Form::text('locationBankId', '', array('class' => 'form-control required', 'placeholder' => 'Location Bank ID'));!!}
									</div>
								</div>
								<hr>
								<div class="col-lg-12">
									<hr style="height:1px;border:none;color:#333;background-color:#333;">
									<div class="alert alert-info" role="alert">
										<div id="infoPanel">
											<b>Marker status:</b>
											<div id="markerStatus"><i>Click and drag the marker.</i></div>
											<b>Current position:</b>
											<div id="info"></div>
											<b>Closest matching address:</b>
											<div id="address"></div>
											<div>
												<button type="button" name="submit" id="confirm" class="btn btn-primary" >Confirm Coordinates</button>
											</div>
											<br>
										</div>
									</div>
									<div class="btn-group">
										<button type="submit" name="addLocation" value="true" class="btn btn-success" id="addLocation">Save Location</button>
										<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    										<span class="caret"></span>
											<span class="sr-only">Toggle Dropdown</span>
										</button>
										<ul class="dropdown-menu">
											<button type="submit" name="addAndNewLocation" value="true" class="btn btn-success" id="addAndNewLocation">Save and New Location</button>
										</ul>
									</div>
									<button type="button" class="btn btn-danger" onclick="window.location='{!!url("locations")!!}'" id="cancelUpdate">Cancel</button>
									<hr>
									<p style="color: #337ab7;">Drag and Drop the pin to your New Location and Click Confirm Button</p>
									<div id="mapCanvas"></div>
									{!!Form::close()!!}
								</div>

							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="tab-pane fade" id="upload">
				<br/>
				<div class="row">
					<div class="col-lg-12">
						<div class="panel panel-primary">
							<div class="panel-heading">
								Upload Locations
							</div>
							<div class="panel-body">
							{!! Form::open(array('url' => 'locations/import', 'method' => 'post', 'files' => true)) !!}
								{!!Form::label('brandId', 'Select Brand');!!}
								<div class="@if ($errors->has('brandId')) has-error @endif">
									{!!Form::select('brandId', \App\Brand::orderBy('brandName', 'ASC')->pluck('brandName', 'id'), null, array('class' => 'form-control brand-upload-select brands-select', 'data-live-search' => 'true', 'data-live-search-placeholder'=>'Search', 'autocorrect'=>'off'))!!}
								</div>
								<hr>

								<div class="form-group">
									<em class="text-primary text-left "><strong>Download Sample:</strong></em>
									<a id="locationsSampleExcel" href="{!!asset('downloads/LocationsTestCreate.xlsx');!!}">LocationsTestCreate.xlsx</a>
								</div>

								{!!Form::label('sheet', 'Select SpreadSheet to upload');!!}<br>
								<div class="btn btn-default btn-file excel-container">
									{!!Form::file('spreadsheet',array('id'=>'spreadsheet-input'));!!}
								</div>
								<br><br>
								<div class="form-group">
								<em class="text-primary text-left "><strong>32Mb Maximum file size</strong></em>
								</div>
								<hr>
								{!!Form::submit('Upload File',array('class' => 'btn btn-success','id'=>'upload-submit-btn'));!!}
								<a class="btn btn-danger" id="cancel-btn" href="#">Cancel</a>
								{!!Form::close()!!}
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /.col-lg-12 -->
</div>
<!-- /.row -->
<div class="modal fade bs-example-modal-publishers" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title" id="myModalLabel"><label>Warning</label></h4>
			<hr>
			<div class="modal-body">
				The selected client currently don't have any brands, please create brand for it before adding location
			</div>
			<div class="modal-footer">
			<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="window.location='{!!url("brands/edit") !!}'">Create brand</button>
		</div>
	</div>
	</div>
</div>

@stop
@section('script')
<script src="https://maps.googleapis.com/maps/api/js?key=<?=Config::get('google.maps.key');?>"></script>
<script type="text/javascript">
var geocoder = new google.maps.Geocoder();
var marker;
var addressC;
var splitter;
var addressTwo = [];
var latLng;
var map;

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

function initialize() {
	if (navigator.geolocation) {
 	    navigator.geolocation.getCurrentPosition(function(position) {
 	        var latitudes = position.coords.latitude;
 	        var longitudes = position.coords.longitude;

 	        var geolocpoint = new google.maps.LatLng($("#latitude").val(),$("#longitude").val());
		   latLng = new google.maps.LatLng(latitudes,longitudes);
		   map = new google.maps.Map(document.getElementById('mapCanvas'), {
		    zoom: 8,
		    center: geolocpoint,
		    mapTypeId: google.maps.MapTypeId.ROADMAP
		  });

   marker = new google.maps.Marker({
    position: latLng,
    title: 'Point A',
    map: map,
    draggable: true
  });

	circle = new google.maps.Circle({
		map: map,
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
 	    });
}
}//initialise
google.maps.event.addDomListener(window, 'load', initialize);

$(document).ready(function() {

	if(getParameterByName('alert') != false){
	    swal(getParameterByName('alert'));
	}


    $("#cancel-btn").click(function(){
		if($("#brandId").val()){
			var url = '/locations?brandId='+$("#brandId").val();
		}else{
			var url = '/locations';
		}
		window.location.href = url;
	});

	$("#brandId").find('option').remove();

	$("select.brands-select").selectpicker({
		liveSearch: true,
		liveSearchPlaceholder: 'Search Brand'
	})

	$("#advertiserId").selectpicker({
		liveSearch: true,
		liveSearchPlaceholder: 'Search Agency'
	})

	function loadBrands(adversiteId,brandId){
		if(!adversiteId){
			adversiteId = $("#advertiserId").val();
		}
		$.ajax({
			dataType: "json",
			url: "{!!action('CampaignController@getAdvertiser')!!}/"+adversiteId
		}).done(function(results){
			$("select.brands-select").find('option').remove();
			$(results).each(function(index,value){
				brandSelected = "";
				if(brandId && brandId == value.id){
					brandSelected = 'selected=selected';
				}
				$("select.brands-select").append("<option  "+brandSelected+" value=\""+value.id+"\">"+value.brandName+"</option>");
			});
			$('select.brands-select').selectpicker('refresh');
		}).fail(function(results){
			swal("Failed to load brands.");
		});
	}

	@if(Input::has('advertiserId') && Input::has('brandId'))
		$('#advertiserId').val("{!!Input::get('advertiserId')!!}");
		loadBrands({!!Input::get('advertiserId')!!},{!!Input::get('brandId')!!});
	@else
		loadBrands();
	@endif

	$("#advertiserId").change(function(){
		loadBrands($(this).val(),"");
	});

	$('#upload-submit-btn').click(function(){
	    if($("#spreadsheet-input").val() != ""){
			return true;
		}

	    swal('Upload a excel sheet with locations.\nDownload the sample excel sheet for an example.');
	    $(".btn.btn-default.btn-file.excel-container").css({
			borderColor:'red'
		});

	    var blink_counter = 0;
	    var blink = setInterval(function(){
			blink_counter++
			$("#locationsSampleExcel").toggle();
			if(blink_counter == 4){
				clearInterval(blink);
			}
	    },300);
	    return false;
	});

	function showPosition(position) {
	  	$('#latitude').val( position.coords.latitude );
	    $('#longitude').val( position.coords.longitude );
	}

	function getParameterByName(name) {
		name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
		var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
			results = regex.exec(location.search);
		return results === null ? false : decodeURIComponent(results[1].replace(/\+/g, " "));
	}

	if((getParameterByName('id') || getParameterByName('brandId')) && getParameterByName('page')){
	    if(getParameterByName('page') == 'bulk'){
			var v = getParameterByName('id') || getParameterByName('brandId');
			$("a[href='#upload']").click();
			$('.brand-upload-select').val(v);
			$('.brand-upload-select').val(v).change();
	    }
	}

	var add = document.getElementById('addLocation');
	var addNew = document.getElementById('addAndNewLocation');

	$('#advertiserId').on('change', function() {

		$.ajax({
			url:  "{!!action('BrandLocationController@getCheck')!!}/"+$("#advertiserId").val(),
			success: function(data) {

				if(data == 0)
				{
					$('#myModal').modal('show');
					add.disabled = true;
					addNew.disabled = true;
				}
				else
				{
					add.disabled = false;
					addNew.disabled = false;
				}
			},
		});
	});

	$("#confirm").on('click', function() {
		var latitudes = marker.getPosition().lat();
		var longitude = marker.getPosition().lng();

		$('#latitude').val(latitudes);
		$('#longitude').val(longitude);

		splitter = addressC.split(",");
		var addressOne = splitter[0];
		$('#addressLine1').val(addressOne);

		splitter.shift()
		var goGetNewAddress = splitter.toString();
		$('#addressLine2').val(goGetNewAddress);

		//TODO: Parse City and country and fill them in

		swal("Are you sure you want to save the changes?");

	});

});
</script>
@stop
