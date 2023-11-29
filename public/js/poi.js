var drawn = false;
var table;
var url = '/poi-demographics/';
var mapCanvas;
var  json = [];
var colors = [];
var PropVal ='';
var lastDrawn;
var idArrs = [];
var multiple = false;
$(document).ready(function() {
  $.ajax({
    type: 'GET',
    url: url+'placemarks',
    success: function(resp) {
      populateSelect(resp);
    }
  });

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  $('#submitDemographics').click(function() {
    $('#submitDemographics button span').removeClass('fa-upload');
    $('#submitDemographics button span').addClass('fa-spinner');
    $('#submitDemographics button span').addClass('fa-spin');

    if(kmlFileData.length < 151){
      $.ajax({
        type: 'POST',
        url: url + "poi-demographics",
        data: {
          locationData: kmlFileData
        },
        success: function(response) {
          responseMessageModal(response);
          $('#submitDemographics button span').addClass('fa-upload');
          $('#submitDemographics button span').removeClass('fa-spinner');
          $('#submitDemographics button span').removeClass('fa-spin');
        }
      });
    }else{
      responseMessageModal('tooMany');
    }
  });

  document.getElementById('poiDemographicsFile').addEventListener('change', handleFileSelect, false);
});

var selectedSearch = [];

function getPlacemarks(){
  $.ajax({
    type: 'GET',
    url: url + 'placemarks',
    success: function(resp) {
      $("#placemarkSelector").find('option').remove();
      populateSelect(resp);
    }
  });
}

function populateSelect(response){
  $.each(response,function(i, e){
    var o = new Option(e.placemark, e.placemark);
    $(o).html(e.placemark);
    $("#placemarkSelector").append(o);
    $('#placemarkSelector').selectpicker('refresh');
  });
  $('.filter-option.pull-left').text('Province');
}

function findClicked(){
  var args =[];

  $('.search-container .dropdown-menu').children('li').find('a[aria-selected=true] > span.text').each(function(e, span){
   txt = $(span).text();
    args.push(txt);
  }); 
  placemarkCall(args); 
}

function placemarkCall(args) {
  $('.placemark-toggle').removeClass('hidden');
  $('.placemark-toggle').removeClass('fa-times');
  $('.placemark-toggle').addClass('fa-spinner');
  $('.placemark-toggle').addClass('fa-spin');

  if (args.length == 0){
    drawSuburbs('');
    $('.placemark-toggle').addClass('fa-times hidden');
    $('.placemark-toggle').removeClass('fa-spinner');
    $('.placemark-toggle').removeClass('fa-spin');
  }else{
    setTimeout(function() {
      $.ajax({
        type: 'GET',
        url: url  +"poi-demographics/" + args.join(),
        success: function(response) {
          drawSuburbs(response);
          lastDrawn = response;
        }
      });
    }, 300);
  }
}

function drawSuburbs(resp){
  if (drawn == true || resp == ''){
    table.clear().draw();
    table.rows.add(resp);
    table.columns.adjust().draw(); 
    $('.placemark-toggle').addClass('fa-times hidden');
    $('.placemark-toggle').removeClass('fa-spinner');
    $('.placemark-toggle').removeClass('fa-spin');
    innit();
  }
  else{
    table = $('#poidemo').DataTable({
      paging: true,
      searching: true,
      ordering:  true,
      select: true,
      data: resp,
      "deferRender": true,
      language: {  search: "" },
      "createdRow": function( row, data) {
          $(row).addClass(data[0]);
      },
      select: {
        style: 'multi',
        // style:    'os',
        selector: 'td:first-child'
      },
      order: [[ 1, 'asc' ]],
      columns: [
          {
            data: null,
            orderable: false,
            defaultContent: '<input type="checkbox">'
          },
          { 
            data: 'placemark',
            className: 'text-capitalize'
          },
          { 
            data: 'suburb',
            className: 'text-capitalize'
          },
          {
            data: null,
            className: "dt-center editor-edit ",
            defaultContent: '<i class="fa fa-eye"/>',
            orderable: false,
            "width": "10%"
          },
          {
            data: null,
            className: "dt-center editor-delete ",
            defaultContent: '<i class="fa fa-trash"/>',
            orderable: false
          },
          {
            data: null,
            className: "dt-center editor-list ",
            defaultContent: '<i class="fa fa-ellipsis-v"/>',
            orderable: false,
            "width": "10%"
          },
          { 
            data: 'map_styles',
            className: "hidden" 
          },
          {  
            data: 'coordinates',
            className: "hidden" 
           },
          { 
            data: 'id',
            className: "hidden" 
           },
           {
            data: 'categories',
            className:"hidden"
           }
      ],
      "columnDefs": [
        { "width": "10%", "targets": 0 }
      ],
      rowCallback: function (row, data) {
            $(row).addClass(data);
      }
    });
    $('#poidemo').removeClass('hidden');
    table.draw();
    $('.placemark-toggle').addClass('fa-times hidden');
    $('.placemark-toggle').removeClass('fa-spinner');
    $('.placemark-toggle').removeClass('fa-spin');

    innit();

    drawn = true;
  }
}

function innit() {
  $("#poidemo").off('click');
  var placemark_trigger = 'td:nth-child(5)';
  removePlacemark(placemark_trigger);

  var viewSelector = 'td:nth-child(4)';
  triggerMap(viewSelector);

  var targSelector = 'td:nth-child(6)';
  triggerPopup(targSelector);

  var editSelector = 'td:first-child'
  multipleEdits(editSelector);

  $('#select-all').removeClass('hidden');
}

function selectAll(){
  if ($('#select-all p').text() == 'Select All'){
    $('#select-all p').text('Deselect All');
    $('#poidemo tr td:first-child input:not(:checked)').click();
  }else{
    $('#select-all p').text('Select All');
    $('#poidemo tr td:first-child input:checked').click();
  }
}

function bulkEdit(){
  $('#poidemo tr.selected').children('td:nth-child(6)').last().click();
}

function multipleEdits(editSelector){
  $("#poidemo " + editSelector).delegate("input", "change",function(e){
    var boolcheck = true;
    if ($(this).prop('checked') !== true){
      idArrs.splice(parseInt($.inArray($(this).parent('td').parent('tr').children('td:nth-last-child(2)').text(), idArrs)),1);
      boolcheck = false;
    }else{
      idArrs.push([$(this).parent('td').parent('tr').children('td:nth-last-child(2)').text(),$(this).parent('td').parent('tr').children('td:nth-child(3)').text()]);
    }
    $(this).prop('checked', boolcheck);
    if($("#poidemo input:checked").length > 1){
      multiple = true;
      $('#bulk-edit').removeClass('hidden');
    }
    else{
      $('#bulk-edit').addClass('hidden');
    }
  });
}

function removePlacemark(placemark_trigger) {
  $("#poidemo").delegate(placemark_trigger, "click",function(e){
    $(this).children("i").removeClass('fa-trash');
    $(this).children("i").addClass('fa-spinner');
    $(this).children("i").addClass('fa-spin');
    var txt = '';
    txt = $(this).parent('tr').children('td:nth-last-child(2)').text();
    deleteType(txt);
  });
}

function triggerMap(viewSelector){
  $("#poidemo").delegate(viewSelector, "click",function(e){
    $(this).children('i').removeClass('fa-eye');
    $(this).children('i').addClass('fa-spinner');
    $(this).children('i').addClass('fa-spin');
    var txt = '';
    var color = $(this).parent('tr').children('td:nth-last-child(4)').text();
    txt = $(this).parent('tr').children('td:nth-last-child(3)').text();
    var argsArr = txt.split("|");
    var argsObj =[];
    $.each(argsArr, function(i, t){
      argsObj.push([parseFloat(t.split(',')[0]), parseFloat(t.split(',')[1])]);
    });
    drawMap(argsObj, color);
    $(this).children('i').removeClass('fa-spinner');
    $(this).children('i').removeClass('fa-spin');
    $(this).children('i').addClass('hidden');
  });
}

