$(document).ready(function(){

	$(document).on('click', '.box-close', function(){
		orgup.box.close( $(this).parent().parent().parent().parent().parent() );
		return false;
	});

    $(document).on('click', '.box-overlay', function(){
        orgup.box.close( $(this).parent() );
    });
    
    $('#menu .menu-link').hover(function() {
        $(this).animate({
            width: 270,
            backgroundColor: '#9A754B'
        },150);
    },function() {
        $(this).animate({
            width: 220,
            backgroundColor: '#600217'
        },150);
    });

    $('#top-menu .top-menu-link').hover(function() {
    $(this).animate({
        top: 0,
        backgroundColor: '#600217'
        },150);
    },function() {
        $(this).animate({
            top: 20,
            backgroundColor: '#9A754B'
        },150);
    });
});

// functions loaded after ajax response
orgup.ajax_functions = {

    render_box: function(agrs, html ){
        orgup.box.replacebox( agrs.header, html[0], agrs.boxid, (agrs.overlay == '1'), agrs.boxsize );
    },

    close_box: function(agrs) {
        $('#box'+agrs.boxid).remove();
    },

    calculate: function( agrs, html ) {
        $('#loans').html(html[0]);
    },

    feedback: function(){

        var city = $('#feedback-city').val();

        if ( YMaps !== undefined && city.length == 0 ) {
            $('#feedback-city').val( YMaps.location.city );
        }

        $('#ajax_feedback').submit(function(){

            var button;
            var send = $('.feedback-send-button');

            $.gajax({
                data: $(this).serialize(),
                action: 'send_feedback',
                beforeSend: function(){
                    button = send.html();
                    send.html( orgup.templates.loader );
                },
                complete: function() {
                    send.html( button );
                }
            });

            return false;
        });
    },

    feedback_sended: function() {
        orgup.box.close('feedback');
    },

    form_errors: function( agrs ) {
        for ( var field_name in agrs ) {
            $('[name="'+agrs[field_name]+'"]').addClass("ui-field-empty-error").focus(function(){
                $(this).removeClass('ui-field-empty-error').unbind('focus');
            });
        }
    }
};