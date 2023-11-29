<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title></title>
    <style >
      body {
        margin: 0;
        padding: 10px 20px 20px;
        font-family: Arial;
        font-size: 16px;
      }
      #map {
        width: 1600px;
        height: 1400px;
      }
    </style>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?=Config::get('google.maps.key');?>"></script>
    <script src="{!!asset('js/vendor/google/markerclusterer.js')!!}"></script>
    <script>
    
      function initialize() {
        var center = new google.maps.LatLng(-28.8273301,25.7944261);
        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 6,
          center: center,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        });
        var markers = [];
        @foreach ($locations as $location)
        	var latLng = new google.maps.LatLng({!!$location->latitude!!},{!!$location->longitude!!});
          	var marker = new google.maps.Marker({position: latLng});
            markers.push(marker);
            var circle = new google.maps.Circle({
          	  map: map,
          	  radius: 5000,    // x miles in metres 
          	  fillColor: '#AA0000'
          	});
          	circle.bindTo('center', marker, 'position');
    	@endforeach
        var markerCluster = new MarkerClusterer(map, markers);
      }
      google.maps.event.addDomListener(window, 'load', initialize);
    </script>
  </head>
  <body>
    <div id="map-container"><div id="map"></div></div>
  </body>
</html>