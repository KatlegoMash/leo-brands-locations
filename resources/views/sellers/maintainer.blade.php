@extends('master')
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>
@endpush

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
@endpush

@section('header')
    <span>Sellers.json</span>
@stop

@section('content')
    <div class="align-center">
            <div>
                <div class="col-lg-12 margin-tb">
                    <p class="d-inline-flex gap-1 float-right">
                        <a class="btn btn-success" href="{{url('seller/create')}}">Create New Seller</a>
                        <a class="btn btn-primary" href="{{url('download-json')}}">Download Whole .JSON File</a>
                    </p>
                </div>
            </div>
            <br>
        <form method="GET" action="{{ url('download-selected-json') }}">
            {{ csrf_field() }}
            <div class="table table-sm">
                <div class="col-auto">
                    <table id="brands" class="display table table-striped table-bordered table-hover dataTable no-footer" cellspacing="0" width="100%">
                        <tr>
                            <th scope="col">  </th>
                            <th scope="col">Seller_ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Domain</th>
                            <th scope="col">Seller Type</th>
                            <th scope="col">Is Passthrough?</th>
                            <th scope="col">Is Confidential?</th>
                            <th scope="col">Placement(s)</th>
                        </tr>
                        @foreach ($collection as $seller)
                            <tr>
                                <td><input type="checkbox" name="selectedSellers[]" value="{{ $seller->id }}"></td>
                                <td>{{ $seller->seller_id }}</td>
                                <td>{{ $seller->name}}</td>
                                <td>{{ $seller->domain}}</td>
                                <td>{{ $seller->seller_type}}</td>
                                <td>{{ $seller->is_passthrough == 1 ? 'True' : 'False' }}</td>
                                <td>{{ $seller->is_confidential == 1 ? 'True' : 'False' }}</td>
                                <td>
                                    @foreach ($seller->placements as $placement)
                                        {{ $placement->name }}
                                        <br>
                                    @endforeach
                                </td>
                                <td>
                                    <a class="btn btn-warning" href="{{ url('seller/edit', ['id' => $seller->id]) }}">Edit</a>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal{{ $seller->id }}">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
            <button type="submit" class="btn btn-warning">Download Selected JSON</button>
        </form>
    </div>
@stop
@foreach ($collection as $seller)
<!-- Delete confirmation modal -->
<div class="modal fade" id="confirmDeleteModal{{ $seller->id }}" tabindex="-1" aria-labelledby="confirmDeleteModalLabel{{ $seller->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel{{ $seller->id }}">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="text-align: center">
                <form method="POST" action="{{ url('seller/destroy', $seller->id) }}" class="delete-form">
                    {{ csrf_field() }}
                    <h3>Are you sure you want to delete<br> {{ $seller->name }}?</h3>
                    <br>
                    <button type="submit" class="btn btn-danger">Delete</button>
                    <button type="button" data-bs-dismiss="modal" aria-label="Close" class="btn btn-success">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach