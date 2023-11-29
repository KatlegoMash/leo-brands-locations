@extends('master')
@section('header')
<span>Creative Templates</span>
@stop
@section('content')
<style type="text/css">
    .fa-times{
	color: red;
    }
    .fa-check{
	color: greenyellow;
    }
    .marker.fa{
	display: none;
    }
    .btn-primary .fa-check{
	display: block !important;
    }
    .in-active .fa-times{
	display: block !important;
    }
    .auto-create-fields{
	display: none;
    }
</style>
<div class="col-lg-12">
	<div class="panel panel-primary">
		<div class="panel-heading">
			Create New Creative Template
		</div>
		<div class="panel-body">
			<div class="row">


				<!-- Placement Information -->
				<div class="col-lg-12">
					{!!Form::open(array('url' => 'template-pins/new', 'method' => 'post',"id"=>"template-form"))!!}
					@if(isset($template))
						{!!Form::hidden('id', $template->id );!!}
					@endif
					<!-- #Revenue Percentage -->
					<div class="form-group">
						{!!Form::label('name', 'Creative Template Name');!!}
						{!!Form::text('name', isset($template) ? $template->name : '', array('class' => 'form-control required', 'placeholder' => 'Enter the creative template name'));!!}
					</div>


					<!-- #Banner Allow -->
					<div class="">

						<br/>
						<div class="col-lg-6 banner-boards" id="leaderBoard">
							<fieldset>
								<legend>Mobile Leaderboard</legend>
							    @foreach($banners as $sizes)
							    @if($sizes['type'] == 'LeaderBoard')
							    <span id='dimension_{!!$sizes['width']."X".$sizes['height']!!}' onclick="bannerAddRemove(this)" class="banner-add-remove btn btn-default <?php if($sizes['selected']){ echo "btn-primary";}else{echo "in-active";}?>" width='{!!$sizes['width']!!}' height='{!!$sizes['height']!!}'>{!!$sizes['width']." X ".$sizes['height']!!}
								<span class="marker fa fa-times"></span>
								<span class="marker fa fa-check"></span>
								</span>
								@endif

								@endforeach
							</fieldset>
						</div>
					<div class="col-lg-6 banner-boards" id="square_rectangle">
						<fieldset>
							<legend>Mobile Rectangle/Square</legend>
							@foreach($banners as $sizes)
							@if($sizes['type'] == 'SquareRectangle')
							<span id='dimension_{!!$sizes['width']."X".$sizes['height']!!}' onclick="bannerAddRemove(this)" class="banner-add-remove btn btn-default <?php if($sizes['selected']){ echo "btn-primary";}else{echo "in-active";}?>" width='{!!$sizes['width']!!}' height='{!!$sizes['height']!!}'>{!!$sizes['width']." X ".$sizes['height']!!}
							<span class="marker fa fa-times"></span>
							<span class="marker fa fa-check"></span>
						</span>
						@endif

						@endforeach
					</fieldset>
					</div>
					<div class="col-lg-6 banner-boards" id="ooh_sizes">
						<fieldset>
							<legend>OOH Site Sizes</legend>
							@foreach($banners as $sizes)
							@if($sizes['type'] == 'ooh')
							<span id='dimension_{!!$sizes['width']."X".$sizes['height']!!}' onclick="bannerAddRemove(this)" class="banner-add-remove btn btn-default <?php if($sizes['selected']){ echo "btn-primary";}else{echo "in-active";}?>" width='{!!$sizes['width']!!}' height='{!!$sizes['height']!!}'>{!!$sizes['width']." X ".$sizes['height']!!}
							<span class="marker fa fa-times"></span>
							<span class="marker fa fa-check"></span>
						</span>
						@endif

						@endforeach
					</fieldset>
					</div>
				</div>

			<br/>

		<div class="col-lg-12">
			<hr>
			<?php $name = isset($template) ? 'Update' : 'Add'; $name .= ' Creative Template';?>
			{!!Form::submit($name,array('class' => 'btn btn-success','id'=>'add-update-template'));!!}
			<button type="button" class="btn btn-danger" onclick="window.location='{!! url("template-pins") !!}'">Cancel</button>
			{!! Form::close() !!}
			</div>
			<!-- /#Placement Information -->
		</form>
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
@stop
@section('script')
<script>
	function bannerAddRemove(element){
		var height = $(element).attr('height');
		var width = $(element).attr('width');
		var dimension = width+'X'+height;

		$(element).removeClass('in-active');
		$(element).toggleClass('btn-primary');
	}
	$(document).ready(function() {
		$('#add-update-template').click(function(){
			if ($('#name').val() == '')
			{
				$('#name').focus();
				swal("Creative Template Name Field is empty");
				return false;
			}

			var counter = 0;
			var bannerSizes = new Array;
			$('.banner-add-remove.btn-primary').each(function(e){
				var height = $(this).attr('height');
				var width = $(this).attr('width');
				var temp = {
					height:height,
					width:width
				}
				$("#template-form").append('<input class="allowedBannershidden" name="sizes['+counter+'][height]" type="hidden" value="'+height+'">');
				$("#template-form").append('<input class="allowedBannershidden" name="sizes['+counter+'][width]" type="hidden" value="'+width+'">');

				bannerSizes.push(temp);
				counter++;
			});

			if(bannerSizes.length == 0){
				swal('Please select at least one banner size.')
				return false;
			}

		});

        $("#leaderBoard, #square_rectangle, #ooh_sizes").show();

	});//#ready


</script>
@include('placements.common_script')
@stop
