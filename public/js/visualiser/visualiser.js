
var defaultColors = [
  "#2F80E7",
  "#2B957A",
  "#564AA3",
  "#CC0E00",
  "#E4BB01",
  "#6AFFFF",
  "#E58628"
];

Vue.use(VueMaterial.default);
Vue.component("vue-ctk-date-time-picker", window["vue-ctk-date-time-picker"]);
Vue.component("verte", Verte);
Vue.use(Verte, {
  recentColors: defaultColors
});
Vue.component("v-select", VueSelect.VueSelect);
Vue.component("vue-multi-select", window["vue-multi-select"].default);
Vue.component("vue-multiselect", window.VueMultiselect.default);

var visualiser = new Vue({
  el: "#visualiser_panel",
  created() {
    this.$on("update:mdActive", payload => {
      console.log(payload);
    });
  },
  data() {
    return {
      selectCampaignOptions: {
        btnLabel: "Select campaign(s) ",
        values: [],
        name: "first group",
        filters: [
          {
            nameAll: "Select all campaigns",
            nameNotAll: "Deselect all",
            func() {
              return true;
            }
          }
        ],
        options: {
          labelName: "campaignName",
          renderTemplate(elem) {
            return `${elem.campaignName}`;
          },
          multi: true,
          groups: false
        }
      },
      selectBrandLocationsOptions: {
        btnLabel: "filtered brand locations(s) ",
        values: [],
        name: "first group",
        filters: [
          {
            nameAll: "Select all location",
            nameNotAll: "Deselect all",
            func() {
              return true;
            }
          }
        ],
        options: {
          labelName: "locationName",
          renderTemplate(elem) {
            return `${elem.locationName}`;
          },
          multi: true,
          groups: false
        }
      },
      selectPlacementOptions: {
        btnLabel: "Select placements(s) ",
        name: "first group",
        values: [],
        filters: [
          {
            nameAll: "Select all placements",
            nameNotAll: "Deselect all",
            func() {
              return true;
            }
          }
        ],
        options: {
          renderTemplate(elem) {
            return `${elem.name}`;
          },
          multi: true,
          groups: false
        }
      },
      selectBrandOptions: {
        btnLabel: "Select brands(s) ",
        name: "first group",

        values: [],
        filters: [
          {
            nameAll: "Select all brands",
            nameNotAll: "Deselect all",
            func() {
              return true;
            }
          }
        ],
        options: {
          labelName: "brandName",
          renderTemplate(elem) {
            return `${elem.brandName}`;
          },
          multi: true,
          groups: false
        }
      },
      metricValidationMessage:
        "For Ad Calls, select both Impressions and Passbacks",
      value: [],
      defaultColors: defaultColors,
      showDateRanges: false,
      showTable: false,
      showSnackbar: false,
      pointsLoading: false,
      hexBinOptions: {
        radius: 12,
        opacity: 0.9,
        duration: 200,
        colorDomain: null,
        radiusDomain: null,
        radiusRange: [10, 10],
        colorRange: [
          "#c3d9f7",
          "#75acf9",
          "#478ae8",
          "#266ed3",
          "#135dc4",
          "#033282",
          "#012560"
        ],
        pointerEvents: "all"
      },
      defaultDate: moment()
        .subtract(1, "days")
        .format("YYYY-MM-DD"),
      map: null,
      google: {},
      centerPointGroup: [
        {
          layerId: 0,
          group: {}
        }
      ],
      url: "http://{s}.tile.osm.org/{z}/{x}/{y}.png",
      zoom: 12,
      center: [-34.007426, 18.461995],
      bounds: null,
      expandSingle: true,
      showItems: true,
      campaignsList: [],
      placementsList: [],
      brandsList: [],
      initialLocation: [-34.007426, 18.461995],
      wheel: null,
      campaigns: [],
      placements: [],
      locationTypes: [
        { id: "device", name: "Fine" },
        { id: "shared", name: "Shared" },
        { id: "wifi_micro", name: "Wifi Micro" },
        { id: "wifi_small", name: "Wifi Small" },
        { id: "wifi_public", name: "Wifi Public" }
      ],
      layerData: [],
      hiddenLayerData: [],
      showNavigation: true,
      showSidepanel: false,
      brandLoc: "",
      brandLocationList: [],
      activeLayer: {},
      showPopup: false
    };
  },
  mounted() {
    this.getFieldData();
    this.defaultDate = moment()
      // .subtract(1, "days")
      .format("YYYY-MM-DD");
    this.google = google;
    this.loadDynamicZoom(google);

    setTimeout(() => {
      this.map = map;
      $("#menu-toggle").click();
    }, 3000);

    setTimeout(() => {
      new L.Control.GPlaceAutocomplete({
        callback: place => {
          var loc = place.geometry.location;

          var layer = this.getActiveLayer();

          layer.selectionType = "googlePOI";
          this.moveGeofenceTo(layer, loc.lat(), loc.lng());
        }
      }).addTo(map);

      map.on("draw:drawstart", e => {
        var layer = this.getActiveLayer();

        layer.isDrawing = true;
        layer.selectionType = "use-coords";
      });

      map.on("draw:editstart", e => {
        var layer = this.getActiveLayer();

        layer.isDrawing = true;
        layer.selectionType = "use-coords";
      });

      map.on("draw:editstop	", e => {
        var layer = this.getActiveLayer();
        layer.selectionType = "use-coords";

        layer.isDrawing = false;
      });

      map.on("draw:deletestart", e => {
        var layer = this.getActiveLayer();

        layer.isDrawing = true;
        layer.selectionType = "use-coords";
      });

      map.on("draw:deletestop	", e => {
        var layer = this.getActiveLayer();
        layer.selectionType = "use-coords";

        layer.isDrawing = false;
      });

      map.on("draw:drawstop	", e => {
        var layer = this.getActiveLayer();
        layer.selectionType = "use-coords";

        layer.isDrawing = false;
      });

      map.on(L.Draw.Event.CREATED, e => {
        var type = e.layerType,
          layer = e.layer;

        if (type === "marker") {
          layer.bindPopup("To remove markers click on it");
          layer.on("mouseover", e => {
            layer.openPopup();
          });
          layer.on("mouseout", e => {
            layer.closePopup();
          });

          layer.on("click", e => {
            editableLayers.removeLayer(layer);
          });
        }

        if (this.activeLayerGroup.selectionType != "use-coords") {
          swal(
            "Please use the Select on map layer selection type to use the draw feature"
          );
          return false;
        }
        editableLayers.addLayer(layer);
      });
    }, 3000);

    setTimeout(() => {
      this.addNewLayer();
      this.addCloseButtons();
    }, 4000);
  },

  watch: {
    "activeLayerGroup.dateFromAndto": function(val, OldLayer) {
      var activeLayer = this.getActiveLayer();
      if(typeof activeLayer == "undefined"){
          return false;
      }
      if (
        moment(activeLayer.dateFromAndto.end).isSameOrAfter(
          this.defaultDate,
          "day"
        )
      ) {
        if (activeLayer.dateFromAndto.shortcut == "-day") {
          return false;
        }

        if (moment(activeLayer.dateFromAndto.start).date() === 1) {
          // If date is the first of this month, make it a the first of last month
          activeLayer.dateFromAndto.start = moment(
            activeLayer.dateFromAndto.start
          )
            .subtract(1, "months")
            .format("YYYY-MM-DD");
          //What would be the llast day of this current month now becomes yesterday.
          activeLayer.dateFromAndto.end = moment(activeLayer.dateFromAndto.end)
            .subtract(1, "months")
            .format("YYYY-MM-DD");
          activeLayer.dateFromAndto.end = moment(activeLayer.dateFromAndto.end)
            .add(1, "days")
            .format("YYYY-MM-DD");
        } else {
          activeLayer.dateFromAndto.end = this.defaultDate;
        }
      }
    },
    "activeLayerGroup.brands": function(val, OldLayer) {
      if (OldLayer != this.activeLayerGroup.brands) {
        this.getBrandLocations(val);
      }
    },
    "activeLayerGroup.brandLocations": function(val, OldLayer) {
      this.setPOICircles(val);
    },

    "activeLayerGroup.campaigns": function(val, OldLayer) {
      var activeLayer = this.getActiveLayer();

      if (!this.validateQuery(activeLayer)) {
        activeLayer.validator.selectedSelectionTypes.isValid = false;
      }
      var camps = val.map(x => x.campaignId);

      // var shortcut = activeLayer.datePickerCustomShortCutsLayer.find(
      //   sc => sc.key == "customValue"
      // );

      axios.post("/map/campaign-start-date", { camps }).then(response => {
        daystart = moment().diff(response.data.startDate, "days");
        daysend = moment().diff(response.data.endDate, "days");

        if (daystart || daysend) {
          activeLayer.campaignStartDate = daystart;
          activeLayer.campaignEndDate = daysend;
        }
      });
    },
    "activeLayerGroup.colour": function(layer, OldLayer) {
      var activeLayer = this.getActiveLayer();
      this.setActiveGeofence(activeLayer);
    },
    "activeLayerGroup.selectionType": function(layer, OldLayer) {
      var activeLayer = this.getActiveLayer();
      if (OldLayer == "vicinityPOI") {
        setTimeout(() => {
          for (const key in activeLayer.POIFences) {
            if (activeLayer.POIFences.hasOwnProperty(key)) {
              const element = activeLayer.POIFences[key];
              this.map.removeLayer(element.circle);
            }
          }
          activeLayer.POIFences = [];
          this.setActiveGeofence(activeLayer);
        }, 2000);
        return;
      }
      this.setActiveGeofence(activeLayer);

      if (layer == "vicinityPOI" && OldLayer !== "vicinityPOI") {
        if (activeLayer.brands.length) {
          this.getBrandLocations(activeLayer.brands);
        }
        if (layer == "vicinityPOI") {
          setTimeout(() => {
            $(".close-content-catergories").html("");
            $(".bs-searchbox").prepend(
              `<div onclick="closeSelectpicker()" class="close-content-catergories"   style="margin: 0 0 5px;  cursor: pointer; float: right"><i class="md-icon md-icon-font md-theme-default">close</i></div>`
            );
            $(".line").prepend(
              `<div onclick="closeSelectMulti()" class="close-content-catergories"   style="cursor: pointer; float: right"><i class="md-icon md-icon-font md-theme-default">close</i></div>`
            );
          }, 3000);
        }
      }
    },
    "activeLayerGroup.inmarketSegments": function(layer, OldLayer) {
      var activeLayer = this.getActiveLayer();
      if (layer == "inmarketSegments") {
        activeLayer.inMarketSegemntsLoading = true;
        activeLayer.selectedSelectionTypes = ["Clicks"];
        setTimeout(() => {
          $(".selectpicker").selectpicker();
          activeLayer.inMarketSegemntsLoading = false;
          $(".in-market-dropdown, .bs-searchbox").prepend(
            `<div onclick="closeSelectpicker()"
            class="close-content-catergories"
             style="margin: 0 0 5px;
             cursor: pointer; float: right">
             <i class="md-icon md-icon-font md-theme-default">
             close
             </i>
             </div>`
          );
        }, 2000);
      }
    },
    "activeLayerGroup.name": function(layer, OldLayer) {
      var activeLayer = this.getActiveLayer();
      this.setActiveGeofence(activeLayer);
    },
    activeLayer: function(layer, OldLayer) {
      var layertest = this.customStringify(layer);
      var layertestold = this.customStringify(OldLayer);

      if (layertest !== layertestold) {
      }
      var activeLayer = this.getActiveLayer();

      this.zooomToBounds(activeLayer);
    },

    activeLayerGroup: function(layer, OldLayer) {
      activeLayer = this.getActiveLayer();
      this.zooomToBounds(activeLayer);
    },
    activeGeofence: function(val, OldLayer) {
      var activeLayer = this.getActiveLayer();

      if (activeLayer.selectionType == "vicinityPOI") {
        return;
      }

      this.setActiveGeofence(activeLayer);
      this.zooomToBounds(activeLayer);
    },
    "layer.selectedSelectionTypes": function(layer, OldLayer) {
      var activeLayer = this.getActiveLayer();

      this.validateQuery(activeLayer);
    }
  },
  computed: {
    getCenterPointCoords() {
      if (this.map) {
        var bounds = this.map.getBounds();
        return bounds.getCenter();
      }
    },
    isDev() {
      if (window.location.href == "https://leo.vic-m.co/map/map-new") {
        return false;
      }
      return true;
    },
    shouldShowGeofenceField() {
      return (
        this.activeLayerGroup.selectionType != "selectAll" &&
        this.activeLayerGroup.selectionType != "vicinityPOI"
      );
    },
    selectedCampaigns() {
      const activeLayer = this.getActiveLayer();
      const campaignIds = activeLayer.campaigns;
      if (campaignIds.length) {
        return campaignIds.map(x => x.campaignId);
      }
    },
    selectedBrands() {
      const activeLayer = this.getActiveLayer();
      const brandsIds = activeLayer.brands;
      if (brandsIds.length) {
        return brandsIds.map(x => x.id);
      }
    },
    selectedBrandLocations() {
      const activeLayer = this.getActiveLayer();
      const brandsIds = activeLayer.brandLocations;
      if (brandsIds.length) {
        return brandsIds.map(x => x.locationName);
      }
    },
    selectedPlacements() {
      const activeLayer = this.getActiveLayer();
      const placementIds = activeLayer.placements;
      if (placementIds.length) {
        return placementIds.map(x => x.zoneId);
      }
    },
    activeLayerGroup() {
      if (this.layerData.length > 0) {
        return this.getActiveLayer();
      } else {
        return this.defaultLayer;
      }
    },
    activeTab() {
      var activeLayer = this.getActiveLayer();

      return `tab-${activeLayer.id}`;
    },
    activeGeofence() {
      fenceSize = this.activeLayerGroup.geofence;
      this.activeLayerGroup.isMetre = this.activeLayerGroup.isMetre * 1;

      if (this.activeLayerGroup.isMetre) {
        fenceSize = this.activeLayerGroup.geofence;
      } else {
        fenceSize = this.activeLayerGroup.geofence * 1000;
      }

      return fenceSize;
    },
    defaultLayer() {
      const latestID = this.layerData.length + 1;

      var setNewColor = this.defaultColors[latestID - 1];
      return {
        active: true,
        /*
         * hexBinLayer
         */
        hexBinData: [],
        hexLayer: L.hexbinLayer(this.hexBinOptions),
        /*
         * hexBinLayer
         */
        pointsLayerGroup: L.layerGroup(),
        pushToMap: false,

        /*
         * Group Layer parent
         */
        trackplaybackPoints: {
          type: "FeatureCollection",
          features: []
        },

        geofenceLayer: {},
        selectionType: "use-coords",
        hideColourPicker: false,
        selectPointsFromTable: false,
        sliderControl: {},
        layerGroup: L.layerGroup(),
        markerClusterGroup: {},
        setCenterPoint: true,
        pointsToPull: [],
        id: latestID,
        name: `Layer ${latestID}`,
        geofence: 500,
        lat: -34.007414,
        lng: 18.462242,
        locationTypes: ["device"],
        campaigns: [],
        placements: [],
        colour: setNewColor,

        /**
         *  Date Stuff
         */
        dateFromAndto: {},
        dateFrom: this.defaultDate,
        dateTo: this.defaultDate,

        expanded: true,
        markers: [],
        isMetre: true,
        ipAddress: [],
        vicinityID: [],
        visible: true,
        points: [],
        pointsHolder: [],
        checkInterval: null,
        centerPoint: {},
        addMarkers: false,
        loading: false,
        selectedDateRange: this.defaultDate,
        oldCircle: {},
        pushTomMapSelection: false,
        tablePoints: [],
        tableSummary: [],
        clearTablePoints: false,
        selectionTypes: ["Clicks", "Impressions", "Passbacks", "Engagements"],
        selectedSelectionTypes: ["Impressions", "Passbacks"],
        brandLocations: [],
        brandLocationsList: [],

        POIFences: [],
        brands: [],
        filteredBrands: [],
        filterBrandLocations: [],
        deleted: false,
        isDrawing: false,
        map_type: "cluster",
        validator: {
          selectedSelectionTypes: {
            isValid: true,
            message: ""
          }
        },
        campaignStartDate: null,
        campaignEndDate: null,
        inMarketSegemntsLoading: false,
        inmarketSegments: false,

        datePickerCustomShortCutsLayer: [
          {
            key: "label1",
            label: "Yesterday",
            value: "-day",
            isSelected: true
          },
          {
            key: "customValue",
            label: "Start To Finish",
            value: (start2, finish2) => {
              var activeLayer = this.getActiveLayer();

              if (activeLayer.campaigns.length == 0) {
                swal(
                  'Please select at least one campaign to use shortcut "Start to finish"'
                );
                return false;
              }

              return {
                start: moment().subtract(activeLayer.campaignStartDate, "days"),
                end: moment().subtract(activeLayer.campaignEndDate, "days")
              };
            },
            callback: ({ start, end }) => {}
          },
          {
            key: "last7Days",
            label: "Last 7 days",
            value: 7,
            isSelected: false
          },
          {
            key: "label6",
            label: "Last Week",
            value: "-isoWeek",
            isSelected: false
          },
          {
            key: "label3",
            label: "Last 30 Days",
            value: 30,
            isSelected: false
          },
          {
            key: "label4",
            label: "This Month",
            value: "month",
            isSelected: false
          },
          {
            key: "label5",
            label: "Last Month",
            value: "-month",
            isSelected: false
          },
          { key: "thisYear", label: "This year", value: "year" },
          { key: "lastYear", label: "Last year", value: "-year" }
        ],
        customSelect: {
          start: moment()
            .subtract(3000, "days")
            .startOf("day"),
          end: moment()
            .subtract(1, "days")
            .endOf("day")
        }
      };
    }
  },
  methods: {
    openSelectPicker(type) {
      switch (type) {
        case "category":
          $("#category-picker").selectpicker("toggle");
          break;
        case "interest":
          $("#interest-picker").selectpicker("toggle");
        default:
          break;
      }
    },
    closePopup() {
      $("#md_toolbar").click();
    },
    addCloseButtons() {
      setTimeout(() => {
        $(".header-picker").prepend(
          `<i onclick="closeSelectMulti()" class="md-icon md-icon-font md-theme-default" style="float: right; cursor: pointer; margin: 0px 11px 10px 0; " >close</i>`
        );
        $(".line").prepend(
          `<div onclick="closeSelectMulti()"  class="close-content-catergories"  style="cursor: pointer; float: right"><i class="md-icon md-icon-font md-theme-default">close</i></div>`
        );

        $(".bs-searchbox").prepend(
          `<div onclick="closeSelectpicker()" class="close-content-catergories"   style="margin: 0 0 5px;  cursor: pointer; float: right"><i class="md-icon md-icon-font md-theme-default">close</i></div>`
        );
      }, 5000);
    },
    metricClick(log) {
      console.log(log);
      console.log(this.$listeners);
    },
    customStringify(v) {
      const cache = new Set();
      return JSON.stringify(v, function(key, value) {
        if (typeof value === "object" && value !== null) {
          if (cache.has(value)) {
            // Circular reference found
            try {
              // If this value does not reference a parent it can be deduped
              return JSON.parse(JSON.stringify(value));
            } catch (err) {
              // discard key if value cannot be deduped
              return;
            }
          }
          // Store value in our set
          cache.add(value);
        }
        return value;
        layerToShow;
      });
    },
    hideLayer(activeLayer) {
      if (!activeLayer.visible) {
        var layerToShow = this.hiddenLayerData.find(
          layer => layer.id == activeLayer.id
        );
        if (layerToShow.id == "undefined") {
          activeLayer.loading = false;
          this.pointsLoading = false;
          return false;
        }
        this.layerData[layerToShow.id - 1] = layerToShow.layer;

        activeLayer.visible = true;
        activeLayer.geofence = activeLayer.geofence - 1;

        var newlayertoshow = Object.assign({}, layerToShow);

        setTimeout(() => {
          var activeLayer2 = this.getActiveLayer();
          for (let q = 0; q < newlayertoshow.points.length; q++) {
            const element = newlayertoshow.points[q];

            if (!element.latitude && !element.longitude) continue;
            this.addMarkerToLayer(activeLayer2, element);
          }
          this.toggleLayerType(activeLayer2.map_type, activeLayer2.id);

          activeLayer.loading = false;
          this.pointsLoading = false;
        }, 2000);
        this.hiddenLayerData.splice(newlayertoshow.id - 1, 1);

        setTimeout(() => {
          activeLayer.geofence = activeLayer.geofence + 1;
        }, 2000);
      } else {
        //if visible
        setTimeout(() => {
          if (activeLayer.selectionType == "vicinityPOI") {
            for (const key in activeLayer.POIFences) {
              if (activeLayer.POIFences.hasOwnProperty(key)) {
                const element = activeLayer.POIFences[key];
                this.map.removeLayer(element.circle);
              }
            }
            activeLayer.POIFences = [];
          }
        }, 1000);

        this.hiddenLayerData.push({
          id: activeLayer.id,
          layer: activeLayer,
          points: activeLayer.points
        });

        activeLayer.visible = false;
        this.deleteLayer(activeLayer);
        activeLayer.loading = false;
        this.pointsLoading = false;
      }
    },
    deleteLayer(activeLayer) {
      var id = activeLayer.id;
      if (activeLayer.visible != false) {
        var previousLayer = this.layerData.find(layer => layer.id == id - 1);

        if (typeof previousLayer == "undefined") {
          swal(
            "Cannot remove first layer. The visualiser needs atleast one layer set. "
          );
          return false;
        }
      }

      this.removeLayerPoints();

      var layerToRemove = this.getActiveLayer();
      layerToRemove.loading = false;

      if (activeLayer.visible != false) {
        layerToRemove.loading = true;
        setTimeout(() => {
          this.$refs["layerid1"][0].click();
          layerToRemove.loading = false;
          layerToRemove.deleted = true;
        }, 3000);
      }
      layerToRemove.loading = false;
    },
    setFilteredBrands(event) {
      const activeLayer = this.getActiveLayer();

      if (
        event == "" &&
        this.$refs[String("brandFilter" + activeLayer.id)][0].isOpen == false &&
        activeLayer.filteredBrands.length == 0
      ) {
        activeLayer.filteredBrands = this.brandsList;

        return true;
      }
      if (event !== "") {
        activeLayer.filteredBrands = this.$refs[
          String("brandFilter" + activeLayer.id)
        ][0].filteredOptions;
      }
    },
    setFilteredBrandLocations(event) {
      if (event !== "") {
        const activeLayer = this.getActiveLayer();
        activeLayer.filterBrandLocations = this.$refs[
          String("brandLocationFilter" + activeLayer.id)
        ][0].filteredOptions;
      }
    },
    getBrandLocations(selectedBrands) {
      const activeLayer = this.getActiveLayer();
      if(typeof activeLayer !== "undefined"){
          $.ajax({
            url: "/map/filter-brand-locations",
            type: "POST",
            data: { term: selectedBrands },
            success: response => {
              activeLayer.brandLocationsList = response.data;
            }
          });

      }
    },
    toggleShowDateRange() {
      this.showDateRanges = !this.showDateRanges;
    },
    addBrandGeofence() {
      const activeLayer = this.getActiveLayer();

      var brandIds = $(`.selectpicker-${activeLayer.id}`).val();

      activeLayer;

      activeLayer.brandLocations = [];
      for (const key in brandIds) {
        if (brandIds.hasOwnProperty(key)) {
          const element = brandIds[key];

          var matched = activeLayer.brandLocationsList.find(
            location => location.id == element
          );
          activeLayer.brandLocations.push(matched);
        }
      }

      this.setPOICircles(activeLayer.brandLocations);
    },
    moveGeofenceTo(activeLayer, lat, lng) {
      var val = {
        latlng: {
          lat: lat,
          lng: lng
        }
      };

      this.getMapCoords(val);
    },
    arrayRemove(arr, value) {
      return arr.filter(function(ele) {
        return ele != value;
      });
    },
    addSelectionTypes(layer, type) {
      if (layer.selectedSelectionTypes.includes(type)) {
        layer.selectedSelectionTypes = this.arrayRemove(
          layer.selectedSelectionTypes,
          type
        );
      } else {
        layer.selectedSelectionTypes.push(type);
      }
    },

    toggleTableDisplay() {
      this.showTable = !this.showTable;
    },
    toggleMenu() {
      this.showNavigation = !this.showNavigation;
    },
    setActiveGeofence(activeLayer) {
      if (activeLayer.layerGroup.hasLayer(activeLayer.geofenceLayer)) {
        activeLayer.layerGroup.removeLayer(activeLayer.geofenceLayer);
      }

      if (
        activeLayer.selectionType == "selectAll" ||
        activeLayer.selectionType == "vicinityPOI"
      ) {
        return false;
      }

      var layer = {};
      layer.layerId = activeLayer.id;
      layer.group = L.layerGroup();
      var icon = L.divIcon({
        iconSize: null,
        draggable: true,
        html: `<div style="" class="map-label redbackground">
                  <div style="border-color: ${activeLayer.colour}" class="map-label-content">${activeLayer.name}</div>
                  <div style="border-color: ${activeLayer.colour} transparent transparent transparent;" class="map-label-arrow"></div>
               </div>`
      });

      L.marker([activeLayer.lat, activeLayer.lng], {
        icon: icon,
        draggable: false
      }).addTo(layer.group);

      var icon = L.divIcon({
        iconSize: null,
        draggable: true,
        html: `<div style="" class="map-label redbackground">
                  <div style="border-color: ${activeLayer.colour}" class="map-label-content">${activeLayer.name}</div>
                  <div style="border-color: ${activeLayer.colour} transparent transparent transparent;" class="map-label-arrow"></div>
               </div>`
      });

      activeLayer.geofenceLayer = L.layerGroup();

      L.marker([activeLayer.lat, activeLayer.lng], {
        icon: icon,
        draggable: false
      }).addTo(activeLayer.geofenceLayer);

      L.circle([activeLayer.lat, activeLayer.lng], {
        radius: this.activeGeofence,
        color: activeLayer.colour
      }).addTo(activeLayer.geofenceLayer);

      activeLayer.layerGroup.addLayer(activeLayer.geofenceLayer);
      this.map.addLayer(activeLayer.layerGroup);
    },
    loadDynamicZoom(google) {
      setTimeout(() => {
        map.on("click", a => {
          this.getMapCoords(a);
        });
      }, 2000);

      Number.prototype.toRad = function() {
        return (this * Math.PI) / 180;
      };

      Number.prototype.toDeg = function() {
        return (this * 180) / Math.PI;
      };

      google.maps.LatLng.prototype.destinationPoint = function(brng, dist) {
        dist = dist / 6371;
        brng = brng.toRad();

        var lat1 = this.lat().toRad(),
          lon1 = this.lng().toRad();

        var lat2 = Math.asin(
          Math.sin(lat1) * Math.cos(dist) +
            Math.cos(lat1) * Math.sin(dist) * Math.cos(brng)
        );

        var lon2 =
          lon1 +
          Math.atan2(
            Math.sin(brng) * Math.sin(dist) * Math.cos(lat1),
            Math.cos(dist) - Math.sin(lat1) * Math.sin(lat2)
          );

        if (isNaN(lat2) || isNaN(lon2)) return null;

        return new google.maps.LatLng(lat2.toDeg(), lon2.toDeg());
      };
      this.google = google;
    },
    zooomToBounds(activeLayer) {
        if(typeof activeLayer !== "undefined"){
            var pointA = new google.maps.LatLng(activeLayer.lat, activeLayer.lng);

            activeLayer.isMetre = this.activeLayerGroup.isMetre * 1;

            if (activeLayer.isMetre) {
              fenceSize = activeLayer.geofence;
            } else {
              fenceSize = activeLayer.geofence * 1000;
            }
            if (
              activeLayer.selectionType == "selectAll" ||
              activeLayer.selectionType == "vicinityPOI"
            ) {
              return false;
            }

            var pointTop = pointA.destinationPoint(0, fenceSize / 1000);
            var pointBottom = pointA.destinationPoint(180, fenceSize / 1000);

            var centerpoint = this.centerPointGroup.filter(
              layer => layer.layerId == activeLayer.id
            );

            if (centerpoint.length > 0) {
              for (let index = 0; index < centerpoint.length; index++) {
                const element = centerpoint[index];
                this.map.removeLayer(element.group);
                delete centerpoint[index];
              }
            }
            var layer = {};
            layer.layerId = activeLayer.id;
            layer.group = L.layerGroup();
            var icon = L.divIcon({
              iconSize: null,
              draggable: true,
              html: `<div style="" class="map-label redbackground">
                        <div style="border-color: ${activeLayer.colour}" class="map-label-content">${activeLayer.name}</div>
                        <div style="border-color: ${activeLayer.colour} transparent transparent transparent;" class="map-label-arrow"></div>
                     </div>`
            });

            L.marker([activeLayer.lat, activeLayer.lng], {
              icon: icon,
              draggable: false
            }).addTo(layer.group);

            this.centerPointGroup[activeLayer.id] = layer;

            if (activeLayer.selectionType == "googlePOI") {
              if (typeof event == "undefined") {
                this.map.flyToBounds([
                  [pointTop.lat(), pointTop.lng()],
                  [pointBottom.lat(), pointBottom.lng()]
                ]);
              } else {
                this.map.fitBounds([
                  [pointTop.lat(), pointTop.lng()],
                  [pointBottom.lat(), pointBottom.lng()]
                ]);
              }
            } else {
              this.map.fitBounds([
                [pointTop.lat(), pointTop.lng()],
                [pointBottom.lat(), pointBottom.lng()]
              ]);
            }
        }

    },
    toggleLayerType(type, id) {
      const activeLayer = this.getActiveLayer();
      activeLayer.map_type = type;

      if (activeLayer.selectionType !== "vicinityPOI") {
        activeLayer.layerGroup.clearLayers();
        this.removeLayerPoints();
      }

      activeLayer.layerGroup.clearLayers();
      this.removeLayerPoints();

      if (activeLayer.selectionType !== "selectAll") {
        this.addCircle(activeLayer);
      }

      switch (type) {
        case "cluster":
          activeLayer.layerGroup.addLayer(activeLayer.markerClusterGroup);
          break;
        case "points":
          if (activeLayer.tablePoints.length >= 1000) {
            swal(
              "Too many results to display points, please use Cluster or Heatmap"
            );
            return false;
          }
          activeLayer.layerGroup.addLayer(activeLayer.pointsLayerGroup);
          break;
        case "heatmap":
          activeLayer.hexLayer.data(activeLayer.hexBinData);
          activeLayer.layerGroup.addLayer(activeLayer.hexLayer);
          activeLayer.hexLayer.redraw();
          this.map.setZoom(this.map.getZoom() + 1);
          setTimeout(() => {
            this.map.setZoom(this.map.getZoom() - 1);
          }, 800);

        default:
          break;
      }
      this.map.addLayer(activeLayer.layerGroup);
    },
    addNewLayer() {
      for (const key in this.layerData) {
        if (this.layerData.hasOwnProperty(key)) {
          const element = this.layerData[key];
          element.expanded = false;
        }
      }

      var newLayer = this.defaultLayer;
      newLayer.expanded = true;

      if (this.layerData.length) {
        var index = this.layerData.length - 1;
        newLayer.lat = this.layerData[index].lat;
        newLayer.lng = this.layerData[index].lng;
        newLayer.selectedSelectionTypes = this.layerData[
          index
        ].selectedSelectionTypes;
        newLayer.geofence = this.layerData[index].geofence;
        newLayer.isMetre = this.layerData[index].isMetre;
        newLayer.selectionType = this.layerData[index].selectionType;
        newLayer.ipAddress = this.layerData[index].ipAddress;
        newLayer.locationTypes = this.layerData[index].locationTypes;
        newLayer.campaigns = this.layerData[index].campaigns;
        newLayer.placements = this.layerData[index].placements;
        newLayer.selectedDateRange = this.layerData[index].selectedDateRange;
        newLayer.brands = this.layerData[index].brands;
        newLayer.brandLocations = this.layerData[index].brandLocations;
      }
      this.layerData.push(newLayer);

      setTimeout(() => {
        var activeLayer = this.getActiveLayer();
        activeLayer.brandLocations = newLayer.brandLocations;
        activeLayer.brands = newLayer.brands;
        this.getBrandLocations(activeLayer.brands);
      }, 2000);
      setTimeout(() => {
        var activeLayer = this.getActiveLayer();
        activeLayer.showDate = true;

        $(`.selectpicker-${activeLayer.id}`).selectpicker(
          "val",
          $(`.selectpicker-${activeLayer.id - 1}`).val()
        );
      }, 5000);
    },
    getActiveLayer() {
      var activeLayer = this.layerData.find(layer => layer.expanded == true);
      return activeLayer;
    },
    toggleExpandedLayer(layer) {
      layer.expanded = false;
    },
    removeLayerPoints() {
      const activeLayer = this.getActiveLayer();

      activeLayer.points = [];
      activeLayer.layerGroup.removeLayer(activeLayer.markerClusterGroup);
      activeLayer.layerGroup.removeLayer(activeLayer.pointsLayerGroup);
      activeLayer.layerGroup.removeLayer(activeLayer.hexLayer);
      activeLayer.layerGroup.removeLayer(activeLayer.layerGroup);
      activeLayer.layerGroup.clearLayers();
      this.map.removeLayer(activeLayer.oldCircle);

      for (const key in activeLayer.POIFences) {
        if (activeLayer.POIFences.hasOwnProperty(key)) {
          const element = activeLayer.POIFences[key];
          this.map.removeLayer(element.circle);
          // delete activeLayer.POIFences[key];
        }
      }
      activeLayer.POIFences = [];

      $(".hexbin-container").empty();
    },
    removeTablePoints() {
      const activeLayer = this.getActiveLayer();
      activeLayer.tablePoints = [];
    },
    setPOICircles(locations) {
      // this.map.setZoom(5);
      const activeLayer = this.getActiveLayer();
      if(typeof activeLayer !== "undefined"){
          for (const key in activeLayer.POIFences) {
            if (activeLayer.POIFences.hasOwnProperty(key)) {
              const element = activeLayer.POIFences[key];
              this.map.removeLayer(element.circle);
            }
          }
          activeLayer.POIFences = [];

          for (const key in locations) {
            if (locations.hasOwnProperty(key)) {
              const element = locations[key];

              const myCustomColour = activeLayer.colour;
              const markerHtmlStyles = `
                background-color: ${myCustomColour};
                width: 3rem;
                height: 3rem;
                display: block;
                left: -1.5rem;
                top: -1.5rem;
                position: relative;
                border-radius: 3rem 3rem 0;
                transform: rotate(45deg);
                border: 1px solid #FFFFFF`;

              const icon = L.divIcon({
                className: "my-custom-pin",
                iconAnchor: [0, 24],
                labelAnchor: [-6, 0],
                popupAnchor: [0, -36],
                html: `<span style="${markerHtmlStyles}" />`
              });

              var label = L.divIcon({
                iconSize: null,
                draggable: true,
                html: `<div style="" class="map-label redbackground">
                      <div style="border-color: ${activeLayer.colour}" class="map-label-content">${element.locationName}</div>
                      <div style="border-color: ${activeLayer.colour} transparent transparent transparent;" class="map-label-arrow"></div>
                   </div>`
              });

              activeLayer.isMetre = this.activeLayerGroup.isMetre * 1;

              if (activeLayer.isMetre) {
                fenceSize = activeLayer.geofence;
              } else {
                fenceSize = activeLayer.geofence * 1000;
              }

              var circle = L.circle([element.latitude, element.longitude], {
                radius: fenceSize,
                color: activeLayer.colour,
                id: element.id
              }).bindPopup(
                `<div> BrandLocation: <br> <strong>    ${element.locationName} </strong> </div>`
              );

              circle.on({
                mouseover: function() {
                  this.openPopup();
                },
                mouseout: function() {
                  this.closePopup();
                },
                click: function() {
                  this.openPopup();
                }
              });

              layer = {
                id: element.id,
                circle: circle
              };

              this.map.addLayer(layer.circle);
              activeLayer.POIFences.push(layer);
            }
          }
      }

    },
    getPoints() {
      const activeLayer = this.getActiveLayer();

      if (activeLayer.selectionType != "vicinityPOI") {
        this.removeLayerPoints();
      }

      if (!this.validateQuery(activeLayer)) {
        swal(activeLayer.validator.selectedSelectionTypes.message);
        return false;
      }

      if (activeLayer.selectionType !== "selectAll") {
        this.addCircle(activeLayer);
      }

      activeLayer.loading = true;
      this.pointsLoading = true;
      activeLayer.setCenterPoint = false;

      if (activeLayer.campaigns == "") {
        activeLayer.campaigns = [];
      }

      if (activeLayer.inmarketSegments == "inmarketSegments") {
        var selectedInMarketSegments = $("#category-picker").selectpicker(
          "val"
        );

        var selectedInMarketInterest = $("#interest-picker").selectpicker(
          "val"
        );
      }

      var filters = {
        campaignIds: this.selectedCampaigns,
        zoneIds: this.selectedPlacements,
        brandLocations: this.selectedBrandLocations,
        brandId: this.selectedBrands,
        type: activeLayer.locationTypes,
        ipAddress: activeLayer.ipAddress,
        vicinityID: activeLayer.vicinityID,
        inmarketSegments: selectedInMarketSegments,
        inmarketSegmentsInterest: selectedInMarketInterest
      };

      var data = {
        filters,
        selectAll: false,
        layerTypes: activeLayer.selectedSelectionTypes
      };

      if (activeLayer.selectionType == "vicinityPOI") {
        var poiLatLngs = [];
        for (const key in activeLayer.brandLocations) {
          if (activeLayer.brandLocations.hasOwnProperty(key)) {
            const element = activeLayer.brandLocations[key];
            poiLatLngs.push({
              lat: element.latitude,
              lng: element.longitude
            });
          }
        }

        data.brandLocations = poiLatLngs;
      }

      if (activeLayer.selectionType == "selectAll") {
        data.selectAll = true;
      }

      if (activeLayer.selectionType !== "selectAll") {
        data.geofence = this.activeGeofence;
        data.isMetre = String(activeLayer.isMetre);
        data.latitude = activeLayer.lat;
        data.longitude = activeLayer.lng;
      }

      if (activeLayer.selectionType == "vicinityPOI") {
        delete data.latitude;
        delete data.longitude;
      }

      if (typeof activeLayer.dateFromAndto.start == "undefined") {
        data.startDate = this.defaultDate;
        data.endDate = this.defaultDate;
      } else {
        data.startDate = activeLayer.dateFromAndto.start;
        data.endDate = activeLayer.dateFromAndto.end;
      }

      activeLayer.markers = [];
      activeLayer.hideColourPicker = true;

      activeLayer.layerGroup = L.layerGroup();
      activeLayer.markerClusterGroup = L.markerClusterGroup({
        spiderfyOnMaxZoom: true
      });
      getVisits(this.selectedBrands);
      axios
        .post("/map/all-data-points", data)
        .then(response => {
          var statusCode = response.data.status;
          activeLayer.points = response.data.points;

          if (response.data.server_busy) {
            swal(
              `Please run again in a while, ${response.data.server_busy} is querying at the moment`
            );
            return false;
          }

          if (statusCode !== 200) {
            activeLayer.loading = false;
            this.pointsLoading = false;

            swal(
              `Error: The server returned response code 500 ${JSON.stringify(
                response
              )}`
            );
            return false;
          }

          if (response.data.points.length == 0) {
            swal(`There is no data for this query`);
            return false;
          }
          if (response.data.points.length > 0) {
            var locations = [];
            for (let q = 0; q < response.data.points.length; q++) {
              const element = response.data.points[q];
              if (!element.latitude && !element.longitude) continue;
              this.addMarkerToLayer(activeLayer, element);
            }
            this.toggleLayerType(activeLayer.map_type, activeLayer.id);
            activeLayer.loading = false;
            this.pointsLoading = false;

            var columns = [
                {id: 'locations',title: 'Locations'},
                {id: 'vists',title: 'Visits'},
            ];
            displayVisitData("#inmarket-overlap",locations,columns);

          }
          activeLayer.points = response.data.points;
          activeLayer.tablePoints = response.data.points;

          summaryData = response.data.summary;
          activeLayer.tableSummary = response.data.summary;
        })
        .catch(error => {
          // Leaving this console log here for debug purposes.
          // As agreed by @Mdu

          console.log(error);

          activeLayer.loading = false;
          this.pointsLoading = false;
          swal(`There was an error with the query can you try again. ${error}`);
        })
        .finally(() => {
          activeLayer.loading = false;
          this.pointsLoading = false;
        });
    },
    addMarkerToLayer(activeLayer, pointInfo) {
      const myCustomColour = activeLayer.colour;

      const markerHtmlStyles = `
                  background-color: ${myCustomColour};
                  width: 3rem;
                  height: 3rem;
                  display: block;
                  left: -1.5rem;
                  top: -1.5rem;
                  position: relative;
                  border-radius: 3rem 3rem 0;
                  transform: rotate(45deg);
                  border: 1px solid #FFFFFF`;

      const icon = L.divIcon({
        className: `${pointInfo.timestamp}`,
        iconAnchor: [0, 24],
        labelAnchor: [-6, 0],
        popupAnchor: [0, -36],
        html: `<span style="${markerHtmlStyles}" />`
      });

      let info = `
            <strong> Location Type: </strong> ${pointInfo.location_type} <br>
            <strong> Campaign:  </strong>${pointInfo.campaignId} <br>
            <strong> Placement:  </strong>${pointInfo.zoneId} <br>
            <strong> Date Time: </strong> ${pointInfo.date_time} <br>
            <strong> Layer Type: </strong> ${pointInfo.layer_type} <br>
            <div title="Add UserID to filter" style="height: 42px;">
            <strong> User ID: </strong> ${pointInfo.vicinity_id}
              <span style="margin-top: 10px; cursor: pointer" onclick="pushToUserID('${pointInfo.vicinity_id}')">
              <i class="fa fa-clipboard fa-2x"></i>
            </span> <br>
            </div>
          `;

      var marker = L.marker([pointInfo.latitude, pointInfo.longitude], {
        icon: icon,
        info: info,
        draggable: false
      });

      marker.bindPopup(info, { maxWidth: 560 });

      activeLayer.pointsLayerGroup.addLayer(marker);
      activeLayer.hexBinData.push([pointInfo.longitude, pointInfo.latitude]);
      activeLayer.markerClusterGroup.addLayer(marker);
    },
    getCampaigns() {
      for (const key in this.campaignsList) {
        if (this.campaignsList.hasOwnProperty(key)) {
          this.campaigns.push({
            campaignName: this.campaignsList[key].campaignName,
            campaignId: this.campaignsList[key].campaignId
          });
        }
      }
    },
    addCircle(activeLayer) {
      const myCustomColour = activeLayer.colour;

      const markerHtmlStyles = `
        background-color: ${myCustomColour};
        width: 3rem;
        height: 3rem;
        display: block;
        left: -1.5rem;
        top: -1.5rem;
        position: relative;
        border-radius: 3rem 3rem 0;
        transform: rotate(45deg);
        border: 1px solid #FFFFFF`;

      const icon = L.divIcon({
        className: "my-custom-pin",
        iconAnchor: [0, 24],
        labelAnchor: [-6, 0],
        popupAnchor: [0, -36],
        html: `<span style="${markerHtmlStyles}" />`
      });

      activeLayer.oldCircle = L.circle([activeLayer.lat, activeLayer.lng], {
        radius: this.activeGeofence,
        color: activeLayer.colour
      });

      this.map.addLayer(activeLayer.oldCircle);
    },
    displayMetricValidationMessage() {
      this.metricValidationMessage =
        "For Ad Calls, select both Impressions and Passbacks";

      setTimeout(() => {
        this.metricValidationMessage = "";
      }, 5000);
    },
    validateQuery(activeLayer) {
        if(typeof activeLayer !== "undefined"){
            if (activeLayer.selectedSelectionTypes.length == 0) {
              swal("Please choose atleast one Layer Type");
              return false;
            }

        }

      return true;
    },
    toggleExpandedLayers(layer) {
      for (const key in this.layerData) {
        if (this.layerData.hasOwnProperty(key)) {
          const element = this.layerData[key];
          element.expanded = false;
        }
      }

      layer.expanded = true;
    },
    getMapCoords(val) {
      var activeLayer = this.getActiveLayer();
      const myCustomColour = activeLayer.colour;

      if (activeLayer.isDrawing) {
        return false;
      }

      if (activeLayer.selectionType == "addMarker") {
        var marker = L.marker([val.latlng.lat, val.latlng.lng], {
          draggable: false
        })
          .addTo(this.map)
          .on("click", function(e) {
            map.removeLayer(marker);
          });
      }

      if (
        activeLayer.selectionType == "use-coords" ||
        activeLayer.selectionType == "googlePOI"
      ) {
        if (activeLayer.tablePoints.length) {
          swal("All your points will be lost. \nCreate a new layer or cancel", {
            buttons: {
              catch: {
                text: "Add New Layer",
                value: "catch"
              },
              cancel: "Cancel"
            }
          }).then(value => {
            switch (value) {
              case "catch":
                this.addNewLayer();
                swal("Success", "Added new layer", "success");
                break;
            }
          });
          return false;
        }
        this.removeLayerPoints();
        activeLayer.lat = val.latlng.lat;
        activeLayer.lng = val.latlng.lng;

        var circle = L.circle([activeLayer.lat, activeLayer.lng], {
          radius: activeLayer.geofence,
          color: activeLayer.colour
        });
        this.setActiveGeofence(activeLayer);
        this.map.addLayer(activeLayer.layerGroup);
        this.zooomToBounds(activeLayer);
      }

      if (activeLayer.selectionType == "selectPointsFromMap") {
      }
    },
    getFieldData() {
      axios.get("/map/fields").then(response => {
        this.campaignsList = response.data.campaigns;
        this.placementsList = response.data.placements;
        this.brandsList = response.data.brands;
        this.getCampaigns();
      });
    }
  }
});
