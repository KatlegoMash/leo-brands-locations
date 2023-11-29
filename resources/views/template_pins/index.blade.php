@extends('master')
@section('header')
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
	#templates td{
			padding: 5px !important;
	}
	#templatese  th{
			padding-left: 5px;
	}
</style>
<span>Creative Templates</span>
@stop
@section('content')
<div class="col-lg-12">
	<div class="panel panel-primary">
		<div class="panel-heading">
			Creative Templates
		</div>
		<div class="panel-body" id="div1">
			<div class="row table-responsive">
				<div class="col-lg-12">
					@if(count($templates))
					<table id="templates" class="display table table-striped table-bordered table-hover dataTable no-footer" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>Id</th>
								<th>Name</th>
								<th>Templates Sizes</th>
								<th>Created</th>
							</tr>
						</thead>
						<tbody>
							@foreach($templates as $template)
							<tr id="row-{!!$template->id!!}" onclick="selectRow(this,{!!$template->id!!})" style="cursor: pointer">
								<td>{!!$template->id!!}</td>
								<td>{!!$template->name!!}</td>
								<td>
									@foreach($template->sizes as $key=>$size)
										{!!$size->width."X".$size->height !!}
									@endforeach
								</td>
								<td>{!!$template->created_at!!}</td>
							</tr>
							@endforeach
						</tbody>
					</table>
					@else
						<p>There are no templates</p>
					@endif
				</div>
			</div>
			<!-- /.row (nested) -->
			<hr>

			<a href="{!!url('template-pins/create')!!}" class="btn btn-primary">New Tempate</a>
			<button onclick="return goToEditPage()" class="btn btn-success">Update Tempate</button>
			<button type="submit" class="btn btn-danger" data-toggle="modal" onclick="showDeletePinTemplate()">Remove Template</button>

		</div>
	</div>
</div>


	<!-- Modal -->
	<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="myModalLabel">Confirm Remove </h4>
				</div>
				<div class="modal-body">
					<p>Are you sure you want to delete this Pin Tempate?</p>
				</div>
				@include('loading',array('name'=>'delete-loading','display'=>'none'))
				<br/>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-danger" onclick="deleteTemplate()">Remove</button>
				</div>
			</div>
		</div>
	</div>
	<!-- /#Modal -->
	@endsection

	@section('script')
	<script type="text/javascript">
		$(document).ready(function(){
			$('#templates').DataTable({
				"pageLength": 50
			});
		});
		var selected_id = "";
		function selectRow(obj,id){
			if($(obj).hasClass('selected')){
				$(obj).removeClass('selected');
				selected_id = "";
			}else{
				$('#templates tbody tr').removeClass('selected');
				$(obj).addClass('selected');
				selected_id = id;
			}

		}
		function validationRowSelection(){
			if(selected_id == ""){
				swal('Select a Template before you perform this action.');
				return false;
			}
		}
		function goToEditPage(){
			if(selected_id == ""){
				swal('Select a Template before edit.');
				return false;
			}
			window.location.href = '/template-pins/edit/'+selected_id;
		}
		function showDeletePinTemplate(){
			if(selected_id == ""){
				swal('Select a Template before edit.');
				return false;
			}
			$('#deleteModal').modal('show');
		}
		function deleteTemplate(){

			//TODO Avoid hardcoding this value
			var defaultStaticTemplate = 27;
			if(parseInt(selected_id) == defaultStaticTemplate){
				swal('Static Distance Window template cannot be deleted.');
				$('button[data-dismiss="modal"]').click();
				return false;
			}

			$('#delete-loading').show();
			$('#deleteModal .modal-body').hide();
			$.ajax({
				url:'/template-pins/delete/'+selected_id,
				type: "POST"
			}).error(function(){
				swal('Failed to delete the creative template');
				$('#delete-loading').hide();
				$('#deleteModal .modal-body').show();
				$('#deleteModal').hide('show');
			})
			.success(function(){
				swal('The creative template has been deleted');
				$('#delete-loading').hide();
				$('#deleteModal .modal-body').show();
				$('#deleteModal').hide('show');
				$('#row-'+selected_id).remove();
				window.location.reload();
			});
			return false;
		}
	</script>
	@endsection
