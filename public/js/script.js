$(document).ready(function() {
    $('#menu-toggle').click(function() {
        $('#page-wrapper').toggleClass('margin_zero');
        $('#main-nav').toggleClass('hide-menu');
        $(this).toggleClass('move-toggle');
        if ($(this).html() == '<i class="fa fa-caret-left"></i>') {
			$(this).html('<i class="fa fa-caret-right"></i>');
		} else {
			$(this).html('<i class="fa fa-caret-left"></i>');
		}
    });
});
