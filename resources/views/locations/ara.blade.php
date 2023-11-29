@extends('master')
@section('header')
    <span>Brand Locations - ARA Check</span>
@stop
@section('content')
    <h2>Introduction and Summary</h2>

    <div class="alert alert-info">
        This table shows all brand locations that can be used in DOOH Advertising, these locations belong to these brands:
        <br />

        <ul>
            <li> <b>ID - 1958 - DOOH Platform 161 Malls</b></li>
            <li> <b>ID - 1959 - DOOH Outdoor Auditors</b></li>
            <li> <b>ID - 1956 - DOOH Platform 161 Outdoor</b></li>
            <li> <b>ID - 1753 - Digital Out Of Home</b></li>
        </ul>
    </div>

    <div>
        <table class="display table table-striped table-bordered table-hover dataTable no-footer" cellspacing="0"
            width="100%">
            <thead>
                <tr>
                    <th>Brand Name</th>
                    <th>Brand ID</th>
                    <th>Compliant Locations</th>
                    <th>Non Compliant Locations</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Digital Out Of Home</td>
                    <td>1753</td>
                    <td>{{ $compliant_brand_locations->where('brand.id', 1753)->count() }} - locations</td>
                    <td>{{ $non_compliant_brand_locations->where('brand.id', 1753)->count() }} - locations</td>
                </tr>
                <tr>
                    <td>DOOH Platform 161 Malls</td>
                    <td>1958</td>
                    <td>{{ $compliant_brand_locations->where('brand.id', 1958)->count() }} - locations</td>
                    <td>{{ $non_compliant_brand_locations->where('brand.id', 1958)->count() }} - locations</td>
                </tr>
                <tr>
                    <td>DOOH Platform 161 Outdoor</td>
                    <td>1959</td>
                    <td>{{ $compliant_brand_locations->where('brand.id', 1959)->count() }} - locations</td>
                    <td>{{ $non_compliant_brand_locations->where('brand.id', 1959)->count() }} - locations</td>
                </tr>
                <tr>
                    <td>DOOH Platform 161 Outdoor</td>
                    <td>1956</td>
                    <td>{{ $compliant_brand_locations->where('brand.id', 1956)->count() }} - locations</td>
                    <td>{{ $non_compliant_brand_locations->where('brand.id', 1956)->count() }} - locations</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h2>Compliant Areas</h2>
    <p class="alert alert-info">
        These places are not within 500m of schools, or places of worship
    </p>
    <table class="display table table-striped table-bordered table-hover dataTable no-footer" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>BL ID</th>
                <th>Location Name</th>
                <th>Brand</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($compliant_brand_locations as $location)
                <tr>
                    <td>{{ $location->id }}</td>
                    <td>{{ $location->locationName }}</td>
                    <td>{{ $location->brand->brandName ?? $location->brandId }}</td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
    <hr class="my-4" />
    <h2>Non Compliant Areas</h2>

    <p class="alert alert-warning text-black">
        These places are within 500m of schools and places of worship, no alcohol should be served near these locations
    </p>
    <table class="display table table-striped table-bordered table-hover dataTable no-footer" cellspacing="0"
        width="100%">
        <thead>
            <tr>
                <th>BL ID</th>
                <th>Location Name</th>
                <th>Brand</th>
                <th>Nearby Places</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($non_compliant_brand_locations as $location)
                <tr>
                    <td>{{ $location->id }}</td>
                    <td>{{ $location->locationName }}</td>
                    <td>{{ $location->brand->brandName ?? $location->brandId }}</td>
                    <td>
                        <table id="{{ $location->id }}-nearbyplaces"
                            class="display table table-striped table-bordered table-hover dataTable no-footer">
                            <thead>
                                <tr>
                                    <th>Place Name</th>
                                    <th>Distance</th>
                                    <th>Types</th>
                                    <th>Status</th>
                                    <th>Lat,Long</th>
                                    <th>Vicinity</th>
                                    <th>GPlace ID</th>
                                </tr>
                            </thead>
                            @forelse ($location->ara_places as $nearbyPlace)
                                <tr>
                                    <td>{{ $nearbyPlace->place_name }}</td>
                                    <td>{{ $nearbyPlace->distance_from_brand_location }}</td>
                                    <td>{{ $nearbyPlace->types }}</td>
                                    <td>{{ $nearbyPlace->business_status }}</td>
                                    <td>{{ $nearbyPlace->latitude }},{{ $nearbyPlace->longitude }}</td>
                                    <td>{{ $nearbyPlace->vicinity }}</td>
                                    <td>{{ $nearbyPlace->place_id }}</td>
                                </tr>
                            @empty
                            @endforelse
                        </table>
                    </td>
                    {{-- <td>{{$location->}}</td> --}}
                </tr>
            @empty
            @endforelse
        <tbody>
    </table>

    {{-- //TODO add map, when brand location is selected, plot all nearby google places and zoom? --}}
@endsection
@section('script')
    <script>
        $(() => {
            $(".dataTable").DataTable({})
        })
    </script>
@endsection
