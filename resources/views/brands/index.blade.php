@extends('master')
@section('header')
<span>Brands</span>
@stop
@section('content')
<div class="col-lg-12">
	<div class="panel panel-primary">
		<div class="panel-heading">
			Brands
		</div>
		<div class="panel-body" id="div1">
				<div class="row table-responsive">
					<div class="col-lg-12">
					<table id="brands" class="display table table-striped table-bordered table-hover dataTable no-footer" cellspacing="0" width="100%">
				        <thead>
				            <tr>
				            	<th>Id</th>
				                <th>Brand Name</th>
								<th>Brand URL</th>
								<th>Brand Logo</th>
								<th>Total Locations</th>
								<th>Visit Geofence Size</th>
								<th>Use for Visits</th>
								<th>Use for NearMe</th>
								<th>Generate QR Code</th>
								<th>Broadsign Advertiser</th>
								<th>Client</th>
								<th>Visit Score</th>
				                <th>Linked Campaigns</th>
								<th>In-Market Brand Classification</th>
								<th>Widget Categories</th>
								<th>Location Bank Client ID</th>
				                <th>Location Bank Data</th>
				            </tr>
				        </thead>
				        <tfoot>
				            <tr>
				            	<th>Id</th>
				                <th>Brand Name</th>
								<th>Brand URL</th>
								<th>Brand Logo</th>
								<th>Total Locations</th>
								<th>Visit Geofence Size</th>
								<th>Use for Visits</th>
								<th>Use for NearMe</th>
								<th>Generate QR Code</th>
								<th>Broadsign Advertiser</th>
								<th>Client</th>
								<th>Visit Score</th>
								<th>Linked Campaigns</th>
								<th>In-Market Brand Classification</th>
								<th>Widget Categories</th>
								<th>Location Bank Client ID</th>
								<th>Location Bank Data</th>
				            </tr>
				        </tfoot>
				    </table>
					</div>
				</div>
				<!-- /.row (nested) -->
				<hr>

			<a href="{!!url('brands/edit')!!}" class="btn btn-primary">New Brand</a>
			<button type="submit" class="btn btn-success" id="next">Edit Brand</button>

			<button type="button" class="btn btn-warning" onclick="populateAndDrawBrandsLogoModel()" id="brandsLogoBtn">Upload Brands Logo</button>
			<button type="button" class="btn btn-danger" id="deSelectAll">Deselect All</button>
	</div>

	<!--pop up model-->
	<div class="modal fade bs-example-modal-l" id="brandsLogoModel" out="hidden" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg d-inline-flex p-2">
			<div class="modal-content">
				<div class="modal-header panel-body">
					<h3 class="modal-title">
						<label>Apply Brands Logo</label>
					</h3>
				</div>
				<div class="modal-body">
					<form method="post" action="{{url('brands/brands-image')}}" enctype="multipart/form-data">
						<input type='hidden' name='brandLogoId' id='brandUploadId'>
						{{csrf_field()}}
						<table id="brandsLogoModelTable" class="display table table-striped table-bordered table-hover dataTable no-footer" title="Add Logo">
								<thead>
									<tr>
										<th>Brand ID</th>
										<th>Brand Name</th>
										<th>Client</th>
									</tr>
								</thead>
							</table>
							<button type="button" class="float-end btn btn-md btn-danger" data-bs-dismiss="modal" data-dismiss="modal">
								Cancel
							</button>
							<!--image upload-->
							<div class="container col-group col-md-4">
								@if ($message = Session::get('error'))
								<div class="alert alert-danger alert-block">
									<strong>There is something error please check your file</strong>
									<button type="button" class="close" data-dismiss="alert">×</button>
									<strong>{{ $message }}</strong>
								</div>
								@endif
							@if ($message = Session::get('success'))
							<div class="alert alert-success alert-block">
								<button type="button" class="close" data-dismiss="alert">×</button>
								<strong>{{ $message }}</strong>
							</div>
							@endif
								<div class="input-group col-md-2" >
								  <input type="file" name="filenames[]" class="myfrm form-control">
								</div>
							</div>
							<div class="col-lg-3">
								<button type="submit" class="btn btn-success">Upload Image</button>
							</div>
					</form>
				</div><!-- modal-body -->
			</div> <!-- modal-content -->
		</div> <!-- modal-dialog -->
	</div> <!-- modal -->

		<!-- /.panel-body -->
		<div class="panel-body" id="div2" style="display:none">
			<div class="col-lg-6">
                {!!Form::open(array('url' => 'brands/index', 'method' => 'post'))!!}
                <div class="form-group @if ($errors->has('brandName')) has-error @endif">
                	{!!Form::label('brandName', 'Brand Name');!!}
                    {!!Form::text('brandName', '', array('class' => 'form-control required', 'id' => 'brandName'));!!}
                </div>
				<div class="form-group @if ($errors->has('brandUrlUpdate')) has-error @endif">
                    {!!Form::label('brandUrlUpdate', 'Brand URL');!!}
                    {!!Form::text('brandUrlUpdate', '', array('class' => 'form-control required', 'id' => 'brandUrlUpdate'));!!}
                </div>
                <div class="form-group @if ($errors->has('billingContactFirstName')) has-error @endif">
                    {!!Form::label('billingContactFirstName', 'Billing Name');!!}
                    {!!Form::text('billingContactFirstName', '', array('class' => 'form-control required', 'id' => 'billingContactFirstName'));!!}
                </div>
                <div class="form-group @if ($errors->has('billingContactEmail')) has-error @endif">
                    {!!Form::label('billingContactEmail', 'Billing Email');!!}
                    {!!Form::email('billingContactEmail', '', array('class' => 'form-control required', 'type' => 'tel', 'id' => 'billingContactEmail'));!!}
                </div>
                <div class="form-group @if ($errors->has('billingContactPhone')) has-error @endif">
                    {!!Form::label('billingContactPhone', 'Billing Tel');!!}
                    {!!Form::text('billingContactPhone', '', array('class' => 'form-control required', 'id' => 'billingContactPhone'));!!}
                </div>
                <div class="form-group @if ($errors->has('reportingContactFirstName')) has-error @endif">
                    {!!Form::label('reportingContactFirstName', 'Reporting Name');!!}
                    {!!Form::text('reportingContactFirstName', '', array('class' => 'form-control required', 'id' => 'reportingContactFirstName'));!!}
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
             	<div class="form-group @if ($errors->has('reportingContactEmail')) has-error @endif">
                	{!!Form::label('reportingContactEmail', 'Reporting Email');!!}
                    {!!Form::text('reportingContactEmail', '', array('class' => 'form-control required', 'id' => 'reportingContactEmail'));!!}
                </div>
                <div class="form-group @if ($errors->has('reportingContactPhone')) has-error @endif">
                    {!!Form::label('reportingContactPhone', 'Reporting Tel');!!}
                    {!!Form::text('reportingContactPhone', '', array('class' => 'form-control required', 'id' => 'reportingContactPhone'));!!}
                </div>
                <div class="form-group @if ($errors->has('creativesContactFirstName')) has-error @endif">
                    {!!Form::label('creativesContactFirstName', 'Creative Name');!!}
                    {!!Form::text('creativesContactFirstName', '', array('class' => 'form-control required', 'id' => 'creativesContactFirstName'));!!}
                </div>
                <div class="form-group @if ($errors->has('creativesContactEmail')) has-error @endif">
                    {!!Form::label('creativesContactEmail', 'Creative Email');!!}
                    {!!Form::email('creativesContactEmail', '', array('class' => 'form-control required', 'id' => 'creativesContactEmail'));!!}
                </div>
                <div class="form-group @if ($errors->has('creativesContactPhone')) has-error @endif">
                    {!!Form::label('creativesContactPhone', 'Creative Tel');!!}
                    {!!Form::text('creativesContactPhone', '', array('class' => 'form-control required', 'id' => 'creativesContactPhone'));!!}
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
				<div class="form-group @if ($errors->has('broadsign_yn')) has-error @endif">
					{!!Form::label('broadsign_yn', 'Check to use this brand on Broadsign');!!}
					{!!Form::checkbox('broadsign_yn', false);!!}
				</div>
                <div class="form-group @if ($errors->has('billingId')) has-error @endif">
                    {!!Form::hidden('billingId', '', array('class' => 'form-control required', 'id' => 'billingId'));!!}
                </div>
                <div class="form-group @if ($errors->has('reportingId')) has-error @endif">

                    {!!Form::hidden('reportingId', '', array('class' => 'form-control required', 'id' => 'reportingId'));!!}
                </div>
                <div class="form-group @if ($errors->has('creativeId')) has-error @endif">

                    {!!Form::hidden('creativeId', '', array('class' => 'form-control required', 'id' => 'creativeId'));!!}
                </div>

                {!! Form::close() !!}
                <hr>
                	<button type="button" class="btn btn-success"  id="update">Update Brand</button>
                    <button type="button" class="btn btn-danger" onclick="window.location='{!! url("brands") !!}'">Cancel</button>
                    {!! Form::close() !!}
             </div>
		</div>
	</div>
	<!-- /.panel -->
</div>
<!-- /.col-lg-12 -->

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Confirm Remove Brand</h4>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this brand?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal" id="button">Remove</button>
      </div>
    </div>
  </div>
</div>
<!-- /#Modal -->


@stop
@section('script')
<script>
function format ( d ) {
    // `d` is the original data object for the row
    return '<table class="no-check" cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">'+
	    '<tr>'+
		    '<td>Brand Name:</td>'+
		    '<td>'+d.brandName+'</td>'+
		'</tr>'+
		'<tr>'+
	        '<td>Billing Contact Name:</td>'+
	        '<td>'+d.total_locations+'</td>'+
	    '</tr>'+
        '<tr>'+
            '<td>Creative Contact Phone:</td>'+
            '<td>'+d.locationBankClientId+'</td>'+
        '</tr>'+

    '</table>';
}

$(document).ready(function() {
	//Get All Widget categories
	let dropDown, rawCategories

	function getAllWidgetCategories () {
		return  $.ajax({
			type: "GET",
			url: "{!! action('BrandController@getWidgetCategories') !!}",
			contentType: 'application/json; charset=utf-8',
			dataType: 'json'
		});
	}

	function createDropDown(data, selectedCategories) {
		var html = '<select name="widgetCategories[]"  data-live-search="true" multiple class="selectpicker form-control widgetCateg">';
		for (var item of data) {
			let optionValue = item.id;
			let optionName = item.categoriesName;
			let selected = '';

			if(selectedCategories.includes(optionValue)){
				selected = "selected";
			}

			html += `<option value='${optionValue}' ${selected}>${optionName}</option>`;
		}
		html += '</select>';
		return html;
	}



	getAllWidgetCategories().done( function(response){
		dropDown = response
	})
	let table = $('#brands').DataTable({
            title: 'Brands',
            className: 'mb-1 btn btn-secondary',
            orientation: 'landscape',
            pageSize: 'LEGAL',
            footer: true,
			select: {
				style: 'multi'
			},
			"stateDuration": -1,
			"ajax": "brands/json",
			dom: 'lBfrtip',
			"columns": [
				{ "data": "id" }, //Fixing this, data tables was setting this to null even in the live site, so changed brandId to id, same values anyways
				{
					data: "brandData",
					render: function(data, type, row, meta){
						let html = `<a href="/locations?brandId=${data.brandId}">${data.brandName}</a>`;
						return html;
					}
				},
				{
					data: "brandUrl",
					render: function(data){
						let html = `<a href="${data}">${data ?? ''}</a>`;
						return html;
					}
				},
				{
					data: 'imageURL',
					render: function(data, type, row) {
						if (type === 'display' && data) {
							return '<img src="' + data + '" loading="lazy" alt="Brand Logo" width="" style="max-height:20px" pointer-events: none;>';
						}
						return data; 
					}
				},
				{ "data": "total_locations"},
				{ "data": "geofence" },
				{ "data": "visits_yn"},
				{
					data: 'use_nearme_yn',
					render: {
						_: 'html',
						sort: 'value'
					}
				},
				{
					data: 'id',
					render: function(data){
						let html = '<button class="btn btn-primary" data-toggle="modal" data-target="#modal-qr-codes" qrCode_generation" onclick="generateQrCodes('+ data +')">QR Codes</button>';
						return html;
					}
				},
				{ "data": "broadsign_yn"},
				{ "data": "client" },
				{ "data": "visit_score"},
				{ "data": "linked_campaigns"},
				{ "data":"In_Market_Brand_Classification"},
				{
					data:'selectedCategories',
					render: function (data, type, row, meta){
						if(type == 'sort' || type == "filter"){
							return data.names
						}

						return createDropDown(dropDown, data.array)
					}
				},
				{ "data": "locationBankClientId" },
				{ "data": "location_bank_data"},

			],
			"columnDefs": [
						{
					targets:[5,6],
       				orderDataType: 'dom-checkbox',
       				render: function(data, type, row, meta){
          				if(type == 'sort'){
             				let api = new $.fn.dataTable.Api( meta.settings );
             				let $input = $(api.cell({ row: meta.row, column: meta.col }).node()).find('input');
             				data = $input.prop('checked') ? '1' : '0';
          				}

          				return data;
       						}
						},
						{
							// "targets": [ 2,6 ],
							"visible": false,
							"searchable": false
						},
						{
							className: "geoFence", "targets":[4]
						},
						{
							className: "maxCap", "targets":[5]
						}
					],
			initComplete: function () {
				var api = this.api();
				api.columns().indexes().flatten().each( function ( i ) {
					var column = api.column( i );
					var select = $('<select><option value=""></option></select>')
						.appendTo( $(column.footer()).empty() )
						.on( 'change', function () {
							var val = $.fn.dataTable.util.escapeRegex(
								$(this).val()
							);
							column
								.search( val ? '^'+val+'$' : '', true, false )
								.draw();
						} );
					column.data().unique().sort().each( function ( d, j ) {
						select.append( '<option value="'+d+'">'+d+'</option>' )
					} );
				} );

				let selectObject = $('.selectpicker.widgetCateg').selectpicker({
					actionsBox: true,
					header: 'Select Categories',
					liveSearch: true,
					selectedTextFormat: 'count > 2'
				})

				selectObject.on('hide.bs.select', function(selectObject){
					//REVIEW: is it possible to compare if anything was selected or disselected? and only post if something did change indeed? Or can this be handled on the server side?
					postSelectedCategories(selectObject.target)
				})
			},
			buttons: [
			{

				extend: 'excel',
				header: true,
				className: 'mb-1 btn btn-secondary btn-export-update ',
				exportOptions: {
						orthogonal: 'sort',
						columns: ':visible'
						}
			},
			{
				extend: 'csv',
				header: true,
				className: 'mb-1 btn btn-secondary btn-export',
				exportOptions: {
						orthogonal: 'sort',
						columns: ':visible'
						}
			},
			],
			"pageLength": 25
		}); //#.DataTable
	// }

	table.on('draw', function(){
		$('.selectpicker.widgetCateg').selectpicker('refresh');
	})

	function postSelectedCategories(selectTarget){
		let row = selectTarget.closest('tr')
		let data = table.row(row).data()
		let selectedCategories = [];

		for(let option of selectTarget.selectedOptions){
			let categoryId = parseInt(option.value)
			selectedCategories = [...selectedCategories, categoryId]
		}

		if(data.id){
			$.ajax({
				type: 'POST',
				url: "{!! action('BrandController@postBrandWidgetCategories') !!}",
				data: {
					brandId: data.id,
					categories: selectedCategories
				}
			}).done( function(response){});
		}
	}


//Deselect Rows
$('#deSelectAll').on('click', function(){
	var dataTable = $('#brands').DataTable();
  		dataTable.rows('.selected').deselect();
})

//#Select row
	$('#brands tbody').on( 'click', 'td', function () {
		var currentRow=$(this).closest("tr");
		if ($(this).hasClass("geoFence")) {
			newInput(this);
		}
		else if ($(this).hasClass("maxCap")) {
			newInput(this);
		}
	} );

	$("#brands tbody").on('click', 'input:checkbox', function(event) {

		let row = $(event.target).closest("tr");
		let { name } = event.target

		$(".selected").removeClass("selected");
		row.addClass('selected');

		let data = table.row(row).data();

		let fieldValue = 0;

		if ($(event.target).is(":checked")) {
			fieldValue = 1;
		}else{
			fieldValue = 0;
		}

		$.ajax({
				 type: 'POST',
				 url:  "{!!action('BrandController@postUpdateCheckBoxes')!!}/"+data.brandId,
				 data: {
					 field: name,
					 value: fieldValue
				}
				}).done(function(xhr){
					let { error, message } = xhr
					if(error) swal(`${error}`)
				}).fail(function(xhr){
					swal("Brand Update failed. Please contact site administrator if the problem persists.");
		});

	})

	function closeInput(elm) {

		var value = $(elm).find('input').val();
		$(elm).empty().text(value);

		$row = table.row(elm).data();

		let geoFence = 0;
		let maximumCapacity = 0;

		//empty\'s out the other field if not being sent.
		//so have to update both.
		if ($(elm).hasClass("geoFence")) {
			geoFence = value;
			maximumCapacity = $('td.maxCap').html();
		}
		if ($(elm).hasClass("maxCap")) {
			maximumCapacity = value;
			geoFence = $('td.geoFence').html();
		}


		if (geoFence !== '0' || maximumCapacity !== '0') {
			$.ajax({
				type: 'POST',
				url:  "{!!action('BrandController@postUpdate')!!}/"+$row.id,
				data: { brandName: $row.brandName, maximum_capacity: maximumCapacity, geofence: geoFence}
			}).done(function(xhr){
				$(elm).append('<i class="fa fa-check ml-5 text-success"></i>');
				$(elm).find('.fa-check').delay(1500).fadeOut();
			}).fail(function(xhr){
			});
		}
	}

	function newInput(elm) {

		var value = $(elm).text();
		$(elm).empty();

		$("<input>")
			.attr('type', 'text')
			.val(value)
			.blur(function () {
				closeInput(elm)
			})
			.appendTo($(elm))
			.focus();



    }

 	// Add event listener for opening and closing details
    $('#brands tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var row = table.row( tr );

        if ( row.child.isShown() ) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        }
        else {
            // Open this row
            row.child( format(row.data()) ).show();
            tr.addClass('shown');
        }
    } );



    $('#button').click( function () {
    	var count = table.rows('.selected').data().length;

    	$rows = table.rows('.selected').data();
		var compare = 0;

    	$.each($rows,function(index,row){
    		$.ajax({
    		    type: 'DELETE',
    		    url:  "brands/brand/"+row.brandIdB,
    		    success: function(data) {
    		        table.row('.selected').remove().draw( false );

					compare = compare + 1;

    		        if( compare == count)
    		        {
						swal("Brand(s) was successfully deleted");
    		        }
    		    },
    		});
		});//#$.each

    } );
    //#Handle delete row

     $("#next").on('click', function() {
    	  var count = table.rows('.selected').data().length;

		  if( count > 1 )
		  {
			  swal("Please select one record you wish to update");
		  }
		  else if( count == 0 )
		  {
			  swal('Please click a row to update');
		  }
		  else
		  {

			$row = table.row('.selected').data();

			$.ajax({
				 type: 'GET',
				 url:  "{!!action('BrandController@getBrand')!!}/"+$row.id,
			 }).done(function(xhr){

				$("#div2").fadeIn();
				$("#div1").fadeOut();
				$row = table.row('.selected').data();
				$('#brandName').val( $row.brandName );
				$('#brandUrlUpdate').val($row.brandUrl);
				$('#billingContactFirstName').val(xhr[0].contactFirstName);
				$('#billingContactEmail').val(xhr[0].contactEmail);
				$('#billingContactPhone').val(xhr[0].contactPhone);
				$('#reportingContactFirstName').val(xhr[1].contactFirstName);
				$('#reportingContactEmail').val(xhr[1].contactEmail);
				$('#reportingContactPhone').val(xhr[1].contactPhone);
				$('#creativesContactFirstName').val(xhr[2].contactFirstName);
				$('#creativesContactEmail').val(xhr[2].contactEmail);
				$('#creativesContactPhone').val(xhr[2].contactPhone)
				$('#locationBankClientId').val($row.locationBankClientId)
				$('#billingId').val($row.idB);
				$('#reportingId').val($row.idR);
				$('#creativeId').val($row.idC);
				$('#geofence').val($row.geofence);
				$('#maximumCapacity').val($row.maximum_capacity);
				if ($row.visits_yn.includes("checked")) {
					$("#visits_yn").prop("checked", true);
				}
			 }).fail(function(xhr){
				swal("Brand Update failed. Please contact site administrator if the problem persists.");
			});

		  }
	});

  //Handle update row
   $('#update').click( function () {
			$row = table.row('.selected').data();
			brandName = $('#brandName').val();
			brandUrlUpdate = $('#brandUrlUpdate').val();
			billingContactFirstName = $('#billingContactFirstName').val();
			billingContactEmail = $('#billingContactEmail').val();
			billingContactPhone = $('#billingContactPhone').val();
			reportingContactFirstName = $('#reportingContactFirstName').val();
			reportingContactEmail = $('#reportingContactEmail').val();
			reportingContactPhone = $('#reportingContactPhone').val();
			creativesContactFirstName = $('#creativesContactFirstName').val();
			creativesContactEmail = $('#creativesContactEmail').val();
			creativesContactPhone = $('#creativesContactPhone').val();
			locationBankClientId = $('#locationBankClientId').val();
			billingId = $('#billingId').val();
			creativeId = $('#creativeId').val();
			reportingId = $('#reportingId').val();
			geofence = $('#geofence').val();
			maximum_capacity = $('#maximumCapacity').val();
			visits_yn = $('#visits_yn').is(":checked");
			use_nearme_yn = $('#use_nearme_yn').is(":checked");


				var urlValue = $('#brandUrlUpdate').val().trim();

        if (urlValue === '') {
					swal("Please enter a Valid Brand URL");
					$('#brandUrlUpdate').focus();
					return false;
        }

				brandUrlUpate = urlValue;

	    var billingemail = billingContactEmail;
        var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
        if( !emailReg.test( billingemail ) )
          {
	        //   swal('Please enter valid Billing email make sure it contains @ and .something');
	      	// return false;

          }

        var reportingemail = reportingContactEmail;
        var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
        if( !emailReg.test( reportingemail ) )
          {
	        //   swal('Please enter valid Reporting email make sure it contains @ and .something');
	      	// return false;

          }

        var creativesemail = creativesContactEmail;
        var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
        if( !emailReg.test( creativesemail ) )
          {
	        //   swal('Please enter valid Creatives email make sure it contains @ and .something');
	      	// return false;

          }

	     if(brandName == "" || billingContactFirstName == "" || billingContactEmail == "" || billingContactPhone == "")
	     {
	    	// swal("Brand name and billing contact are required!");
	     }
	     else
	     {

	     }

		 if($row.brandId > 0){

			 $.ajax({
				 type: 'POST',
				 url:  "{!!action('BrandController@postUpdate')!!}/"+$row.brandId,
				 data: { brandName: brandName,billingContactFirstName: billingContactFirstName,billingContactEmail: billingContactEmail,
					 billingContactPhone: billingContactPhone,reportingContactFirstName :reportingContactFirstName,reportingContactEmail :reportingContactEmail,
					 reportingContactPhone :reportingContactPhone,creativesContactFirstName :creativesContactFirstName ,creativesContactEmail :creativesContactEmail,creativesContactPhone :creativesContactPhone,
					 billingId :billingId, creativeId :creativeId, reportingId :reportingId, locationBankClientId: locationBankClientId, geofence: geofence, maximum_capacity: maximum_capacity,visits_yn:visits_yn,
					 use_nearme_yn:use_nearme_yn, brandUrlUpdate: brandUrlUpdate}
			 }).done(function(xhr){
				 swal("Brand successfully updated");
				 location.reload();
			 }).fail(function(xhr){
				swal("Brand Update failed. Please contact site administrator if the problem persists.");
			 });

		 }

} );


	$('#submitBrandsContentCategories').on('click', function(){
		let brandsTable = $('#brands').DataTable()
		let rowSelect = brandsTable.rows('.selected').data().toArray();

		let selectedBrands = rowSelect.map(({id, brandName}) => {
			return id
		})

		let selectedContentCategories =  $('#brandContentCategories').val()

		if(selectedBrands.length == 0){
			swal({
				title: "Please select Some Brands",
				icon: "warning"
			})
			return false
		}

		if(!selectedContentCategories || selectedContentCategories.length == 0 ){
			swal({
				title: "Please select some categories",
				icon: "warning"
			})

			return false
		}

		$.ajax({
			type: 'POST',
			url: "{!! action('BrandController@postBrandContentCategories') !!}",
			data: {
				brandIds: selectedBrands,
				contentCategories: selectedContentCategories
			}
		}).done(({error, message}) => {
			if(error){
				swal({
					title: error,
					text: message,
					icon: "warning"
				})
			}else{
				swal({
					title: "Success",
					text: message,
					icon: "success"
				})
			}
		}).fail((error) =>{
			swal({
					title: error,
					text: message,
					icon: "error"
				})
		})

	})
});

function populateAndDrawBrandsLogoModel(){
	let campaignTable = $('#brands').DataTable()
	let rowSelect = campaignTable.rows('.selected').data().toArray();

	if(rowSelect.length > 1 || rowSelect.length == 0){
		swal("Error!", "Select One Brand Only!", "error")
	}else{
		let table = $('#brandsLogoModelTable').DataTable({
			"bInfo" : false,
			"bFilter": false,
			"bPaginate": false,
		});

			table.clear()

			rowSelect.map((brandData) => {
				let {id , brandName, client} = brandData
				$('#brandUploadId').val(id)

			table.row.add([
				id,
				brandName,
				client,
			])
		})
			table.draw()

			$('#brandsLogoModel').modal('show');
			$('.modal-dialog').css('z-index', '999999');
	}
};
</script>
@stop