function triggerPopup(targSelector){
  $("#poidemo").delegate(targSelector, "click",function(e){
   getCategoriesAndAttributes(this);
    $('#response-message-wrapper').removeClass('modal');
    $('#response-message-wrapper').removeClass('fade');
    $('#response-message-modal').removeClass('hide');
    $('#response-title').empty();
    $('.propVal').empty();
    $('.propVal').removeClass('hidden');
    PropVal = $(this).parent('tr').children('td:nth-last-child(2)').text();
    if (multiple == true){
      var listOfSuburbs = '';
      $(idArrs).each(function(index,val){
        listOfSuburbs += val[1];
        if((index+1) < idArrs.length){
          listOfSuburbs +=  ', ';
        }
      });
      $('#response-title').append(listOfSuburbs + ' Categories');
    }
    else{
      idArrs = [];
      $('#response-title').append($(this).parent('tr').children('td:nth-child(3)').text() + ' Categories');
    }
    
    $('#response-message').append('<i class="fa fa-spinner fa-spin" style="margin-left: calc(50% - 20px);"></i>');
    $('#property-value').val($(this).parent('tr').children('td:nth-child(2)').text());
    $('#response-message').empty();
  
    $('#close-modal').click(function() {
      selectAll();
      idArrs = [];
      multiple = false;
      $('#response-message').empty();
      $('.propVal').addClass('hidden');
      $('#response-message-wrapper').addClass('modal');
      $('#response-message-wrapper').addClass('fade');
      $('#response-message-modal').addClass('hide');
      if($('#bulk-edit').hasClass('hidden') == false){
        $('#bulk-edit').addClass('hidden');
      }
      
      if(drawn == true){
        drawSuburbs(lastDrawn);
      }
    });
  });
}

function drawMap(mapdata, color) {

  // Creating a map
  var mapDiv = document.getElementById("map");
  mapCanvas = new google.maps.Map(mapDiv, {
    mapTypeId : google.maps.MapTypeId.ROADMAP
  });

  // Generate bunch of path data
  var sw = new google.maps.LatLng(-37.99617113, 17.71874785);
  var ne = new google.maps.LatLng(-24.52714458, 33.18749785);
  var bounds = new google.maps.LatLngBounds(sw, ne);
  mapCanvas.setCenter(bounds.getCenter());
  mapCanvas.setZoom(6);

  path =[];
  for (var k = 0; k < mapdata.length; k++) {
    path.push(new google.maps.LatLng(mapdata[k][1], mapdata[k][0])); 
  }
  json.push(path);
  colors.push(color);
    
  var bounds, polyList = [];
  for (var i = 0; i < json.length; i++) {
    var polyline = createPolygon(json[i], colors[i]);
    polyList.push(polyline);
  }
  var clusterer = new MarkerClusterer(mapCanvas, polyList);
  counter = 0;
}

function createPolygon(path, col = "red") {
  var polygon = new google.maps.Polygon({
    path : path,
    strokeOpacity : 1,
    strokeColor : col,
    fillColor : col,
    fillOpacity: 0.7
  });

  var lastPath = null,
      lastCenter = null;
  polygon.getPosition = function() {
    var path = this.getPath();
    if (lastPath == path) {
      return lastCenter;
    }
    lastPath = path;
    var bounds = new google.maps.LatLngBounds();
    path.forEach(function(latlng, i) {
      bounds.extend(latlng);
    });

    lastCenter = bounds.getCenter()
    return lastCenter;
  };
  return polygon;
}

function deleteType(id){
  $.ajax({
    type: 'POST',
    url: url+ 'delete-poi-demographics/' + id,
    success: function() {
    }
  });
  setTimeout(function(){
    window.location.href = window.location.href;
  }, 1000);
}

function updateProperty(value){
  var valArray = idArrs;
  if( idArrs.length < 1){
    valArray = 'oneResult';
  }
  $.ajax({
    type: 'POST',
    url: url+'update-placemark/',
    data: {
      newPlacemark: value,
      id: PropVal,
      idList: valArray
    },
    success: function(resp) {
      modalVisualValidation(true);
      getPlacemarks();
    },
    error: function(resp){
      modalVisualValidation(false);
    }
  });
}

