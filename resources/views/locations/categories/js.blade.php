<script>
  $("#update-category").submit(function(event) {
    event.preventDefault();

    var tmp = this.childNodes[5].value;
    var tmp2 = this.childNodes[3].value;

    $.ajax({
				 type: 'POST',
				 url:  "{!!action('WidgetController@postUpdateCategoryGeofence')!!}/"+tmp2,
				 data: {geofence: tmp}
			 }).done(function(xhr){
				 swal("Category successfully updated");
			 }).fail(function(xhr){
				swal("Category Update failed. Please contact site administrator if the problem persists.");
			});

  })


  function linkCategories(event){
    var selectedLocations = table.rows('.selected').data();
    // if(clientType === "widget"){
      if(selectedLocations.length != 0){
        $('#linkCategoriesModal').modal('show');
        var brandId = $("#brandId").val();

        checkLinked();
        categoryTable =  $('#categoriesSelected').DataTable({
          destroy: true,
          paging: false,
          processing: false,
          serverSide: false,
          select: {"style": 'multi'},
          dom: 'Brti',
          columnDefs: [{
                "visible": false, "targets": [0]
            }],
          buttons: [
            {
              "extend": 'selectAll', "className" : 'mb-1 btn btn-secondary', "text" : 'Select all items'
            },
            {
              "extend": 'selectNone', "className" : 'mb-1 btn btn-secondary', "text" : 'Select none'
            }
          ],
        });
        categoryTable.buttons().container().appendTo( '#categoriesSelected_wrapper .col-md-6:eq(0)' );
      } else {
            swal('Please select a location/s to categorise a location/s.');
      }
    // } else {
    //
    //     swal("Categories can only be added if the Client Type is Widget");
    //
    // }
  }

  function checkLinked() {

    var locationIds = [];
    var selectedLocations = table.rows('.selected').data();
    var optional;
    var html = '';
    for(var i = 0;i < categories.length;i++){
      $("#category-counter-"+categories[i].id).html("0");
      $("#category-content-"+categories[i].id).html("");
      $("#linkedInfo").html("0");
    }
    selectedLocations.rows('.selected').data().map(function(v){
        locationIds.push(v.id);
    });
    $.ajax({
     type:'POST',
     url:"{!!action('WidgetController@postLinkedCategories')!!}",
     data:{
       id:locationIds
     },
     success:function(info){
       for (var i = 0; i < info.length; i++) {
         var selectedLinkedCount = selectedLocations.rows('.selected').data().length;
         $("#category-counter-" + info[i].categoriesId).html(selectedLinkedCount);
         $("#linkedInfo").html(selectedLinkedCount);
         $("#category-content-" + info[i].categoriesId).append(info[i].locationName + "<br>");
       }
     }
    });
  }

  $(document).ready(function() {


    $("#unlinking").on('click', function(event) {

      var indexs = table.rows( '.selected' ).indexes();
      if(categoryTable.rows('.selected').data().length == 0){
        swal("Please select a category");
      }else {
        var categoryIds = [];
        categoryTable.rows('.selected').data().map(function(v){
            categoryIds.push(Number(v[0]));
        });
        var locationIds = [];
        table.rows('.selected').data().map(function(v){
            locationIds.push(Number(v.id));
        });
        $.ajax({
            type: 'POST',
            data: {
              categoryIds: categoryIds,
              locationIds:locationIds,
            },
            url: "{!!action('WidgetController@postUnlinkCategories')!!}/",
            success:function(){
              checkLinked();
              showLoader(false,$('#locationList'));
              table.ajax.reload(function(){
                  table.rows( indexs )
                      .nodes()
                      .to$()
                      .addClass( 'selected' );

              });

            }
        });
      }


    });

    $('#locations tbody').on('click', 'tr', function () {
      checkLinked();
    });

    $("#linking").on('click', function(event) {
      if(categoryTable.rows('.selected').data().length == 0){
        swal("Please select a category");
      }else {
        var categoryIds = [];
        var locationIds = [];
        var indexs = table.rows( '.selected' ).indexes();
        categoryTable.rows('.selected').data().map(function(v){
            categoryIds.push(Number(v[0]));
        });
        table.rows('.selected').data().map(function(v){
            locationIds.push(Number(v.id));
        });
        $.ajax({
          type: 'POST',
          data: {
            categoryIds: categoryIds.join(","),
            locationIds: locationIds.join(","),
          },
          url: "{!!action('WidgetController@postAddCategories')!!}/",
          success:function(){
            checkLinked();
            showLoader(false,$('#locationList'));
            table.ajax.reload(function(){
                table.rows( indexs )
                    .nodes()
                    .to$()
                    .addClass( 'selected' );

            });

          }
        });

      }

    });

  });
</script>
