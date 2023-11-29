var map;
var editableLayers;
(function() {
  var CartoDB_Voyager = L.tileLayer(
    "https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png",
    {
      attribution: `Vicninity Media - ${new Date().getFullYear()}`,
      subdomains: "abcd",
      maxZoom: 19
    }
  );
  $(".leaflet-control-attribution ").html(
    `Vicninity Media - ${new Date().getFullYear()}`
  );

  map = L.map("map", {
    zoomControl: true,
    drawControl: true,
    measureControl: true,
    layers: [CartoDB_Voyager]
  }).setView([-33.970697997361626, 18.468017578125004], 5);

  /**
   * Leaflet Draw
   */
  editableLayers = new L.FeatureGroup();
  map.addLayer(editableLayers);

  var options = {
    position: "bottomleft",
    draw: {
      polyline: {
        shapeOptions: {
          color: "#f357a1",
          weight: 10
        }
      },
      polygon: {
        showArea: true,
        showLength: true,
        allowIntersection: false, // Restricts shapes to simple polygons
        drawError: {
          color: "#e1e100", // Color the shape will turn when intersects
          message: "<strong>Oh snap!<strong> you can't draw that!" // Message that will show when intersect
        },
        shapeOptions: {
          color: "#bada55"
        }
      },
      circlemarker: false,
      circle: {
        showRadius: true
      }, // Turns off this drawing tool
      rectangle: {
        shapeOptions: {
          clickable: true
        }
      },
      marker: {
        repeatMode: true
        // icon: new MyCustomMa rker()
      }
    },
    edit: {
      featureGroup: editableLayers, //REQUIRED!!
      remove: true
    }
  };

  var drawControl = new L.Control.Draw(options);
  map.addControl(drawControl);

  // L.control.ruler().addTo(map);
  var ruleroptions = {
    position: "bottomleft",
    lengthUnit: {
      display: "km", // This is the display value will be shown on the screen. Example: 'meters'
      decimal: 2, // Distance result will be fixed to this value.
      factor: null, // This value will be used to convert from kilometers. Example: 1000 (from kilometers to meters)
      label: "Distance:"
    }
  };

  // var ruler = L.control.ruler(ruleroptions).addTo(map);

  // L.control.scale({ position: "bottomleft" }).addTo(map);

  var measureControl = new L.Control.Measure({
    position: "bottomleft",
    primaryLengthUnit: "meters",
    secondaryLengthUnit: "kilometers",
    primaryAreaUnit: "sqmeters",
    secondaryAreaUnit: "hectares",
    popupOptions: {
      className: "leaflet-measure-resultpopup",
      autoPanPadding: [10, 10]
    },
    captureZIndex: 1000000
  });
  measureControl.addTo(map);

  map.on("measurefinish", function(evt) {
    console.log("measurefinish");

    console.log(evt);
  });

  map.on("measurestart", function(evt) {
    console.log("measurestart");

    console.log(evt);
  });

  /**
   * End Leaflet Draw
   */

  var plyylineDraw = {
    position: "topleft", // Position to show the control. Values: 'topright', 'topleft', 'bottomright', 'bottomleft'
    unit: "metres", // Show imperial or metric distances. Values: 'metres', 'landmiles', 'nauticalmiles'
    clearMeasurementsOnStop: true, // Clear all the measurements when the control is unselected
    showBearings: false, // Whether bearings are displayed within the tooltips
    bearingTextIn: "In", // language dependend label for inbound bearings
    bearingTextOut: "Out", // language dependend label for outbound bearings
    tooltipTextFinish: "Click to <b>finish line</b><br>",
    tooltipTextDelete: "Press SHIFT-key and click to <b>delete point</b>",
    tooltipTextMove: "Click and drag to <b>move point</b><br>",
    tooltipTextResume: "<br>Press CTRL-key and click to <b>resume line</b>",
    tooltipTextAdd: "Press CTRL-key and click to <b>add point</b>",
    // language dependend labels for point's tooltips
    measureControlTitleOn: "Turn on PolylineMeasure", // Title for the control going to be switched on
    measureControlTitleOff: "Turn off PolylineMeasure", // Title for the control going to be switched off
    measureControlLabel: "&#8614;", // Label of the Measure control (maybe a unicode symbol)
    measureControlClasses: [], // Classes to apply to the Measure control
    showClearControl: true, // Show a control to clear all the measurements
    clearControlTitle: "Clear Measurements", // Title text to show on the clear measurements control button
    clearControlLabel: "&times", // Label of the Clear control (maybe a unicode symbol)
    clearControlClasses: [], // Classes to apply to clear control button
    showUnitControl: true, // Show a control to change the units of measurements
    distanceShowSameUnit: false, // Keep same unit in tooltips in case of distance less then 1 km/mi/nm
    unitControlTitle: {
      // Title texts to show on the Unit Control button
      text: "Change Units",
      metres: "metres",
      landmiles: "land miles",
      nauticalmiles: "nautical miles"
    },
    unitControlLabel: {
      // Unit symbols to show in the Unit Control button and measurement labels
      metres: "m",
      kilometres: "km",
      feet: "ft",
      landmiles: "mi",
      nauticalmiles: "nm"
    },
    tempLine: {
      // Styling settings for the temporary dashed line
      color: "#00f", // Dashed line color
      weight: 2 // Dashed line weight
    },
    fixedLine: {
      // Styling for the solid line
      color: "#006", // Solid line color
      weight: 2 // Solid line weight
    },
    startCircle: {
      // Style settings for circle marker indicating the starting point of the polyline
      color: "#000", // Color of the border of the circle
      weight: 1, // Weight of the circle
      fillColor: "#0f0", // Fill color of the circle
      fillOpacity: 1, // Fill opacity of the circle
      radius: 3 // Radius of the circle
    },
    intermedCircle: {
      // Style settings for all circle markers between startCircle and endCircle
      color: "#000", // Color of the border of the circle
      weight: 1, // Weight of the circle
      fillColor: "#ff0", // Fill color of the circle
      fillOpacity: 1, // Fill opacity of the circle
      radius: 3 // Radius of the circle
    },
    currentCircle: {
      // Style settings for circle marker indicating the latest point of the polyline during drawing a line
      color: "#000", // Color of the border of the circle
      weight: 1, // Weight of the circle
      fillColor: "#f0f", // Fill color of the circle
      fillOpacity: 1, // Fill opacity of the circle
      radius: 3 // Radius of the circle
    },
    endCircle: {
      // Style settings for circle marker indicating the last point of the polyline
      color: "#000", // Color of the border of the circle
      weight: 1, // Weight of the circle
      fillColor: "#f00", // Fill color of the circle
      fillOpacity: 1, // Fill opacity of the circle
      radius: 3 // Radius of the circle
    }
  };
  //
  // L.control.polylineMeasure(plyylineDraw).addTo(map);

  var styleMutant = L.gridLayer.googleMutant({
    styles: [
      { elementType: "geometry", stylers: [{ color: "#242f3e" }] },
      { elementType: "labels.text.stroke", stylers: [{ color: "#242f3e" }] },
      { elementType: "labels.text.fill", stylers: [{ color: "#746855" }] },
      {
        featureType: "administrative.locality",
        elementType: "labels.text.fill",
        stylers: [{ color: "#d59563" }]
      },
      {
        featureType: "poi",
        elementType: "labels.text.fill",
        stylers: [{ color: "#d59563" }]
      },
      {
        featureType: "poi.park",
        elementType: "geometry",
        stylers: [{ color: "#263c3f" }]
      },
      {
        featureType: "poi.park",
        elementType: "labels.text.fill",
        stylers: [{ color: "#6b9a76" }]
      },
      {
        featureType: "road",
        elementType: "geometry",
        stylers: [{ color: "#38414e" }]
      },
      {
        featureType: "road",
        elementType: "geometry.stroke",
        stylers: [{ color: "#212a37" }]
      },
      {
        featureType: "road",
        elementType: "labels.text.fill",
        stylers: [{ color: "#9ca5b3" }]
      },
      {
        featureType: "road.highway",
        elementType: "geometry",
        stylers: [{ color: "#746855" }]
      },
      {
        featureType: "road.highway",
        elementType: "geometry.stroke",
        stylers: [{ color: "#1f2835" }]
      },
      {
        featureType: "road.highway",
        elementType: "labels.text.fill",
        stylers: [{ color: "#f3d19c" }]
      },
      {
        featureType: "transit",
        elementType: "geometry",
        stylers: [{ color: "#2f3948" }]
      },
      {
        featureType: "transit.station",
        elementType: "labels.text.fill",
        stylers: [{ color: "#d59563" }]
      },
      {
        featureType: "water",
        elementType: "geometry",
        stylers: [{ color: "#17263c" }]
      },
      {
        featureType: "water",
        elementType: "labels.text.fill",
        stylers: [{ color: "#515c6d" }]
      },
      {
        featureType: "water",
        elementType: "labels.text.stroke",
        stylers: [{ color: "#17263c" }]
      }
    ],
    maxZoom: 24,
    type: "roadmap"
  });

  var roadMutant = L.gridLayer.googleMutant({
    maxZoom: 24,
    type: "roadmap"
  });

  var satMutant = L.gridLayer.googleMutant({
    maxZoom: 24,
    type: "satellite"
  });

  var terrainMutant = L.gridLayer.googleMutant({
    maxZoom: 24,
    type: "terrain"
  });

  var hybridMutant = L.gridLayer.googleMutant({
    maxZoom: 24,
    type: "hybrid"
  });

  var trafficMutant = L.gridLayer.googleMutant({
    maxZoom: 24,
    type: "roadmap"
  });
  trafficMutant.addGoogleLayer("TrafficLayer");

  var transitMutant = L.gridLayer.googleMutant({
    maxZoom: 24,
    type: "roadmap"
  });

  transitMutant.addGoogleLayer("TransitLayer");

  var CartoDB_Positron = L.tileLayer(
    "https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png",
    {
      attribution:
        '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
      subdomains: "abcd",
      maxZoom: 19
    }
  );

  var Stamen_TonerLite = L.tileLayer(
    "https://stamen-tiles-{s}.a.ssl.fastly.net/toner-lite/{z}/{x}/{y}{r}.{ext}",
    {
      attribution:
        'Map tiles by <a href="http://stamen.com">Stamen Design</a>, <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a> &mdash; Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
      subdomains: "abcd",
      minZoom: 0,
      maxZoom: 20,
      ext: "png"
    }
  );

  L.control
    .layers(
      {
        "<img src='/img/leaflet/screenshots/colour_bg.png' style='margin-right: 10px;' width=100 height=70>Colour (Default)": CartoDB_Voyager,
        "<img src='/img/leaflet/screenshots/night_bg.png' style='margin-right: 10px;' width=100 height=70>NightMode": styleMutant,
        "<img src='/img/leaflet/screenshots/roads_bg.png' style='margin-right: 10px;' width=100 height=70>Google POI": roadMutant,
        "<img src='/img/leaflet/screenshots/hybrid_bg.png' style='margin-right: 10px;' width=100 height=70>Satellite": hybridMutant,
        "<img src='/img/leaflet/screenshots/traffic_bg.png' style='margin-right: 10px;' width=100 height=70>Real-Time  Traffic": trafficMutant,
        "<img src='/img/leaflet/screenshots/transit_bg_1.png' style='margin-right: 10px;' width=100 height=70>Public Transport": transitMutant,
        "<img src='/img/leaflet/screenshots/plain_bg.png' style='margin-right: 10px;' width=100 height=70>Plain": CartoDB_Positron,
        "<img src='/img/leaflet/screenshots/bandw_bg.png' style='margin-right: 10px;' width=100 height=70>B&W": Stamen_TonerLite
      },
      {},
      {
        position: "topleft",
        collapsed: true
      }
    )
    .addTo(map);
})();
