@extends('master')
@section('header')
	<span>Brands 2</span>
@stop
@section('content')
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            Edit Form
                        </div>
                        <div class="panel-body">
                            <div class="row">


                                <!-- Advertiser Information -->
                                <div class="col-lg-6">
                                	{!!Form::open(array('url' => 'brands/new', 'method' => 'post'))!!}
                                		<div class="form-group @if ($errors->has('advertiserId')) has-error @endif">
                                            {!!Form::label('advertiserId', 'Select Advertiser');!!}

											<select class="form-control selectpicker" data-live-search="true" data-live-search-placeholder="Search" autocorrect="off" id="advertiserId" name="advertiserId">
												<option value="">Select an advertiser</option>
												@foreach(\App\Advertiser::orderBy('advertiserName','ASC')->pluck('advertiserName', 'advertiserId') as $key=>$value)
													<option value="{!!$key!!}">{!!$value!!}</option>
												@endforeach
											</select>

	                                    </div>
                                		<div class="form-group @if ($errors->has('brandName')) has-error @endif">
                                            {!!Form::label('brandName', 'Brand Name');!!}
                                            {!!Form::text('brandName', '', array('class' => 'form-control required', 'placeholder' => 'Enter brand name', 'id' => 'brandName'));!!}
                                        </div>
                                        <div class="form-group @if ($errors->has('brandUrlNew')) has-error @endif">
                                            {!!Form::label('brandUrlNew', 'Brand URL');!!}
                                            {!!Form::text('brandUrlNew', '', array('class' => 'form-control required', 'placeholder' => 'Enter brand url', 'id' => 'brandUrlNew'));!!}
                                        </div>
                                        <div class="form-group @if ($errors->has('billingName')) has-error @endif">
                                            {!!Form::label('billingName', 'Billing Name');!!}
                                            {!!Form::text('billingName', '', array('class' => 'form-control required', 'placeholder' => 'Enter billing name'));!!}
                                        </div>
                                        <div class="form-group @if ($errors->has('billingEmail')) has-error @endif">
                                            {!!Form::label('billingEmail', 'Billing Email');!!}
                                            {!!Form::email('billingEmail', '', array('class' => 'form-control required', 'type' => 'tel', 'placeholder' => 'Enter billing email'));!!}
                                        </div>
                                        <div class="form-group @if ($errors->has('billingTel')) has-error @endif">
                                            {!!Form::label('billingTel', 'Billing Tel');!!}
                                            {!!Form::text('billingTel', '', array('class' => 'form-control required', 'placeholder' => 'Enter billing tel'));!!}
                                        </div>
                                        <div class="form-group @if ($errors->has('reportingName')) has-error @endif">
                                            {!!Form::label('reportingName', 'Reporting Name');!!}
                                            {!!Form::text('reportingName', '', array('class' => 'form-control required', 'placeholder' => 'Enter reporting name'));!!}
                                        </div>
                                        <div class="form-group @if ($errors->has('geofence')) has-error @endif">
                                            {!!Form::label('geofence', 'Geofence Size');!!}
                                            {!!Form::text('geofence', '', array('class' => 'form-control required', 'id' => 'geofence'));!!}
                                        </div>
                                        <div class="form-group @if ($errors->has('maximumCapacity')) has-error @endif">
                                            {!!Form::label('maximumCapacity', 'Maximum Capacity');!!}
                                            {!!Form::text('maximumCapacity', '', array('class' => 'form-control required', 'id' => 'maximumCapacity'));!!}
                                        </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group @if ($errors->has('reportingEmail')) has-error @endif">
                                            {!!Form::label('reportingEmail', 'Reporting Email');!!}
                                            {!!Form::text('reportingEmail', '', array('class' => 'form-control required', 'placeholder' => 'Enter reporting email'));!!}
                                    </div>
                                    <div class="form-group @if ($errors->has('reportingTel')) has-error @endif">
                                            {!!Form::label('reportingTel', 'Reporting Tel');!!}
                                            {!!Form::text('reportingTel', '', array('class' => 'form-control required', 'placeholder' => 'Enter reporting tel'));!!}
                                    </div>
                                    <div class="form-group @if ($errors->has('creativeName')) has-error @endif">
                                            {!!Form::label('creativeName', 'Creative Name');!!}
                                            {!!Form::text('creativeName', '', array('class' => 'form-control required', 'placeholder' => 'Enter creative name'));!!}
                                    </div>
                                    <div class="form-group @if ($errors->has('creativeEmail')) has-error @endif">
                                            {!!Form::label('creativeEmail', 'Creative Email');!!}
                                            {!!Form::email('creativeEmail', '', array('class' => 'form-control required', 'placeholder' => 'Enter creative email'));!!}
                                    </div>
                                    <div class="form-group @if ($errors->has('creativeTel')) has-error @endif">
                                            {!!Form::label('creativeTel', 'Creative Tel');!!}
                                            {!!Form::text('creativeTel', '', array('class' => 'form-control required', 'placeholder' => 'Enter creative tel'));!!}
                                    </div>
                                    <div class="form-group @if ($errors->has('locationBankClientId')) has-error @endif">
                                        {!!Form::label('locationBankClientId', 'Enter Location Bank Client ID');!!}
                                        {!!Form::text('locationBankClientId', '', array('class' => 'form-control required', 'placeholder' => 'Enter Location Bank Client ID'));!!}
                                    </div>
                                    <div class="form-group @if ($errors->has('visits_yn')) has-error @endif">
                                        {!!Form::label('visits_yn', 'Check to include Brand in Visits Calculations');!!}
                                        {!!Form::checkbox('visits_yn', false);!!}
                                    </div>
                                    <div class="form-group @if ($errors->has('use_nearme_yn')) has-error @endif">
                                        {!!Form::label('use_nearme_yn', 'Check to use this brand on NearMe');!!}
                                        {!!Form::checkbox('use_nearme_yn', false);!!}
                                    </div>

                                    <hr>
                                	{!!Form::submit('Add Brand',array('onclick'=>'return validate()', 'class' => 'btn btn-primary'));!!}
                                    <button type="button" class="btn btn-danger" onclick="window.location='{!! url("brands") !!}'">Cancel</button>
                                    {!! Form::close() !!}
                                </div>
                            </div>
                            <!-- /.row (nested) -->
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
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
								The Brand with the same name already exist!, please change the brand name because you can't create duplicate.
					      </div>
					      <div class="modal-footer">
					        <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="clearAndFocus( document.getElementById('brandName'))">Change Name</button>
					      </div>
			    </div>
			  </div>
			</div>
@stop
@section('script')
<script type="text/javascript">
$(document).ready(function() {

	 $('#brandName').on('change', function() {
		 brandName = $('#brandName').val();
		 $.ajax({
		    url:  "{!!action('BrandController@getSearch')!!}/"+brandName,
		    success: function(data) {
			    if(data >= 1)
			    {
			    	$('#myModal').modal('show');
			    }
		    },
		});
	 });
});
</script>
@include('brands.js')
<script type="text/javascript">
  function clearAndFocus( el ) { el.value = ''; el.focus() }
</script>
@stop
