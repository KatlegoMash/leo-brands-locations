<style>
	.absolute-top-right{
		position: absolute;
		right:26px;
		top:40px;
	}
	#hide-tab-options{
		margin-left:20px;
		cursor:pointer;
		-webkit-box-ordinal-group: 15;
		-moz-box-ordinal-group: 15;
		box-ordinal-group: 15;
	}
	#hide-tab-options > span{
		margin-left:5px;
		font:14px/21px "Lucia Sans", "Lucida Grande", "Lucida Sans Unicode", seriff;
	}
	.custom-combobox {
		position: relative;
		display: inline-block;
	}
	.custom-combobox-toggle {
		position: absolute;
		top: 0;
		bottom: 0;
		margin-left: -1px;
		padding: 0;
	}
	.custom-combobox-input {
		margin: 0;
		padding: 5px 10px;
		background: #ffffff;
		width: 280px;
	}
	.use-default-value{
		cursor: pointer;
		color: blue;
		font-size: 80%;
	}
	.hide {
		display: none;
		visibility: hidden;
	}
	.bootstrap-switch{
		float: right;
		margin-top: -33px;
	}
	#banner-temp-seetings{
		-webkit-box-orient: vertical;
		-moz-box-orient: vertical;
		box-orient: vertical;
		display: -webkit-box;
		display: -moz-box;
		display: box;
	}
	
	#banner-temp-settings > h3 > div{
		position: absolute;
		z-index: 98;
		top:30px;
		left:0;
		width:300px;
		padding:20px;
		border-radius:5px;
		box-shadow:2px 2px 5px rgba(0,0,0,0.2);
		background:#fff;
		color:grey;
	}
	#banner-temp-settings > #global-fields{
		margin-left:15px;
	}
	#banner-temp-settings > h3 > div img{
		max-width:260px;
	}
	.tooltip-b {
		position: relative;
		cursor: pointer;
	}
	.tooltip-b .tooltiptext {
		visibility: hidden;
		width: 120px;
		background-color: #333;
		color: #fff;
		text-align: center;
		border-radius: 6px;
		padding: 5px 0;

		/* Position the tooltip */
		position: absolute;
		z-index: 99;
		left:60px;
		font:14px/21px "Lucia Sans", "Lucida Grande", "Lucida Sans Unicode", seriff;
	}
	.tooltip-b .tooltiptext::before{
		content:'';
		border-style: solid;
    	border-width: 10px 15px 10px 0;
		border-color: transparent #333 transparent transparent;
		left: -9px;
    	position: absolute;
		z-index: -1;
	}
	.tooltip-b:hover .tooltiptext, .form-actions .btn:hover  .tooltip-b .tooltiptext {
		visibility: visible;
	}
	.widgets{
		list-style-type:none;
		padding-left:0px;
		width: fit-content;
		margin-top:10px;
		columns:2;
		border-radius:6px;
		gap: 0px;
	}
	.col-md-2{
		max-width: 118px;
	}
	.widgets li{
		padding:9.2px;
		display:inline-table;
		transition:0.5s;
		font-size:16px;
		background:#fff;
		width: fit-content;
		border:1px solid #bdbdbd;
	}
	.widgets li:hover{
		background:#e4e4e4;
	}
	.form-wrap.form-builder .form-actions .btn{
		padding:8px 11.3px;
	}
	.widgets li:first-child{
		border-radius:5px 0px 0px 0px;
	}
	.widgets li:last-child{
		border-radius:0px 0px 5px 0px;
	}
	.widgets li:nth-child(9){
		border-radius:0px 0px 0px 5px;
	}
	.widgets li:nth-child(10){
		border-radius:0px 5px 0px 0px;
	}
	.add-page{
		color:#449d44;
		border:1px solid #449d44;
		background:#fff;
		padding: 9px 11px;
		transition:0.5s;
	}
	.add-page:hover{
		color:#fff;
		background:#449d44;
	}
	/* #banner-temp-settings > h3:hover div.hidden, #banner-temp-settings > h3 div.hidden:hover{
		display:block!important;
		visibility:visible!important;
	} */
	#banner-temp-settings .col-md-1{
		max-width:116.2px;
	}
	#dynamic-form .form-builder .nav-tabs.nav>li>a{
		border-radius:0;
	}
	.customise-holder h3{
		font-size: 14px;
		font-weight: 300;
		text-transform: uppercase;
		text-align: center;
		border-bottom: 1px solid #bdbdbd;
		padding-bottom: 28px;
		margin-top: 30px;
		margin-bottom: 20px;
	}
	.customise-holder{
		position: relative;
	}
	.customise-holder #onlygeoip{
    	position:absolute;
	}

	.customise-holder #onlygeoip label{
		margin-right: 103px;
    	font-size: 10px;
		font-weight: 300;
	}
	
	.bootstrap-switch{
		border-radius:30px;
		background:linear-gradient(to right, #eee 50%, #337ab7 50%);
	}

	.bootstrap-switch .bootstrap-switch-label{
		border-radius: 800px;
    	box-shadow: 2px 2px 5px rgb(0 0 0 / 50%);
		position: relative;
		z-index: 2;
	}

	.bootstrap-switch:active,.bootstrap-switch:focus, .bootstrap-switch.bootstrap-switch-focused{
		outline:0;
		box-shadow:none;
		border-color:#eee;
	}

	.hide-all-but-last-li li {
		display: none;
	}

	.hide-all-but-last-li li:last-child {
		display: block !important;
	}

</style>


<?php
	/* Moving Global Settings to the Front & Form builder to the bottom */
	if (\App\TemplatePinSettings::testFormBuilder($template->id)) {
		$globalFields = $templateFields['global-fields'];
		$formBuilder = $templateFields['form-builder'];
		unset($templateFields['form-builder']);
		unset($templateFields['global-fields']);
		$templateFields = array('global-fields' => $globalFields) + $templateFields + array('form-builder' => $formBuilder);
	}
?>
<div class="customise-holder">
	@if(!count($templateFields))
		<p>No fields to customize</p>
	@else
		<div id="onlygeoip" style="height:40px;">
			<label>GeoIP/WiFi Preview </label><br>
			<input onchange="save_custom_settings('preview');" type="checkbox" id="geoip_preview" name="geoip_preview">
		</div>
</div>

	@if($template->id == 59)
	<!--
		<button type="button" class="btn btn-warning col-sm-12" onclick="applyDistanceSettings()">Apply Distance Settings</button><br/><br/>
	-->
	@endif

	<div id="banner-temp-settings" class="@if(\App\TemplatePinSettings::testFormBuilder($template->id) == false) stnd @endif" style="-webkit-box-orient: vertical;-moz-box-orient: vertical;box-orient: vertical;display: -webkit-box;display: -moz-box;display: box;">
	@php
			$counter = 0;
	@endphp
	@foreach($templateFields as $index=>$allFields)

		@if($groups[$index]["name"] !== 'Banner Studio')
			@php
				$counter = $counter + 1;
			@endphp
			<h3 id="{!!$groups[$index]["id"]!!}" style="-webkit-box-ordinal-group: {!!$counter!!};-moz-box-ordinal-group: {!!$counter!!};box-ordinal-group: {!!$counter!!};"><span>{!!$groups[$index]["name"]!!}</span>
			<div class="hidden @if(\App\TemplatePinSettings::testFormBuilder($template->id) == false) not-stnd @endif">
		@else
			<div style="-webkit-box-ordinal-group: {!!count($templateFields) + 1!!};-moz-box-ordinal-group: {!!count($templateFields) + 1!!};box-ordinal-group: {!!count($templateFields) + 1!!};">
		@endif
			@foreach($allFields as $field)
				@if(!$hasGeoip && isset($field["is_geoip"]) && $field["is_geoip"])
				@else

					@if($field['type'] == 'select')
						<div class="form-group {!!$field['class'] or ''!!}">
							<label id="label_{!!$field['name']!!}">{!!$field['label']!!}</label>
							<span class="use-default-value" onclick="use_default('{!!$field['name']!!}');"> Use default</span>
							<br>
							<select class="form-control form-control-solid" data-live-search="true" data-live-search-placeholder="Search" name="{!!$field['name']!!}" selected='top'>
								@foreach($field['options'] as $key=>$value)
									<option @if($key == $template->settings->{$field['name']})selected="selected"@endif value="{!!$key!!}">{!!$value!!}</option>
								@endforeach
							</select>
						</div>
					@endif

					@if($field['type'] == 'select-multiple')
						<div class="form-group {!!$field['class'] or ''!!}">
							<label id="label_{!!$field['name']!!}">{!!$field['label']!!}</label>
							<br>
							@php
								$selectedArray = [];
								$fname = $field['name'];
								if(!is_array($template->settings->$fname)){
									$selectedArray = explode(',',$template->settings->$fname);
								}
							@endphp
							<select name="{!!$field['name']!!}[]" class="form-control selectpicker" data-live-search="true"
									data-live-search-placeholder="Search" data-container="body"  multiple="multiple" id="{!!$field['name']!!}">
								@foreach($field['options'] as $key=>$value)
									@if(in_array($key, $selectedArray))
										<option value="{!!$key!!}" selected>{!!$value!!}</option>
									@else
										<option value="{!!$key!!}" >{!!$value!!}</option>
									@endif
								@endforeach
							</select>
						</div>
							{{-- <script>
								$(document).ready(function(){
									$('#{!!$field['name']!!}').val({{ '[' . $template->settings->widget_categories . ']' }});
									$('#{!!$field['name']!!}').selectpicker('render');
								});
							</script> --}}
					@endif

					@if($field['type'] == 'select-multiple-optgroup')
						<div class="form-group auto {!!$field['class'] or ''!!}">
							<label id="label_{!!$field['name']!!}">{!!$field['label']!!}</label>
							<br>
							@php
								$selectedArray = [];
								$fname = $field['name'];
								if(!is_array($template->settings->$fname)){
									$selectedArray = explode(',',$template->settings->$fname);
								}
							@endphp
							<select title="{!!$field['label']!!}" class="form-control selectpicker" multiple="multiple" data-live-search="true" data-live-search-placeholder="Search" name="{!!$field['name']!!}[]">
								@forelse ($field['options'] as $optLabel => $options)
									<optgroup label="{!! $field['optGroupLabels'][$optLabel] !!}">
										@foreach ($options as $key=>$value)
											@if(in_array($key, $selectedArray))
											<option value="{!!$key!!}" selected>{!!$value!!}</option>
										@else
											<option value="{!!$key!!}" >{!!$value!!}</option>
										@endif
										@endforeach
									</optgroup>
								@empty
									
								@endforelse
							</select>
						</div>
					@endif

					@if($field['type'] == 'color')
						<div class="input-group {!!$field['class'] or ''!!} colorpicker-component">
							<label id="label_{!!$field['name']!!}">{!!$field['label']!!}</label>
							<span class="use-default-value " onclick="use_default('{!!$field['name']!!}');"> Use default</span><br>
						</div>

						<div class="input-group colorpicker-component">
							<input type="text" name="{!!$field['name']!!}" value="<?php echo $template->settings->{$field['name']} ?>"class="form-control" />
							<span class="input-group-addon"><i></i></span>
						</div>
						<br/>

					@endif

					@if($field['type']  == 'hidden')
						<?php if(is_array($template->settings->{$field['name']})||is_object($template->settings->{$field['name']})) $template->settings->{$field['name']} = json_encode($template->settings->{$field['name']}); ?>
						<textarea id="{!!$field['name']!!}" name="{!!$field['name']!!}" style="display: none;">
							@if(!empty($template->settings->{$field['name']})) <?php echo  $template->settings->{$field['name']} ?> @endif
						</textarea>
					@endif

					{{--type text-field is a normal text input and not a distance message input--}}
					@if($field['type']  == 'text-field')
						<div class="form-group {!!$field['class'] or ''!!}">
							<label id="label_{!!$field['name']!!}">{!!$field['label']!!}</label><span class="use-default-value" onclick="use_default('{!!$field['name']!!}');"> Use default</span><br>
							<input class="form-control" type="text" name="{!!$field['name']!!}" value="<?php echo $template->settings->{$field['name']} ?>"/>
						</div>
					@endif

					{{--type Text represents a distance message input--}}
					@if($field['type']  == 'text')
						@if(!isset($track))
							<?php $track = true; ?>
							<button onclick="add_distance()" type="button" class="btn btn-success btn-block">Add New Distance Message</button>
							<br />
							<div id="allow-multiple-messages">
								<label class="multiple-messages-allowed btn btn-light">Multiple Messages</label>
								<label class="single-messages-allowed btn btn-primary">Single Message</label>
							</div>
							<br />
							<div id="multiple-messages">
								<ul class="nav nav-tabs">
									<li class="active">
										<a href="#default" data-toggle="tab" aria-expanded="true">0 to 5km</a>
									</li>
								</ul>
								<br/>
								<div class="tab-content">
									<div class="tab-pane fade active in" id="default">
									</div>
								</div>
							</div>
						@endif


						<div class="form-group {!!$field['class'] or ''!!} multiple-emssage-default">
							<label id="label_{!!$field['name']!!}">{!!$field['label']!!}</label>
							<span class="use-default-value" onclick="use_default('{!!$field['name']!!}');"> Use default</span>
							<span style="margin-bottom: 5px;" class="use-default-value pull-right" title="Copy to clipboard" onclick="copyToClipboard('{!!$field['name']!!}')"> <i class="far fa-2x fa-clipboard"></i></span>
							<br>
							<input class="form-control autocomplete @if(isset($field["is_geoip"]) && $field["is_geoip"]) geoip_text @endif" type="text" id="{!!$field['name']!!}" name="{!!$field['name']!!}"
							@if(!empty($template->settings->{$field['name']})) value="<?php echo $template->settings->{$field['name']} ?>" @endif />
						</div>
					@endif

					@if($field['type']  == 'radio')

						<div style="height: 60px;    padding-top: 10px;">
							<label>{!!$field['label']!!} </label><br>
							<input @if($template->settings->{$field['name']}) checked="checked" @endif type="checkbox" id="{!!$field['name']!!}" name="{!!$field['name']!!}" >
						</div>
					@endif

					@if($field['type']  == 'checkbox')
						<div class="form-group {!!$field['class'] or ''!!}">
							<label for="label_{!!$field['name']!!}" style="cursor: pointer">{!!$field['label']!!}&nbsp;&nbsp;&nbsp;</label>
							<input  id="label_{!!$field['name']!!}" style="cursor: pointer;display: inline;top: 3px;" class="checkbox" type="checkbox" name="{!!$field['name']!!}" <?php if ($template->settings->{$field['name']}) {echo "checked='checked'";} ?>/>
							<br>
						</div>
					@endif

					@if($field['type']  == 'upload')
						<div class="form-group {!!$field['class'] or ''!!} upload">
							<div class="upload-container" style="display: block;">
								<label id="label_{!!$field['name']!!}">{!!$field['label']!!}</label>
								<span class="use-default-value" onclick="use_default('{!!$field['name']!!}');"> Use default</span>
								<br/>
								@if(isset($field['allow_empty']) && $field['allow_empty'])
									<button class="btn btn-xs btn-danger pull-right" onclick="return removeImage('{!!$field['name']!!}',this);">Remove Image</button>
									<br>
								@endif
								<input type="file" name="{!!$field['name']!!}" class="upload-input">
								<input type="hidden" value="<?php echo $template->settings->{$field['name']} ?>" id="{!!$field['name']!!}"/>

								@if(!empty($template->settings->{$field['name']}))
								<div class="panel panel-default" style="background-color: rgba(221, 221, 221, 0.32);">
									<img src="<?php echo $template->settings->{$field['name']} ?>" id="file_{!!$field['name']!!}"/>
								</div>
								@else
									<img src="<?php echo $template->settings->{$field['name']} ?>" id="file_{!!$field['name']!!}"/>
								@endif

							</div>
						</div>
					@endif

					@if($field['type']  == 'font')
						<div class="form-group {!!$field['class'] or ''!!}">
							<label id="label_{!!$field['name']!!}">{!!$field['label']!!}</label>
							<span class="use-default-value" onclick="use_default('{!!$field['name']!!}');"> Use default</span><br>
							<?php $fonts_json = json_decode($fonts);?>
							<?php
								$template->settings->{$field['name']} = str_replace("+", " ", $template->settings->{$field['name']});
							?>
							<input  value="<?php echo $template->settings->{$field['name']} ?>" class="font-selector" type="text" name="{!!$field['name']!!}">

						</div>
					@endif

					@if($field['type']  == 'custom_font')
						<link rel="stylesheet" type="text/css" href="https://static.vic-m.co/fonts/custom.css">
						<div class="form-group {!!$field['class'] or ''!!}">
							<label id="label_{!!$field['name']!!}">{!!$field['label']!!}</label>
							<span class="use-default-value" onclick="use_default('{!!$field['name']!!}');"> Clear</span><br>
							<span class="alert alert-warning" style="display: inline-block; width: 100%; padding: 2px; margin-bottom: 4px;">Selecting a Custom font will override the Google font</span>
							<select class="form-control selectpicker" name="{!!$field['name']!!}" id="select_{!!$field['name']!!}">
								<option value=""></option>
								<option value="Arial" data-content="<span style='font-family: Arial;'>Arial</span>">Arial</option>
								<option value="Gotham+Black" data-content="<span style='font-family: Gotham Black;'>Gotham Black</span>">Gotham Black</option>
								<option value="Gotham+Bold" data-content="<span style='font-family: Gotham Bold;'>Gotham Bold</span>">Gotham Bold</option>
								<option value="Helvetica+Light" data-content="<span style='font-family: Helvetica Light;'>Helvetica Light</span>">Helvetica Light</option>
								<option value="Block+Berthold+Textured+Regular" data-content="<span style='font-family: Block Berthold Textured Regular;'>Block Berthold Textured Regular</span>">Block Berthold Textured Regular</option>
								<option value="TrashHand" data-content="<span style='font-family: TrashHand;'>TrashHand</span>">TrashHand</option>
								<option value="DIN+Next+LT+Pro+Bold" data-content="<span style='font-family: DIN Next LT Pro Bold;'>DIN Next LT Pro Bold</span>">DIN Next LT Pro Bold</option>
								<option value="DIN+Next+LT+Pro+Bold+Condensed" data-content="<span style='font-family: DIN Next LT Pro Bold Condensed;'>DIN Next LT Pro Bold Condensed</span>">DIN Next LT Pro Bold Condensed</option>
								<option value="DIN+Next+LT+Pro+Medium" data-content="<span style='font-family: DIN Next LT Pro Medium;'>DIN Next LT Pro Medium</span>">DIN Next LT Pro Medium</option>
								<option value="DIN+Next+LT+Pro+Medium+Condensed" data-content="<span style='font-family: DIN Next LT Pro Medium Condensed;'>DIN Next LT Pro Medium Condensed</span>">DIN Next LT Pro Medium Condensed</option>
								<option value="Humnst777+BlkCn+BT" data-content="<span style='font-family: Humnst777 BlkCn BT;'>Humnst777 BlkCn BT</span>">Humnst777 BlkCn BT</option>
								<option value="Veneer" data-content="<span style='font-family: Veneer;'>Veneer</span>">Veneer</option>
								<option value="Veneer+Clean+Soft" data-content="<span style='font-family: Veneer Clean Soft;'>Veneer Clean Soft</span>">Veneer Clean Soft</option>
								<option value="Foundry+Monoline" data-content="<span style='font-family: Foundry Monoline;'>Foundry Monoline</span>">Foundry Monoline</option>
								<option value="FoundryMonoline+Bold" data-content="<span style='font-family: Foundry Monoline;'>Foundry Monoline Bold</span>">Foundry Monoline Bold</option>
							</select>
						</div>
						<script>
							$(document).ready(function(){
								$("[name={!!$field['name']!!}]").val('<?php echo $template->settings->{$field['name']} ?>');
								$('[name={!!$field['name']!!}]').selectpicker('render');
							});
						</script>
					@endif

					@if($field['type']  == 'form_builder')
						<style>
							.ui-accordion .ui-accordion-content.ui-widget-content{
								padding: 10px!important;
							}
							.frmb.stage-wrap.pull-left.ui-sortable.empty,
							.frmb.stage-wrap.pull-right.ui-sortable.empty{
								min-height: 300px !important;
							}
							.form-group.className-wrap,
							.form-group.name-wrap,
							.form-group.access-wrap,
							.form-group.style-wrap,
							.get-data,
							.save-template {
								display:none !important;
							}

							.form-wrap.form-builder .form-actions {
								float: left;
								margin-top: 5px;
							}

							.draggable {
								touch-action: none;
								user-select: none;
							}

							#dynamic-form{
								width: 100%;
								min-height: 550px;
							}

							[class^="icon-"]:before, [class*=" icon-"]:before {
								margin-right: .3em;
								font-weight: bolder;
							}

							[class^="fa"]:before, [class*=" fa"]:before,
							[class^="fas"]:before, [class*=" fas"]:before {
								font-style: normal;
								speak: none;
								display: inline-block;
								text-decoration: inherit;
								width: 1em;
								margin-right: .3em;
								text-align: center;
								font-variant: normal;
								text-transform: none;
								line-height: 1em;
								margin-left: .2em;
							}

							#form-pages-tab .nav-tabs>li>a {
								color: #999;
								padding-right: 25px;
							}

							#form-pages-tab #tabs>li.active>a, .nav-tabs>li.active>a:focus, .nav-tabs>li.active>a:hover {
								background-color: #FFF;
							}

							#form-pages-tab #tabs .nav-tabs>li.active>a {
								color: #000;
							}

							#form-pages-tab ul li .close {
								display: none;
							}

							#form-pages-tab ul li.active .close {
								display: block;
								position: absolute;
								top: 2px;
								right: 4px;
								margin-bottom: -25px;
								color: red;
								border-radius: 50%;
								padding: 0px;
								width: 20px;
							}

							#form-pages-tab ul li .close:hover {
								background: #ccc;
							}

							#form-pages-tab .ui-tabs-panels{
								display: none;
							}

							#frameBuilder{
								position: relative;
							}
							.fullscreen-toggle{
								position: absolute;
								top:5px;
								left:5px;
								z-index: 1;
								padding:5px 10px;
								border-radius:5px;
								background:#fff;
							}
							.fullscreen-toggle .fa-arrows-alt{
								transform: rotate(45deg);
							}
							.bigSize{
								position:fixed;
								top:0;
								bottom:0;
								left:0;
								right:0;
								overflow:none;
								z-index: 99999;
								min-height: 100vh!important;
							}
							.bigSize .fullscreen-toggle{
								position: fixed!important;
							}
							.bigSize.one-point-five{
								transform:scale(1.5);
								transform-origin: top left;
							}
						</style>
						<div id="dynamic-form">
							<div class="form-wrap form-builder ">
								<div class="col-md-12" id="form-pages-tab" style="margin-bottom: 20px">
									<div class="pull-left">
										<button type="button" class="add-page" id="add-page" onclick="addPage()"><span class="fa fa-plus"></span></button>
									</div>

									<div id="tabs" class="" style="float: left;margin-left: 15px;">
										<ul class="ui-tabs-nav nav nav-tabs"></ul>
										<div class="ui-tabs-panels"></div>
									</div>
								</div>

								<div class="col-md-2">
									<div class="cb-wrap" style="width: 100% !important">
										<div class="form-actions btn-group">
											<button onclick="clearForm()" type="button" class="clear-all btn btn-danger" ><span class="tooltip-b fa fa-trash"><span class="tooltiptext">Clear</span></span></button>
											<button onclick="save_custom_settings('preview');" class="btn btn-primary" id="refresh-preview" type="button"><span class="tooltip-b fa fa-eye"><span class="tooltiptext">Preview</span></span></button>
										</div>
										<br><br>
										<ul class="widgets ui-sortable">
											@foreach(\App\TemplatePinSettings::dynamicFormFields() as $value)
												<li onclick="addField('{{$value['id']}}')" class="input-control input-control-9 ui-sortable-handle tooltip-b btn btn-primary" >
													<i class="{{$value['icon']}}" aria-hidden="true"></i><span class="tooltiptext">{{$value['name']}}</span>
												</li>
											@endforeach
										</ul>

									</div>
								</div>
								<div class="col-md-10">
									<div id="frameBuilder">
										<div align="center" class="preview-window preview-div">
										</div>
									</div>

								</div>

							</div>
						</div>

						<script type="text/javascript">
							var activePageIndex = 0;
							var pageCount = 0;
							var defaultValues;
							var customFieldTypes = {!!json_encode(\App\TemplatePinSettings::dynamicFormFields())!!};
							var globalPageSettings = {!!json_encode(\App\TemplatePinSettings::dynamicFormFields())!!}; //Pull default settings for global page settings
							var pageTabs, pageTabCounter;

							function getTemplateSetting(selector){
								return JSON.parse($(selector).val().trim() || "[]");
							}
							function setTemplateSetting(selector, value){
								$(selector).val(value ? JSON.stringify(value) : '').change();
							}
							function initialiseDefaults(){
								defaultValues = {
									page: {
										'id': 0,//new Date().getTime(),
										'name': 'New Page',
										'deleted_at':false,
										'page-settings': {
											'transition': {
												'display_times': 1
											},
										}
									},
								}
								customFieldTypes.forEach(function(field,index){
									defaultValues[field.id] = field.defaults
								})
							}
							function clearForm(){
								if(confirm("Are you sure you want to clear the form?")){
									setTemplateSetting("#fieldsFormsBuilder");
									setTemplateSetting("#pageSettings");
									setTemplateSetting("#globalPageSettings");
									addPage();
								}
							}
							function addField(fieldType){
								var fieldList = getTemplateSetting("#fieldsFormsBuilder");
								var pages = getTemplateSetting("#pageSettings");

								var newField = defaultValues[fieldType][fieldType];
								newField['id'] = 'item-'+fieldList.length;
								newField['name'] = 'Label '+(fieldList.length + 1);
								newField['page'] = activePageIndex;
								//newField['pageId'] = pages[activePageIndex].id;

								fieldList.push(newField);
								saveFieldSettings(fieldList, true);
							}
							function addPage(){
								var pages = getTemplateSetting("#pageSettings");
								var newPage = defaultValues.page;
								newPage['id'] = pages.length; //new Date().getTime();
								newPage['name'] = 'New Page';
								pages.push(newPage);
								pageCount++;
								activePageIndex = pages.findIndex(p => {return p.id == newPage.id});
								savePageSettings(pages, true);
								return false;
							}
							function setActivePage(pageIndex, element){
								activePageIndex = pageIndex;

								$("#form-pages-tab ul li").removeClass('active');
								$('li#page-'+activePageIndex).addClass('active');

								$('#frameBuilder  iframe').contents().find('.creativeImage').hide();
								$('#frameBuilder  iframe').contents().find('#page-'+activePageIndex).show();
								sendMessage('setActivePage');
								$('#frameBuilder  iframe').contents().find('#pageId').val(activePageIndex);
								return false;
							}
							function removePage(event, pageIndex, target){
								if(confirm(`Are you sure you want to delete this page`)){
									var pages = getTemplateSetting("#pageSettings");
									if(pages[pageIndex]){
										pages[pageIndex].deleted_at = (new Date()).toISOString();
										pageCount--;
									}
									activePageIndex = pages.findIndex(p => {return p.deleted_at === false;});
									savePageSettings(pages, true);
									if(pageCount==0){
										addPage();
									}
								}
								if (event.stopPropagation){
									event.stopPropagation();
								} else if(window.event){
									window.event.cancelBubble=true;
								}
								return false;
							}
							function printPageButtons(){
								var pages = getTemplateSetting("#pageSettings");
								clearPageTabs();
								pageCount = 0;
								for(i in pages){
									if(pages[i].deleted_at===false){
										pageCount++;
									}
									addPageTab(i,pages[i].name,'',pages[i].deleted_at!==false);
								}
								setActivePage(activePageIndex);
							}
							function saveGlobalPageSettings(settings, preview=true){
								setTemplateSetting("#globalPageSettings",settings);
								if(preview){
									save_custom_settings('preview');
								}
							}
							function savePageSettings(pages, preview=true){
								setTemplateSetting("#pageSettings",pages);
								printPageButtons();
								if(preview){
									save_custom_settings('preview');
								}
							}
							function saveFieldSettings(fields, preview=true){
								setTemplateSetting("#fieldsFormsBuilder",fields);
								if(preview){
									save_custom_settings('preview');
								}
							}
							function toggleGroupVisibility(){
								$('#banner-temp-settings h3#text-message').hide();
								$('#banner-temp-settings h3#pin-colors').hide();

								customFieldTypes.forEach(function(field,index){
									$(`#banner-temp-settings h3#${field.id}_group`).hide();
								});

								var fieldList = getTemplateSetting("#fieldsFormsBuilder");

								$(fieldList).each(function(i,field){
									$('#banner-temp-settings h3#'+field.type+'_group').show();
									if(field.type=="distance_message"){
										$('#banner-temp-settings h3#text-message').show();
									}
									if(field.type=="distance_pin"){
										$('#banner-temp-settings h3#pin-colors').show();
									}
								});
							}
							function sendMessage(msg) {
								var frameDesigner = $('#frameBuilder iframe').get(0);
								frameDesigner ? frameDesigner.contentWindow.postMessage(''+msg, '*') : '';
							}
							function addPageTab(id, label, tabContentHtml = '',hidden=false) {
								var style = hidden?'style="display:none;"':'';
								var li = `<li ${style} onclick="setActivePage('${id}',this)" id="page-${id}" role="page-${id}"><a href="#${id}">${label}</a><button type="button" class="close" onclick="removePage(event,'${id}',this)">×</button></li>`;
								var div = `<div ${style} id="${id}"><p>${tabContentHtml}</p></div>`;
								pageTabs.find( ".ui-tabs-nav" ).append( li );
								pageTabs.find( ".ui-tabs-panels" ).append( div );
							}
							function clearPageTabs() {
								pageTabs.find( ".ui-tabs-nav" ).empty();
								pageTabs.find( ".ui-tabs-panels" ).empty();
							}
							function  initPageTabs() {
								pageTabs = $( "#tabs" )
								pageTabs.find( "ul" ).sortable({
									connectWith: "#tabs ul",
									axis: "x",
									stop: function( event, ui ) {
										var pages = getTemplateSetting("#pageSettings");
										var tempPages = [];
										$(event.target.children).each(function( index, child ) {
											var pageIndex = parseInt(child.id.replace('page-',''));
											tempPages.push(pages[pageIndex]);
										});
										savePageSettings(tempPages,true);
									}
								});
							}

							$(document).ready(function(){
								initialiseDefaults();
								toggleGroupVisibility();
								$("#fieldsFormsBuilder").change(function(){
									toggleGroupVisibility();
								});
								initPageTabs();
								printPageButtons();
								var testWidth = parseFloat($('.preview-window.preview-div').width());
								if (testWidth < 600){
									$('.col-md-11').addClass('col-md-10');
									$('.col-md-11').removeClass('col-md-11');
									$('.col-md-1').addClass('col-md-2');
									$('.col-md-2').removeClass('col-md-1');
								}
							});

						</script>

					@endif

					@if($field['type']  == 'font_size')
						<div class="form-group {!!$field['class'] or ''!!}">
							<label id="label_{!!$field['name']!!}">{!!$field['label']!!}</label><span class="use-default-value" onclick="use_default('{!!$field['name']!!}');"> Use default</span><br>
							<input class="form-control" type="number" min="{{$field['min'] ?? 1 }}" max="{{$field['max'] ?? 100 }}" name="{!!$field['name']!!}" value="<?php echo $template->settings->{$field['name']} ?>"/>
						</div>
					@endif

					@if($field['type']  == 'border_radius')
						<div class="form-group {!!$field['class'] or ''!!}">
							<label id="label_{!!$field['name']!!}">{!!$field['label']!!}</label><span class="use-default-value" onclick="use_default('{!!$field['name']!!}');"> Use default</span><br>
							<input class="form-control" min="1" type="number" name="{!!$field['name']!!}" value="<?php echo $template->settings->{$field['name']} ?>"/>
						</div>
					@endif

					@if($field['type']  == 'text_transform')
						<div class="form-group {!!$field['class'] or ''!!}">
							<label id="label_{!!$field['name']!!}">{!!$field['label']!!}</label>
							<span class="use-default-value" onclick="use_default('{!!$field['name']!!}');"> Clear</span><br>
							<select class="form-control selectpicker" name="{!!$field['name']!!}" id="select_{!!$field['name']!!}">
								<option value=""></option>
								<option value="uppercase" data-content="<span style='text-transform: uppercase;'>uppercase</span>">UPPERCASE</option>
								<option value="lowercase" data-content="<span style='text-transform: lowercase;'>lowercase</span>">lowercase</option>
								<option value="capitalize" data-content="<span style='text-transform: capitalize;'>capitalize</span>">Capitalize</option>
							</select>
						</div>
						<script>
							function applySelectedCase(inputText, selectedCase){

								var macros = inputText.match(/(\%\%(\w+)\%\%)/g) || false;

								if(macros) {
									inputText = inputText.replace(/(\%\%(\w+)\%\%)/g,'#');
								}

								switch(selectedCase){
									case 'uppercase': {
										inputText = inputText.toUpperCase();
									} break;
									case 'lowercase': {
										inputText = inputText.toLowerCase();
									} break;
									case 'capitalize': {
										inputText = inputText.toLowerCase().replace(/\b./g, function(a){ return a.toUpperCase(); } );
									} break;
									default: {
									} break;
								}

								if(macros){ inputText = macros.reduce(function(p,c){return p.replace(/#/,c)},inputText); }

								return inputText;
							}

							function applyCase(){
								var selectedCase = $('#select_{!!$field['name']!!}').val();
								var inputs = ['#distance_value_text', '#distance_value_text_geoip', '#from_location', '#from_location_geoip'];

								for(i in inputs){
									var currentText = $(inputs[i]).val() || false;
									if(currentText){
										var changedText = applySelectedCase(currentText,selectedCase);
										$(inputs[i]).val(changedText);
									}
								}
							}

							$(document).ready(function(){

								$("[name={!!$field['name']!!}]").val('<?php echo $template->settings->{$field['name']} ?>');
								$('[name={!!$field['name']!!}]').selectpicker('render');

								$('#select_{!!$field['name']!!}').on('change', function () {
									applyCase();
								});

								applyCase();
							});
						</script>
					@endif

					@if($template->id == 91)
						<script>
						$('select[name=pin_templates_id]').on('change', function() {
							widgetMessage();
						});
						$(document).ready(function(){
							$('#other').hide();
						});
						</script>
					@endif

					@if($template->id == 49 || $template->id == 51)
					<script>

						$(document).ready(function() {
							function changedOption() {
								var type = $("select[name='orientation_type']").val();
								$("select[name='transition_direction'] option").show();
							if(type == 'h' || type == 'width'){
								$("#label_transition_direction").show();
								$("select[name='transition_direction'] option[value='topToBottom']").hide();
								$("select[name='transition_direction'] option[value='bottomToTop']").hide();
								$("select[name='transition_direction']").show();
								$("select[name='transition_direction']").val("leftToRight");
							}
							if(type == 'v' || type == 'height'){
								$("#label_transition_direction").show();
								$("select[name='transition_direction'] option[value='leftToRight']").hide();
								$("select[name='transition_direction'] option[value='rightToLeft']").hide();
								$("select[name='transition_direction']").show();
								$("select[name='transition_direction']").val("topToBottom");
							}
							if(type == 'r' || type == 'none'){
								$("#label_transition_direction").hide();
								$("select[name='transition_direction']").hide();
							}

							}

							$("select[name='orientation_type']").on('change',function() {
							changedOption();
							});
						});

					</script>

					@endif

					@if($field['type']  == 'number')
						<div class="form-group {!!$field['class'] or ''!!}">
							<label id="label_{!!$field['name']!!}">{!!$field['label']!!}</label><span class="use-default-value" onclick="use_default('{!!$field['name']!!}');"> Use default</span><br>
							<input class="form-control" type="number" name="{!!$field['name']!!}" value="<?php echo $template->settings->{$field['name']} ?>"/>
						</div>
					@endif

					@if($field['type']  == 'loop')
						<?php
								$min = isset($field['min']) ? $field['min'] : 0;
								$max = isset($field['max']) ? $field['max'] : 100;
								$multiply = isset($field['multiply']) ? $field['multiply'] : 1000;
						?>
						<div class="form-group {!!$field['class'] or ''!!}">
							<label id="label_{!!$field['name']!!}">{!!$field['label']!!}: </label>
							<span style="color:red" id="speed-label-{!!$field['name']!!}"><?php echo $template->settings->{$field['name']} ?></span>
							<span class="use-default-value" onclick="use_default('{!!$field['name']!!}');"> Use default</span>
							<br>
							<div id="slider_{!!$field['name']!!}"></div>
							<input class="form-control" type="hidden" name="{!!$field['name']!!}" value="<?php echo $template->settings->{$field['name']} ?>"/>
						</div>
						<script>
							$(document).ready(function(){
								$( "#slider_{!!$field['name']!!}" ).slider({
								range: "max",
								min: {!!$min!!},
								max: {!!$max!!},
								value: <?php echo $template->settings->{$field['name']} / $multiply ?>,
								slide: function( event, ui ) {
									$( "input[name='{!!$field['name']!!}']" ).val( ui.value * {!!$multiply!!} );
									$( "#speed-label-{!!$field['name']!!}" ).text( ui.value );
								}
								});
								$( "input[name='{!!$field['name']!!}']" ).val( $( "#slider_{!!$field['name']!!}" ).slider( "value" ) * {!!$multiply!!} );
								$( "#speed-label-{!!$field['name']!!}" ).text( $( "#slider_{!!$field['name']!!}" ).slider( "value" )  );
							});
						</script>
					@endif

				@endif
			@endforeach
		</div>
		@if($groups[$index]["name"] !== 'Banner Studio')
			</h3>
		@endif
	@endforeach
	<i class="fa fa-eye-slash hidden" id="hide-tab-options" onclick="hideTabOptions()"><span>Hide</span></i>
	@if($groups[$index]["name"] == 'Banner Studio')
		<!-- <a class="btn absolute-top-right hidden" onclick="save_custom_settings('preview');"><i class="fa fa-eye"></i> Preview</a> -->
	@endif
	</div>

@endif


<script type="text/javascript">
	var multiplyDistances = [];
	var availableTags = [
		"%%DISTANCE%% From your location",
		"%%DISTANCE%% from %%STORENAME%%",
		"%%DISTANCE%%",
		"%%STORENAME%%",
		"%%ADDRESS1%%",
		"%%ADDRESS2%%",
		"%%SUBURB%%",
		"%%CITY%%",
		"%%CUSTOMLOCMSG%%"
	];
	var templateSettings  = <?php echo json_encode($template->settings);?>;

	@if(isset($template->settings->multipleDistanceKey))
		multiplyDistances = <?php echo json_encode(unserialize($template->settings->multipleDistanceKey));?>;
	@endif

	function hideTabOptions(){
		$('#banner-temp-settings > h3 > div').addClass('hidden');
		$('#hide-tab-options').addClass('hidden');
		$('.stnd').css('padding-bottom', '0px');
	}
	$(document).ready(function(){
		@if(isset($template->settings->sample_campaign_location))
		$('select[name=sample_campaign_location]').val({!!$template->settings->sample_campaign_location!!});
		@endif

		/* $('.font-selector').fontselect(); */
		$(".font-selector").fontpicker({
            lang: 'en',
            variants: false,
            nrRecents: 5,
            localFontsUrl: '/templates/libs/fontpicker/0.9.1/fonts/',
            localFonts: {
                "Corporate_A_Bold": {
                },
                "Corporate_A_Light": {
                },
                "TrashHand": {
                },
                "DINNextLTPro-Bold": {
                },
                "DINNextLTPro-BoldCondensed": {
                },
                "DINNextLTPro-Medium": {
                },
                "DINNextLTPro-MediumCond": {
                },
				"Foundry-Monoline": {
                },
				"FoundryMonoline-Bold": {
                },
                "Humanist777BT-BlackB": {
                },
                "Humanist777BT-ItalicB": {
                },
                "Humanist777BT-BlackCondensedB": {
                },
                "Humanist777BT-LightB": {
                },
                "Humanist777BT-BlackItalicB": {
                },
                "Humanist777BT-LightCondensedB": {
                },
                "Humanist777BT-BoldB": {
                },
                "Humanist777BT-LightItalicB": {
                },
                "Humanist777BT-BoldItalicB": {
                },
                "Humanist777BT-RomanB": {
                },
                "Humanist777BT-ExtraBlackB": {
                },
                "Veneer": {
                },
                "Humanist777BT-ExtraBlackCondB": {
                },
                "VeneerClean-Soft": {
                },

            }
        });

		$.fn.bootstrapSwitch.defaults.onColor = 'primary';
		$.fn.bootstrapSwitch.defaults.offColor = 'default';
		$.fn.bootstrapSwitch.defaults.onText = 'On';
		$.fn.bootstrapSwitch.defaults.offText = 'Off';
		$("#geoip_preview").bootstrapSwitch();

		$.fn.bootstrapSwitch.defaults.onColor = 'primary';
		$.fn.bootstrapSwitch.defaults.offColor = 'warning';
		$.fn.bootstrapSwitch.defaults.onText = 'Yes';
		$.fn.bootstrapSwitch.defaults.offText = 'No';

		$("#banner_static_geoip").bootstrapSwitch();
		makefieldUneditable($("#banner_static_geoip").prop("checked"));

		var e = $(".multiple-emssage-default").clone();

		$(".multiple-emssage-default").remove();
		$("#multiple-messages .tab-content .tab-pane").append(e);
		$("#multiple-messages .active a").text("0 to "+formatDistance(maxDistance));
		$("#multiple-messages .active").attr("data-id",maxDistance);
		$("#default").append("<input type='hidden' value='"+maxDistance+"' name='multipleDistanceKey[]' />");

		$('#allow-multiple-messages label').click(function() {
			$('#allow-multiple-messages label').each(function() {
				$(this).removeClass('btn-primary')
				$(this).addClass('btn-light')
			})

			$(this).addClass('btn-primary')
			$(this).removeClass('btn-light')
		})

		$('.single-messages-allowed').click(function() {
			$('#multiple-messages ul').addClass('hide-all-but-last-li')

			$('.hide-all-but-last-li li:last-child a').trigger('click')
		})

		$('.multiple-messages-allowed').click(function() {
			$('#multiple-messages ul').removeClass('hide-all-but-last-li')

			for (key in multiplyDistances) {
				if (multiplyDistances[key] == maxDistance) continue
				
				add_distance(multiplyDistances[key], true);

				$("[name='distance_value_text_"+ multiplyDistances[key] +"']").val(templateSettings["distance_value_text_"+multiplyDistances[key]]);
				$("[name='distance_value_text_geoip_"+ multiplyDistances[key] +"']").val(templateSettings["distance_value_text_geoip_"+multiplyDistances[key]])
			};
		})

		$('.single-messages-allowed').trigger('click')

		$('.autocomplete').autocomplete({
			source: availableTags,
			highlightItem: true,
			multiple: true,
			minLength: 0,
			multipleSeparator: " ",
			select: function(event, ui){
				if (event.target.id == 'distance_value_text') {
					$("#distance_value_text").val($("#distance_value_text").val()+" "+ui.item.label);
				} else if(event.target.id == 'distance_value_text_geoip') {
					$("#distance_value_text_geoip").val($("#distance_value_text_geoip").val()+" "+ui.item.label);
				} else if(event.target.id == 'from_location') {
					$("#from_location").val($("#from_location").val()+" "+ui.item.label);
				} else if(event.target.id == 'from_location_geoip') {
					$("#from_location_geoip").val($("#from_location_geoip").val()+" "+ui.item.label);
				}
				return false;
			},
			change: function( event, ui ) {
				var inputs = ['distance_value_text', 'distance_value_text_geoip', 'from_location', 'from_location_geoip'];
				for(i in inputs){
					if(inputs[i]==event.target.id){
						applyCase();
					}
				}
			},
			close: function(){
				$(this).autocomplete('search', '');
			}
		}).focus(function(){
				$(this).autocomplete('search', '');
		});
	});
	
	$('#banner-temp-settings > h3 > span').click(function(){
		if ($(this).parent('h3').children('div').hasClass('hidden') == true){
			$('#banner-temp-settings > h3 > div').addClass('hidden');
			$(this).parent('h3').children('div.hidden').removeClass('hidden');
			$('.stnd #hide-tab-options').removeClass('hidden');
			var newHeight = $(this).parent('h3').children('div').height() + 30;
			$('.stnd').css('padding-bottom', newHeight+'px');
		}
		else{
			$('#banner-temp-settings > h3 > div').addClass('hidden');
			$('#hide-tab-options').addClass('hidden');
			$('.stnd').css('padding-bottom', '0px');
		}
	});
	
	function initCloseListener(){
		$('#banner-temp-settings > div, #banner-temp-settings iframe').click(function(){
			$('#banner-temp-settings > h3 > div').addClass('hidden');
		});
		$('#previewTarget').contents().on("mousedown, mouseup, click", function(){
			$('#banner-temp-settings > h3 > div').addClass('hidden');
		});
	}

	$('input[name="banner_static_geoip"]').on('switchChange.bootstrapSwitch', function(event, state) {
		makefieldUneditable(state);
	});

	function use_default(name){
		var template = {!!$template!!};
		var defaultCurrent = defaultSettings[template.id];

		for(var i = 0;i < defaultCurrent.length;i++){
			if(defaultCurrent[i].name == name){
				if($('[name='+name+']').attr("disabled") != "disabled"){
					if(defaultCurrent[i].type == 'upload'){
						$('#'+name).val(defaultCurrent[i].value);
						$('#file_'+name).attr('src',defaultCurrent[i].value);
						$('#file_'+name).show();
					}else if(defaultCurrent[i].type == 'custom_font'){
						$('[name='+name+']').val(defaultCurrent[i].value);
						$('[name='+name+']').selectpicker('render');
					}else if(defaultCurrent[i].type == 'text_transform'){
						$('[name='+name+']').val(defaultCurrent[i].value);
						$('[name='+name+']').selectpicker('render');
					}else{
						$('[name='+name+']').val(defaultCurrent[i].value);
					}
				}else{
					swal("Enable the field first");
				}
			}
		}
	}

	function formatDistance(distance){
		var name = distance+"m";
		if(distance >= 1000){
			var name = (distance / 1000).toFixed(1)+"km";
		}

		return name;
	}

	function remove_distance(id){
		if(confirm("Are you sure you want to delete")){
			var list = $("#multiple-messages .nav-tabs li");
			list.each(function(index,value){
				if($(value).attr("data-id") == id){
					$(value).remove();
					$("#home-"+id).remove();
					merry_go_round();
					$("#multiple-messages .nav-tabs li:first-child a").click();
					return false;
				}
			});
		}
		return false;
	}

	function merry_go_round(){
		var list = $("#multiple-messages .nav-tabs li");

		list.each(function(id,value){
			$(value).find("a").text(formatDistance($(value).attr("data-id")));
		});

		list.sort(function(a, b){
			return $(a).attr("data-id")-$(b).attr("data-id")
		});

		var temp = [];
		var temp2 = [];
		list.each(function(id,value){
			var id = $(value).attr("data-id") * 1;
			if(temp.indexOf(id) == -1){
				temp.push(id);
				temp2.push(value);
			}
		});

		$("#multiple-messages .nav-tabs").html(temp2);
		var value = $("#multiple-messages .nav-tabs li:first-child").attr('data-id');
		$("#multiple-messages .nav-tabs li:first-child a").text("0 to "+formatDistance(value));
	}

	function isMultipleMessage() {
		let messageAllowed = true

		$('#allow-multiple-messages label').each(function(e, i) {
			if ($(this).hasClass('btn-primary')) {
				if ($(this).hasClass('single-messages-allowed')) {
					messageAllowed = false

					alert("Please select Multiple Messages, and then add a new distance message")	
				}
			}
		})

		return messageAllowed
	}

	function add_distance(distance,msg){
		if(!distance && isMultipleMessage() == true)
			var distance = prompt("Enter the distance in meters: "+maxDistance);

		if(distance){
			var list = $("#multiple-messages .nav-tabs li");
			var bigStuff = true;
			error = "";
			if(isNaN(distance)){
				error = "Enter a numeric value";
				alert(error);
				return false;
			}
			distance = parseInt(distance);
			if(distance >= maxDistance){
				error = "Distance can not be equal to the maximum distance";
				alert(error);
				return false;
			}

			if(distance <= 0){
				error = "Distance can not be zero or less";
				alert(error);
				return false;
			}

			list.each(function(id,value){
				if($(value).attr("data-id") == distance){
					error = "Distance already captured";
					bigStuff =  false;
				}
			});

			if(!bigStuff && !msg){
				alert(error);
				return false;
			}

			var id = "home-"+distance;
			var name = formatDistance(distance);

			$("#multiple-messages .nav-tabs").append('<li class="" data-id="'+distance+'"><a href="#'+id+'" data-toggle="tab" aria-expanded="false">'+name+'</a></li>');
			
			var e = $("#multiple-messages .tab-content .tab-pane:first-child").clone().prop("id",id).removeClass("active");
			
			e.append("<button onclick='return remove_distance("+distance+")' class='btn btn-danger btn-sm'>Delete for: "+name+"</button>");
			e.append("<input type='hidden' value='"+distance+"' name='multipleDistanceKey[]' />");
			e.find('.use-default-value').remove();
			e.find("input").each(function(i,v){
				e.find(v).attr("name",$(v).attr('name')+"_"+distance);
			});
			$("#multiple-messages .tab-content").append(e);

			var distanceInputs = ['distance_value_text', 'distance_value_text_geoip', 'from_location', 'from_location_geoip'];
			for(i in distanceInputs){
				var selectorName = "[name='" + distanceInputs[i] + "_" + distance + "']";
				var targetName   = distanceInputs[i] + "_" + distance;
				$(selectorName).autocomplete({
					source: availableTags,
					highlightItem: true,
					multiple: true,
					minLength: 0,
					multipleSeparator: " ",
					select: function(event, ui){
						if (event.target.name == 'distance_value_text_'+distance) {
							$("[name='distance_value_text_" + distance + "']").val($("[name='distance_value_text_" + distance + "']").val()+" "+ui.item.label);
						} else if(event.target.name == 'distance_value_text_geoip_'+distance) {
							$("[name='distance_value_text_geoip_" + distance + "']").val($("[name='distance_value_text_geoip_" + distance + "']").val()+" "+ui.item.label);
						} else if(event.target.name == 'from_location_'+distance) {
							$("[name='from_location_" + distance + "']").val($("[name='from_location_" + distance + "']").val()+" "+ui.item.label);
						} else if(event.target.name == 'from_location_geoip_'+distance) {
							$("[name='from_location_geoip_" + distance + "']").val($("[name='from_location_geoip_" + distance + "']").val()+" "+ui.item.label);
						}

						return false;
					},
					close: function(){
						$(this).autocomplete('search', '');
					}
				}).focus(function(){
					$(this).autocomplete('search', '');
				});
				$(selectorName).on('blur', function () {
					$('.ui-autocomplete').hide();
				});
			}

			merry_go_round();
		}

	}

	$("#label_customisable_pin_yn").click(function(){
		if(!this.checked){
			use_default('pin_img');
		}
	});

	function removeImage(name,btn){
		$(btn).hide();
		$('#'+name).val('');
		$('#file_'+name).hide();
		return false;
	}

	function getImageSize(){
		//testin
	}

	function makefieldUneditable(value){
		if(value){
			$(".geoip_text").attr("disabled","disabled");
		}else{
			$(".geoip_text").removeAttr("disabled");
		}
	}

	var _URL = window.URL || window.webkitURL;
	$(".upload-input").change(function (e) {
		var file, img;
		var name = $(this).attr('name');

		if ((file = this.files[0])) {
			img = new Image();
			img.onload = function () {

				var img = document.getElementById("file_"+name);
				var imageExt = file.name.split('.').pop().toLowerCase();

				var width = img.clientWidth;
				var height = img.clientHeight;

				$('#submit-button').prop('disabled',true);

				//TODO: Fix issue with width and height not being set because the file_pin_img is empty
				if(width != 0 || height != 0){
					if( this.width != width || this.height != height){
						swal('Upload an image that is: '+width+"X"+height + '. Refer to https://vicinity-media.com/wiki/ for more instructions.');
						return false;
					}
				}

				if(imageExt != 'png'){
					swal('Upload an image that is a PNG, with a transparent background. Refer to https://vicinity-media.com/wiki/ for more instructions.');
					return false;
				}

				$('#submit-button').prop('disabled',false);

			};
			img.src = _URL.createObjectURL(file);
		}
	});

	$(function() {
		$('.colorpicker-component').colorpicker({});
	});

	(function( $ ) {
		$.widget( "custom.combobox", {
		_create: function() {
			this.wrapper = $( "<span>" )
			.addClass( "custom-combobox" )
			.insertAfter( this.element );

			this.element.hide();
			this._createAutocomplete();
			this._createShowAllButton();
		},

		_createAutocomplete: function() {
			var selected = this.element.children( ":selected" ),
			value = selected.val() ? selected.text() : "";

			this.input = $( "<input>" )
			.appendTo( this.wrapper )
			.val( value )
			.attr( "title", "" )
			.addClass( "custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left" )
			.autocomplete({
				delay: 0,
				minLength: 0,
				source: $.proxy( this, "_source" )
			})
			.tooltip({
				tooltipClass: "ui-state-highlight"
			});

			this._on( this.input, {
			autocompleteselect: function( event, ui ) {
				ui.item.option.selected = true;
				this._trigger( "select", event, {
				item: ui.item.option
				});
			},

			autocompletechange: "_removeIfInvalid"
			});
		},

		_createShowAllButton: function() {
			var input = this.input,
			wasOpen = false;

			$( "<a>" )
			.attr( "tabIndex", -1 )
			.attr( "title", "Show All Items" )
			.tooltip()
			.appendTo( this.wrapper )
			.button({
				icons: {
				primary: "ui-icon-triangle-1-s"
				},
				text: false
			})
			.removeClass( "ui-corner-all" )
			.addClass( "custom-combobox-toggle ui-corner-right" )
			.mousedown(function() {
				wasOpen = input.autocomplete( "widget" ).is( ":visible" );
			})
			.click(function() {
				input.focus();

				// Close if already visible
				if ( wasOpen ) {
				return;
				}

				// Pass empty string as value to search for, displaying all results
				input.autocomplete( "search", "" );
			});
		},

		_source: function( request, response ) {
			var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
			response( this.element.children( "option" ).map(function() {
			var text = $( this ).text();
			if ( this.value && ( !request.term || matcher.test(text) ) )
				return {
				label: text,
				value: text,
				option: this
				};
			}) );
		},

		_removeIfInvalid: function( event, ui ) {

			// Selected an item, nothing to do
			if ( ui.item ) {
			return;
			}

			// Search for a match (case-insensitive)
			var value = this.input.val(),
			valueLowerCase = value.toLowerCase(),
			valid = false;
			this.element.children( "option" ).each(function() {
			if ( $( this ).text().toLowerCase() === valueLowerCase ) {
				this.selected = valid = true;
				return false;
			}
			});

			// Found a match, nothing to do
			if ( valid ) {
			return;
			}

			// Remove invalid value
			this.input
			.val( "" )
			.attr( "title", value + " didn't match any item" )
			.tooltip( "open" );
			this.element.val( "" );
			this._delay(function() {
			this.input.tooltip( "close" ).attr( "title", "" );
			}, 2500 );
			this.input.autocomplete( "instance" ).term = "";
		},

		_destroy: function() {
			this.wrapper.remove();
			this.element.show();
		}
		});
	})( jQuery );

	$(function() {
		$( ".combobox" ).combobox();
	});

	function copyToClipboard(element) {
		$(`input[name=${element}]`).select();
		document.execCommand("copy");
	}

	$('#distance_value_text, #distance_value_text_geoip, #from_location, #from_location_geoip').on('blur', function () {
		$('.ui-autocomplete').hide()
	});

</script>
