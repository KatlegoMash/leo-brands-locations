var url = '/data-dashboard/';
$(document).ready(function() {
  $('.col-md-4').each(function(i,e){
    var dashboard = $(e).attr('dataid');
    $.ajax({
      type: 'GET',
      url: url+'comments/'+dashboard,
      success: function(resp) {
        console.log(resp);
      }
    });
  });
});