function fileChanged(e) {
  this.file = e.target.files[0]
  this.parseDocument(this.file)
}

function handleFileSelect(evt) {
  // File list Object
  var files = evt.currentTarget.files;

  // Iterate through files
  for (i = 0; i < files.length; i++) {
    // Initialise file reader
    var reader = new FileReader();

    // File data
    f = files[i];

    // File name
    var fileName = files[i]['name'];

    // Closure to capture the file information.
    reader.onload = (function() {
      return function(e) {
        extractGoogleCoords( e.target.result, fileName );
      };
    })(f);

    // Read file
    reader.readAsText(f);
  }
}

function replaceAll(string, search, replace) {
  return string.split(search).join(replace);
}

const capitalize = (s) => {
  return s.charAt(0).toUpperCase() + s.slice(1).toLowerCase();
}

function extractGoogleCoords(plainText, actualFileName) {
  let parser = new DOMParser();
  let xmlDoc = parser.parseFromString(plainText, "text/xml");
  let folders = xmlDoc.getElementsByTagName('Folder');
  
  kmlFileData = [];
  for (let i = 0; i < folders.length; i++){
    let suburb;
    let mapStyles;
    let plcmark;
    var alt = false;
      if (folders[i].getElementsByTagName('SimpleData').length > 0){
        alt = true;
        plcmark = folders[i].getElementsByTagName('Placemark');
      }
      else{
        suburb = folders[i].getElementsByTagName('Placemark');
      }
      let placemark = 'Uncategorized';
      // placemark  = folders[i].getElementsByTagName('name')[0].innerHTML;
      if (folders[i].getElementsByTagName('styleUrl').length > 0){
        mapStyles = folders[i].getElementsByTagName('styleUrl')[0].innerHTML;
        mapStyles = '#'+ mapStyles.substr(6,6);
      }
      else{
        mapStyles = folders[i].getElementsByTagName('color')[0].innerHTML;
        mapStyles = '#'+ mapStyles.substr(0,6);
      }

      if(alt == true){
        for(let j = 0; j < plcmark.length; j++){
          var placemarkData = $(plcmark[j]).children('ExtendedData');
          var the_suburb = '';
          $(placemarkData).find('SimpleData').each(function(index,vl){
            if($(vl).attr('name') == 'OFC_SBRB_NAME'){
              the_suburb = $(vl).text();
            }
            else if($(vl).attr('name') == 'suburb'){
              the_suburb = $(vl).text();
            }
          });
          
          kmlFileData.push({
            'layer': actualFileName,
            'placemark': placemark,
            'suburb': the_suburb,
            'points': replaceAll(plcmark[j].getElementsByTagName('coordinates')[0].innerHTML, ' ', '|'),
            'mapStyles': mapStyles
          });
        }
      }
      else{
        for(let j = 0; j < suburb.length; j++){
          var points = replaceAll(suburb[j].getElementsByTagName('coordinates')[0].innerHTML, ',0', '|');
          points = points.split(/[\s,]+/).join();
          points = replaceAll(points, '|,','|');
          points = points.substr(1, points.length -3);
          kmlFileData.push({
            'layer': actualFileName,
            'placemark': placemark,
            'suburb': suburb[j].getElementsByTagName('name')[0].innerHTML,
            'points': points,
            'mapStyles': mapStyles
          });
        }
      }
  }
  return kmlFileData;
}

function responseMessageModal(response) {
  $('#response-message-wrapper').removeClass('modal');
  $('#response-message-wrapper').removeClass('fade');
  $('#response-message-modal').removeClass('hide');

  $('#close-modal').click(function() {
    $('#response-message').empty();
    $('#response-message-wrapper').addClass('modal');
    $('#response-message-wrapper').addClass('fade');
    $('#response-message-modal').addClass('hide');
  });
  let resp = response;
  $('#response-title').empty();
  if (resp == "Successful upload!") {
    $('#response-title').append("Successful upload!");
    return getPlacemarks();
  } 
  else if( resp == 'tooMany'){
    $('#response-title').append("Maximum number exceeded!");
    $('#response-message').append('<h4>The uploded file(s) exceed 100 records</h4>');
    return getPlacemarks();
  }
  else {
    
    // $('#response-title').append('<p>'+resp+'</p>');
    $('#response-title').append('Upload failed! These suburbs are already assigned:');
    for (let i = 0; i < resp.length; i++) {
      suburbToJson = JSON.parse(resp[i]);
      for (let x = 0; x < suburbToJson.length; x++) {
        $('#response-message').append('<li>' + suburbToJson[x].suburb + '</li>');
      }
    }
  }
}

