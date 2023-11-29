@extends('master')
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
@endpush
@push('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
@endpush
@section('header')
    <span>Add Entry</span>
@stop
@section('content')
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-left">
                <h2>Add a Seller</h2>
            </div>
            <div class="pull-right">
                <a class="btn btn-success" href="{{url('seller/index')}}">Back</a>
            </div>
        </div>
    </div>


    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p>{{ $message }}</p>
        </div>
    @endif

    {!! Form::open(array('url' => 'seller/store','method'=>'POST')) !!}
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Seller ID:</strong>
                    {!! Form::text('seller_id', null, array('placeholder' => 'Enter Seller ID','class' => 'form-control')) !!}
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Name:</strong>
                    {!! Form::text('name', null, array('placeholder' => 'Enter Name','class' => 'form-control')) !!}
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Domain:</strong>
                    {!! Form::text('domain', null, array('placeholder' => 'Enter Domain','class' => 'form-control')) !!}
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Select Placements:</strong>
                    <br>
                    <select class="selectpicker" name="placements[]" multiple>
                        @foreach($placements as $placement)
                            <option value="{{ $placement->id }}">{{ $placement->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Seller Type:</strong>
                    {!! Form::select('seller_type', $seller_type, null, ['placeholder' => 'Select Seller Type', 'class' => 'form-control','id' => 'seller_type_select', 'onchange' => 'removePlaceholderOptionType()']) !!}
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>is Passthrough:</strong>
                    {!! Form::select('is_passthrough', [
                        '1' => 'True',
                        '0' => 'False',
                    ], null, ['placeholder' => 'Select True or False', 'class' => 'form-control','id' => 'is_passthrough_select', 'onchange' => 'removePlaceholderOptionIP()']) !!}
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>is Confidential:</strong>
                    {!! Form::select('is_confidential', [
                        '1' => 'True',
                        '0' => 'False',
                    ], null, ['placeholder' => 'Select True or False', 'class' => 'form-control','id' => 'is_confidential_select', 'onchange' => 'removePlaceholderOptionIC()']) !!}
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Comment:</strong>
                    {!! Form::textarea('comment', null, array('placeholder' => 'Enter Comment','class' => 'form-control')) !!}
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </div>
    {!! Form::close() !!}
    <script>
        function removePlaceholderOptionIC() {
            var selectElement = document.getElementById('is_confidential_select');
            selectElement.remove(0); // Remove the first option (index 0)
            selectElement.onchange = null; // Remove the onchange event listener
        }
        function removePlaceholderOptionIP() {
            var selectElement = document.getElementById('is_passthrough_select');
            selectElement.remove(0); // Remove the first option (index 0)
            selectElement.onchange = null; // Remove the onchange event listener
        }
        function removePlaceholderOptionType() {
            var selectElement = document.getElementById('seller_type_select');
            selectElement.remove(0); // Remove the first option (index 0)
            selectElement.onchange = null; // Remove the onchange event listener
        }
    </script>
@stop






