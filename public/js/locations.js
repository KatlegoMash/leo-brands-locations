
//this fires on page ready
$(function() {

    showLoader(true);
    var start = moment().subtract(29, 'days');
    var end = moment();

    function cb(start, end) {
        $('#reportrange span').html(start.format('YYYY/MM/DD') + ' - ' + end.format('YYYY/MM/DD'));
    }

    $('#reportrange').daterangepicker({
        startDate: start,
        endDate: end,
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, cb);

    cb(start, end);

})

var table;

function initializeTable() {

    let locationTable = $('#newLocationTable').DataTable({
		destroy: true,
		processing: false,
		serverSide: false,
		select: {"style": 'multi'},
		order:[[3,'asc']],
        ajax: {
            "url": '/locations/advanced-location-search',
            "type": "POST",
            "data": function(d) {
                let root = nameTagsApp.$root.$data;

                let obj = {
                    brands: root.brands,
                    cities: root.cityTags,
                    suburbs: root.suburbTags,
                    provinces: root.provinceTags,
                    names: root.locationTags,
                    tags: root.labelTags,
                    dates : root.dateRange,
                    languages : root.languageTags,
                    races : root.raceTags,
                    in_market : root.inMarketTags,
                    parents: root.parents,
                    the_children: root.the_children,
                    pplHouseHold : root.pplHouseHold,
                    age : root.age,
                    income : root.income,
                    gender : root.gender

                };

                d = obj;

                return d;
            },
        },
		dom: '<lf<B><t>ip>',
		columns: [
			{ "data": "id" }, //not visible
			{ "data": "brandName" }, // 3
			{ "data": "locationName" }, // 4
            { "data": "city" }, // 5
			{ "data": "suburb" }, // 6
			{ "data": "province" }, // 7
			{ "data": "country" }, //not visible 8
			{ "data": "latitude" }, //not visible 9 
			{ "data": "longitude" }, //not visible 10
			{ "data": "maxGeofence" }, //not visible 11
			{ "data": "addressLine1" }, //not visible 12
			{ "data": "addressLine2" }, //not visible 13
			{ "data": "phone" }, //not visible 14
			{ "data": "homePage" }, //not visible 15
			{ "data": "storeName" }, //not visible 16 
			{ "data": "storeCode" }, //not visible 17
			{ "data": "postalZipCode" }, //not visible 18
			{ "data": "countryCode" }, //not visible 19
			// { "data": "phone" }, // 20
			{ "data": "locationBankId" }, //not visible 21
			{ "data": "google_place_id" }, //not visible 22
			{ "data": "categories"}, // 23
            { "data": "rating"}, // 24
            { "data": "tags"}, // 25
            { "data": null, "className": "checkbox-control","orderable": false,"defaultContent": '' },
            { "data": "visitScore"},
			{ "data": null, "className": "details-control","orderable": false, "defaultContent": '' },
    	],
        // 2, 8, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21
		columnDefs: [
			{ "visible": false, "targets": [0, 6, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 23] },
		],
		buttons: [
			{
				title: '',
				extend: 'excel',
				header: true,
				className: 'mb-1 btn btn-secondary btn-export',
				text: 'Export',
				exportOptions: {
					columns: [0, 2, 4, 9, 10, 11, 16, 17, 12, 13, 18, 5, 19, 14, 15, 21, 6, 7, 8],
					format: {
						header:  function (data, index) {
								return data.replace(/ /g,'')
						}
					}
				}
			},
			{
				text: 'Select All',
				className: 'mb-1 btn btn-secondary',
				action: function () {
					locationTable.rows( {search:'applied'} ).select();
				}
			},{
				name: 'selectNone',
				text: 'Select none',
                className: 'mb-1 btn btn-secondary',
                action: function () {
                    locationTable.rows().deselect();
                }
            },{
				name: 'retrieve',
				text: 'Retrieve Locations',
                className: 'mb-1 btn btn-secondary',
                action: function () {

                    let root = nameTagsApp.$root.$data;

                    if (root.locationTags != []) {
                        let nameFlag = false;
                        let provinceFlag = false;

                        root.locationTags.forEach(function(item) {
                            if(item.text.length < 3) {
                                nameFlag = true;
                            }
                        })

                        if (root.provinceTags.length >= 1) {
                            if (root.locationTags.length == 0 && root.brands.length == 0 && root.labelTags.length == 0) {
                                provinceFlag = true;
                            }
                        }

                        if (nameFlag == true) {
                            alert("Please only use Location Names with more than 3 characters!");
                        } else if (provinceFlag == true) {
                            alert("Please do not try retrieve locations with only the province filter. It places too much strain on the system! Use in in conjunction with other filters.");
                        } else {
                            showLoader(true);
                            $('#newLocationTable').DataTable().ajax.reload();
                        }

                    } else {
                        showLoader(true);
                        $('#newLocationTable').DataTable().ajax.reload();
                    }

                }
            },

		],
		language: {
			buttons: {
				selectAll: "Select all items",
				selectNone: "Select none"
			}
		},

		initComplete: function() {
			$("select[name='newLocationTable_length']").addClass("form-control form-control-solid")
			$("#newLocationTable_filter input").addClass("form-control search-bar form-control-solid")
			$('#newLocationTable tbody').on('click', 'td.details-control', function() {
				var tr = $(this).closest('tr');
				var row = locationTable.row( tr );
				if (row.child.isShown()) {
					row.child.hide();
					tr.removeClass('shown');
				} else {
					row.child( format(row.data()) ).show();
					tr.addClass('shown');
				}
            });
            
            $('#newLocationTable').on('xhr.dt', function(e, settings, json, xhr) {
                // This event will be triggered after the AJAX request is completed

                let cityArr = [];
                let suburbArr = [];

                json.data.forEach(function(item) {
                    if (item.city != '' && item.city != null) {
                        cityArr.push(item.city);
                    }
                    if (item.suburb != '' && item.suburb != null) {
                        suburbArr.push(item.suburb);
                    }
                });

                //I need to inject the cities and suburbs from here into the vue objects.
                cityArr = [...new Set(cityArr)];
                suburbArr = [...new Set(suburbArr)];

                for(let i = 0; i < cityArr.length; i++) {
                    cityArr[i] = {text:cityArr[i]};
                }

                for(let i = 0; i < suburbArr.length; i++) {
                    suburbArr[i] = {text:suburbArr[i]};
                }

                nameTagsApp.$root.$data.citySuggested = cityArr;
                nameTagsApp.$root.$data.suburbSuggested = suburbArr;

                nameTagsApp.$root.$data.cityHidden = true;
                nameTagsApp.$root.$data.suburbHidden = true;

                $('#secondaryHeaders').show();

            });

		}
    });

    locationTable.on( 'draw', function () {
        showLocationsOnMap(locationTable.data() ,function(){
            showLoader(false,$('#locationList'));
        });
    } );

    locationTable.on('select', function (e, dt, type, indexes) {
        showLocationsOnMap(locationTable.rows({ selected: true}).data(), function() {
            showLoader(false, $('#locationList'));
        });
    });
    locationTable.on('deselect', function (e, dt, type, indexes) {
        showLocationsOnMap(locationTable.rows({ selected: true}).data(), function() {
                showLoader(false, $('#locationList'));
        });
    });
    
    return locationTable;

}

Vue.config.devtools = true;
Vue.config.silent = true;

var nameTagsApp = new Vue({
    el: '#brandLocationsVueComponent',
    data: function() {
        return {
            returnedData: null,

            locationTag: '',
            locationPlaceholder: 'Search Location',
            locationTags: [],

            brandTag: '',
            brandPlaceholder: 'Search Brand...',
            brands: [],
            brandsSuggested: [],

            cityHidden: false,
            cityTag: '',
            cityPlaceholder: 'Search City...',
            cityTags: [],
            citySuggested: [],

            suburbHidden: false,
            suburbTag: '',
            suburbPlaceholder: 'Search Suburb...',
            suburbTags: [],
            suburbSuggested: [],

            provinceTag: '',
            provincePlaceholder: 'Search Province...',
            provinceTags: [],
            provinceTagsSuggested: [],

            labelTag: '',
            labelPlaceholder: 'Search Label...',
            labelTags: [],
            labelTagsSuggested: [],

            demographicsHidden: false,

            raceTag: '',
            racePlaceholder: 'Search Race...',
            raceTags: [],
            raceTagsSuggested: [],

            languageTag: '',
            languagePlaceholder: 'Search Language...',
            languageTags: [],
            languageTagsSuggested: [],

            inMarketTag: '',
            inMarketPlaceholder: 'Search In-Market...',
            inMarketTags: [],
            inMarketTagsSuggested: [],

            pplHouseholdDefault : [2, 5],
            pplHouseHold : [],
            pplHouseholdMin : 0,
            pplHouseholdMax : 20,

            ageDefault : [10, 30],
            age : [],
            ageMin : 0,
            ageMax : 120,

            incomeDefault : [10000, 25000],
            income : [],
            incomeMin : 0,
            incomeMax : 500000,

            parentsDefault : [1, 2],
            parents : [],
            parentsMin : 0,
            parentsMax : 2,

            childrenDefault : [1, 3],
            the_children : [],
            childrenMin : 0,
            childrenMax : 12,

            gender : '',
            
            dateFrom : '', 
            dateTo: '',
            dateRange : []

        };
    },
    methods: {
        showDemographics(){
            var toggle = $('#demographic-toggle span');
            if($(toggle).hasClass('fa-plus')){
                $(toggle).removeClass('fa-plus');
                $(toggle).addClass('fa-minus');
                this.demographicsHidden = true;
            } else {
                $(toggle).removeClass('fa-minus');
                $(toggle).addClass('fa-plus');
                this.demographicsHidden = false;
            }
        }
    },
    computed: {
        filteredBrandTags() {
            let arr = this.brandsSuggested.filter(tag => {
                if (tag.text.toLowerCase().includes(this.brandTag.toLowerCase())) {
                    return true;
                } else {
                    return false;
                }
            })
            return arr;

        },
        filteredCityTags() {
            let arr = this.citySuggested.filter(tag => {
                if (tag.text.toLowerCase().includes(this.cityTag.toLowerCase())) {
                    return true;
                } else {
                    return false;
                }
            })
            return arr;
        },
        filteredSuburbTags() {
            let arr = this.suburbSuggested.filter(tag => {
                if (tag.text.toLowerCase().includes(this.suburbTag.toLowerCase())) {
                    return true;
                } else {
                    return false;
                }
            })
            return arr;
        },
        filteredProvinceTags() {
            let arr = this.provinceTagsSuggested.filter(tag => {
                if (tag.text.toLowerCase().includes(this.provinceTag.toLowerCase())) {
                    return true;
                } else {
                    return false;
                }
            })
            return arr;
        },
        filteredLabelTags() {
            let arr = this.labelTagsSuggested.filter(tag => {
                if (tag.text.toLowerCase().includes(this.labelTag.toLowerCase())) {
                    return true;
                } else {
                    return false;
                }
            })
            return arr;
        },
        filteredRaceTags() {
            let arr = this.raceTagsSuggested.filter(tag => {
                if (tag.text.toLowerCase().includes(this.raceTag.toLowerCase())) {
                    return true;
                } else {
                    return false;
                }
            })
            return arr;
        },
        filteredLanguageTags() {
            let arr = this.languageTagsSuggested.filter(tag => {
                if (tag.text.toLowerCase().includes(this.languageTag.toLowerCase())) {
                    return true;
                } else {
                    return false;
                }
            })
            return arr;
        },
        filteredInMarketTags() {
            let arr = this.inMarketTagsSuggested.filter(tag => {
                if (tag.text.toLowerCase().includes(this.inMarketTag.toLowerCase())) {
                    return true;
                } else {
                    return false;
                }
            })
            return arr;
        },
        pplHouseHoldFormatter(){
            return this;
        }
    },
    created(){
        this.formatter = value => `￥${value}`;
    },
    beforeMount: function () {

    },
    mounted: function () {
        this.$nextTick(function () {
            // Code that will run only after the
            // entire view has been rendered

            let url = window.location.origin;
            
            //this is for suggesting brands
            let tmp = this.brandsSuggested;

            $.ajax({
                type: "GET",
                url: url+"/brands/simple-brands"
            }).done(function(results){
                results.forEach(element => {
                    if (element.brandName == "") {
                        var elem = {id:element.id, text:"none"};
                    } else {
                        var elem = {id:element.id, text:element.brandName};
                    }
                    
                    tmp.push(elem);
                });

            }).fail(function(){
                swal("Failed to retrieve Placements");
            });

            this.brandsSuggested = tmp;
            //end

            //for suggesting province
            let tmp4 = this.provinceTagsSuggested;

            $.ajax({
                type: "GET",
                url: url+"/locations/provinces"
            }).done(function(results){
                results.forEach(element => {
                    if (element.province == null || element.province == "") {
                        tmp4.push({text:"none"});
                    } else {
                        tmp4.push({text:element.province});
                    }
                    
                });

            }).fail(function(){
                swal("Failed to retrieve Placements");
            });

            this.provinceTagsSuggested = tmp4;
            //end

            //this is for suggesting tags
            let tmp5 = this.labelTagsSuggested;

            $.ajax({
                type: "GET",
                url: url+"/name-tags/location-tags2"
            }).done(function(results){
                results.forEach(element => {
                    if (element.tag == "") {
                        var elem = {id:element.tag_id, text:"none"};
                    } else {
                        var elem = {id:element.tag_id, text:element.tag};
                    }
                    
                    tmp5.push(elem);
                });

            }).fail(function(){
                swal("Failed to retrieve Labels");
            });

            this.labelTagsSuggested = tmp5;
            
            let races = [
                {
                    id : 1,
                    text : 'white'
                },
                {
                    id : 2,
                    text : 'black_african'
                },
                {
                    id : 3,
                    text : 'coloured'
                },
                {
                    id : 4,
                    text : 'indian_or_asain'
                },
                {
                    id : 5,
                    text : 'other'
                }
            ];

            this.raceTagsSuggested = races;
                        
            let languages = [
                {
                    id : 1,
                    text : 'afrikaans'
                },
                {
                    id : 2,
                    text : 'english'
                },
                {
                    id : 3,
                    text : 'isindebele'
                },
                {
                    id : 4,
                    text : 'isixhosa'
                },
                {
                    id : 5,
                    text : 'isizulu'
                },
                {
                    id : 2,
                    text : 'not_applicable'
                },
                {
                    id : 3,
                    text : 'other'
                },
                {
                    id : 4,
                    text : 'sepedi'
                },
                {
                    id : 5,
                    text : 'sesotho'
                },
                {
                    id : 5,
                    text : 'setswana'
                },
                {
                    id : 2,
                    text : 'sign_language'
                },
                {
                    id : 3,
                    text : 'siswati'
                },
                {
                    id : 4,
                    text : 'tshivenda'
                },
                {
                    id : 5,
                    text : 'xitsonga'
                }
            ];

            this.languageTagsSuggested = languages;

            let tmp6 = this.inMarketTagsSuggested;
            $.ajax({
                type: "GET",
                url: url+"/locations/content-categories/"
            }).done(function(results){
                results.forEach(element => {
                    if (element.name == "") {
                        var elem = {id:element.id, text:"none"};
                    } else {
                        var elem = {id:element.id, text:element.name};
                    }
                    tmp6.push(elem);
                });

            }).fail(function(){
                swal("Failed to retrieve Placements");
            });

            this.inMarketTagsSuggested = tmp6;
            
            populateSlider("#slider-range-household", this.pplHouseholdMin, this.pplHouseholdMax, this.pplHouseholdDefault, "#amount-household");
            populateSlider("#slider-range-age", this.ageMin, this.ageMax, this.ageDefault, "#amount-age");
            populateSlider("#slider-range-income", this.incomeMin, this.incomeMax, this.incomeDefault, "#amount-income");
            populateSlider("#slider-range-parents", this.parentsMin, this.parentsMax, this.parentsDefault, "#amount-parents");
            populateSlider("#slider-range-children", this.childrenMin, this.childrenMax, this.childrenDefault, "#amount-children");

            /* Listeners */
            $("#slider-range-household").on('slidestop', function(event, ui){
                var vueInstance = nameTagsApp;
                vueInstance.pplHouseHold = [ui.values[0], ui.values[1]];
            });
            $("#slider-range-age").on('slidestop', function(event, ui){
                var vueInstance = nameTagsApp;
                vueInstance.age = [ui.values[0], ui.values[1]];
            });
            $("#slider-range-income").on('slidestop', function(event, ui){
                var vueInstance = nameTagsApp;
                vueInstance.income = [ui.values[0], ui.values[1]];
            });
            $("#slider-range-parents").on('slidestop', function(event, ui){
                var vueInstance = nameTagsApp;
                vueInstance.parents = [ui.values[0], ui.values[1]];
            });
            $("#slider-range-children").on('slidestop', function(event, ui){
                var vueInstance = nameTagsApp;
                vueInstance.the_children = [ui.values[0], ui.values[1]];
            });
            $('#reportrange').on('apply.daterangepicker', function(ev, picker){
                var vueInstance = nameTagsApp;
                vueInstance.startDate = picker.startDate._d;
                vueInstance.endDate = picker.startDate._d;
                vueInstance.dateRange = [picker.startDate._d, picker.endDate._d];
                console.log(picker);
            });
            $('.gender .btn').on('click', function(){
                var selector = this;
                var gender = getGender(selector);
                var vueInstance = nameTagsApp;
                vueInstance.gender = gender;
            });
            
        })
    },
    watch:{
        pplHouseHold: function(newValue) {
        // When the data changes, update the jQuery UI slider
        $("#slider-range-household").slider("values", 0, newValue[0]);
        $("#slider-range-household").slider("values", 1, newValue[1]);
        $("#amount-household").val(newValue[0] + " - " + newValue[1]);
        },
    }

});

function getDate( element ) {
    var date;
    try {
      date = $.datepicker.parseDate( dateFormat, element.value );
    } catch( error ) {
      date = null;
    }

    return date;
}

function populateSlider(selector, min, max, values = [], valueSelector){
    $(selector).slider({
        range: true,
        min,
        max,
        values,
        slide: function( event, ui ) {
          $(valueSelector).val( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
        }
      });
      $(valueSelector).val($(selector).slider("values", 0) + " - " + $(selector).slider("values", 1));

}

function getGender(selector){
    /* (Reidaa is a has an IQ of 1 million - My code was doing the same, but less efficiently) */
    [selector, selector.parentNode.querySelector('.btn-primary')].forEach(function(el) {
        return el.classList.toggle('btn-primary');
    })

    return selector.name;
}

function showLoader(state, elementToShow){
	if(state){
		$('#locationList').hide();
		$('#locationPage').hide();
		$('#loader').show();
	} else {
		$('#locationList').hide();
		$('#locationPage').hide();
		$('#loader').hide();
		if(elementToShow){
			elementToShow.show();
		}
	}
}

function format (d) {
    // `d` is the original data object for the row
    return '<table class="no-check" cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">'+
	    '<tr>'+
		    '<td>Brand Name:</td>'+
		    '<td>'+d.brandName+'</td>'+
		'</tr>'+
		'<tr>'+
		    '<td>Location Name:</td>'+
		    '<td>'+d.locationName+'</td>'+
		'</tr>'+
	    '<tr>'+
		    '<td>Store Name:</td>'+
		    '<td>'+d.storeName+'</td>'+
		'</tr>'+
	    '<tr>'+
		    '<td>Store Code:</td>'+
		    '<td>'+d.storeCode+'</td>'+
		'</tr>'+
		'<tr>'+
		    '<td>Latitude:</td>'+
		    '<td>'+d.latitude+'</td>'+
		'</tr>'+
		'<tr>'+
		    '<td>Longitude:</td>'+
		    '<td>'+d.longitude+'</td>'+
		'</tr>'+
		'<tr>'+
		    '<td>MaxGeofence:</td>'+
		    '<td>'+d.maxGeofence+'</td>'+
		'</tr>'+
        '<tr>'+
            '<td>Address Line 1:</td>'+
            '<td>'+d.addressLine1+'</td>'+
        '</tr>'+
        '<tr>'+
	        '<td>Address Line 2:</td>'+
	        '<td>'+d.addressLine2+'</td>'+
	    '</tr>'+
	    '<tr>'+
	        '<td>Postal Zip Code:</td>'+
	        '<td>'+d.postalZipCode+'</td>'+
	    '</tr>'+
        '<tr>'+
            '<td>City:</td>'+
            '<td>'+d.city+'</td>'+
        '</tr>'+
        '<tr>'+
	        '<td>Country Code:</td>'+
	        '<td>'+d.countryCode+'</td>'+
	    '</tr>'+
        '<tr>'+
            '<td>Phone:</td>'+
            '<td>'+d.phone+'</td>'+
        '</tr>'+
        '<tr>'+
	        '<td>Home Page:</td>'+
	        '<td>'+d.homePage+'</td>'+
	    '</tr>'+
		'<tr>'+
	        '<td>LocationBank Id:</td>'+
	        '<td>'+d.locationBankId+'</td>'+
	    '</tr>'+
        '<tr>'+
          '<td>Categories Linked:</td>'+
          '<td>'+d.categories+'</td>'+
        '</tr>'+

        '<tr>'+
          '<td>Rating:</td>'+
          '<td>'+d.rating+'</td>'+
        '</tr>'+






    '</table>';
}


//map functions and variables.
const markers = new Map();
var markerArr = [];

var latitude = -28.8273301;
var longitude = 25.7944261;
var mapCenter = new google.maps.LatLng(latitude,longitude);

var map = new google.maps.Map(document.getElementById('map'), {
    zoom: 6,
    center: mapCenter,
    mapTypeId: google.maps.MapTypeId.ROADMAP
});

const markerCluster = new markerClusterer.MarkerClusterer({map});

function showLocationsOnMap(data, fnComplete) {

    markers.clear();
    markerArr = [];
    
    $(data).each(function(index,location){

        var infowindow = new google.maps.InfoWindow({
            content: "Address:"+location.addressLine1+'<br/>'+location.addressLine2+'<br/>'+"Store Name:"+location.storeName+'<br/>'+"Geofence:"+location.maxGeofence+'<br/>'+"City:"+location.city
        });

        latLng = new google.maps.LatLng(location.latitude,location.longitude);
        marker = new google.maps.Marker({position: latLng});

        markers.set(location.id, {location:location, marker:marker, circle:null});
        markerArr.push(marker);

        google.maps.event.addListener(marker, 'click', (function(marker) {
            return function() {
                infowindow.open(map, marker);
            }
        })(marker));

    });

    markerCluster.clearMarkers();
    markerCluster.addMarkers(markerArr);

    fnComplete = fnComplete || function() {};
	fnComplete();

}

//this function sets and unsets the geofence overlay.
function toggleGeofence() {

    markers.forEach(function(mappedItem) {

        if (mappedItem.circle == null) {
            
            var circle = new google.maps.Circle({
                strokeColor: "#66cccc",
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: "#66cccc",
                fillOpacity: 0.35,
                map: map,
                radius: parseFloat(mappedItem.location.maxGeofence),
            });
    
            circle.bindTo('center', mappedItem.marker, 'position');

            mappedItem.circle = circle;

        } else {
            mappedItem.circle.setMap(null);
            mappedItem.circle = null;;
        }

    });

}