function modalVisualValidation(bool){
    if (bool !== true){
      $('#modal-error').show();
      $('#modal-error').fadeTo(300, 1);
      $('#modal-error').fadeTo(2000, 0);
    }
    $('#modal-checker').addClass('fast-spin');
    $('#modal-checker').fadeTo(300, 1);
    setTimeout(function(){
      $('#modal-checker').removeClass('fast-spin');
    },300);
    $('#modal-checker').fadeTo(2000, 0);
}

function getCategoriesAndAttributes(targ) {
  $.ajax({
    type: 'GET',
    url: url + 'categories-and-attributes',
    success: function(response) {
      var arr = [];
      var txt = $(targ).parent('tr').children('td:last-child').text();
      arr = txt.split('|');
      $('.propVal').append('<div class="col-md-6"><p class="text-left">Province</p></div><div class="col-md-6"><select id="property-value" onchange="updateProperty(this.value)"><option value="Eastern Cape">Eastern Cape</option><option value="Free State">Free State</option><option value="Gauteng">Gauteng</option><option value="KwaZulu-Natal">KwaZulu-Natal</option><option value="Limpopo">Limpopo</option><option value="Mpumalanga">Mpumalanga</option><option value="Northern Cape">Northern Cape</option><option value="North West">North West</option><option value="Western Cape">Western Cape</option><option value="Uncategorized">Uncategorized</option></select></div>');
      var activeState = $(targ).parent('tr').children('td:nth-child(2)').text()
      $('.propVal #property-value').val(activeState);
      $.each(response, function(i, e){
        var tdata = arr[i];
        var atts = tdata.split(',');
        $('.propVal').append('<div class="col-md-12" style="margin-bottom:5px; margin-top:5px;"><div class="title-holder"><p class="text-left">' + e.category + '</p></div><div class="cat-'+ e.cat_id +'" style="border-top:1px solid #999; padding-top:5px;"></div></div>');
        for (let x = 0; x < e.attributes.length; x++) {
          var indx = ((x + 1)*2)-1;
          $('.propVal .cat-'+ e.cat_id).append('<div class="col-md-3"><label style="font-size:11px;"><span>'+ e.attributes[x] +'</span><input type="number" value="'+ atts[indx] +'" min="0" max="100" step=".10"><span class="perc">%</span></label></div>');
        }
      });
      innitAttrListener();
    }
  });
}

function innitAttrListener(){
  $('.propVal').delegate('input', 'change', function(va){
    var attr = []; 
    var lastId = '1';
    var breaker ='';
    $('.propVal input').each(function(value){
      var theid = $(this).parent('label').parent('div').parent('div').attr('class').split('-').pop();
      if (theid != lastId){
        breaker ='|';
        lastId = theid;
      }
      else{
        breaker= '';
      }
      attr.push(breaker + theid +','+$(this).val());
    });
    updateCats(attr);
  });
}

function updateCats(attr){
  var catData = attr.join();
  catData = catData.replaceAll("|,", "|");
  var valArray = idArrs;
  if( idArrs.length < 1){
    valArray = 'oneResult';
  }
  $.ajax({
    type: 'POST',
    url: url + 'update-categories',
    data: {
      categoryList: catData,
      id: PropVal,
      idList:valArray
    },
    success: function(resp){
      modalVisualValidation(true);
      multiple = false;
      $('#placemarkSelector').val('0');
      $('#placemarkSelector').change();
    },
    error:function(resp){
      modalVisualValidation(false);
    }
  });
}
