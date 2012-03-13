if ( orgup === undefined ) {
	orgup = {
		vars: {},
        pages: {},
        common: {},
        templates: {}
	}
}
orgup.common = {

	notificationcount: 0,
    ajaxcache: [],
    ajax_last_cache_id: 0,
    time_before_close: 4000, // in ms

	showmess: function( message, messtype, time, width ) {

		var messages = [];

		if ( message instanceof Array ) {
			messages = message;
		} else {
			messages[0] = message;
		}

		var html = '';
		var notification_id = ++this.notificationcount;

        html += '<div id="notification'+notification_id+'">';
		for ( var i=0; i< messages.length; i++ ){
			html += '<div class="noterr-block noterr-'+messtype+'">'+messages[i]+'</div>';
		}
        html += '</div>';

		$('#messageframe').append(html);
        if ( width ) {
            $('#messageframe').width( width );
        }

        if ( time === undefined ) {
            time = orgup.common.time_before_close;
        }

		if ( time > 0 ) {
            setTimeout(function(){
                $('#notification'+notification_id).animate({
                   opacity: 0
                }, 500).slideUp( 500, function(){$(this).remove();});
            }, time * messages.length );
        }
	},

    for_message_manage: function() {
        $('#messageframe .noterr-block').click(function(){
			$(this).remove();
		});

        setTimeout(function(){
			$('#noterr_onstart').animate({
			   opacity: 0
			},500).slideUp(500, function(){$(this).remove();});
		}, 20000 );
    },

    check_object_or_string: function( firstObj, secondObj ) {
        if ( typeof firstObj !== typeof secondObj )
            return false;

        if ( typeof firstObj == 'object' )
            return Object.equal( firstObj, secondObj );

        return firstObj === secondObj;
    }
};

orgup.box = {

    box_last_id: 0,
    box_last_zindex: 100,

	replacebox: function( header, content, boxid, overlay, boxsize, removable ){
        if ( removable !== undefined )
            $('#box'+boxid).addClass('box-removable');
        $('#box'+boxid+' .box-header-text').html(header);
        $('#box'+boxid+' .box-inner').html(content);
        if ( boxsize ) {
            $('#box'+boxid+' .box-container').width(boxsize);
        }
        if ( overlay ) {
            $('#box'+boxid+' .box-overlay').show();
        }
	},

	loadbox: function ( boxtype, boxid, parameters ){

        if ( boxid === undefined ) {
            boxid = ++this.box_last_id;
        }

        if ( document.getElementById('box'+boxid ) === null )
	        this.showbox( orgup.lang.loading, orgup.templates.loader, boxid );

		var data = 'boxtype='+boxtype+'&boxid='+boxid;

		if ( parameters !== undefined )
			data += '&'+parameters;

		$.gajax({
			data: data,
			action: 'box'
		});
	},

    showbox: function( header, content, boxid ) {

        var box = orgup.templates.box.replace( '%boxid%', boxid );
        box = box.replace( '%header_text%', header );
        box = box.replace( '%content%', content );

		$('body').prepend(box);

        var scr = 200;
		if( window.scrollY !== undefined ){
			scr += window.scrollY;
		} else if ( document.documentElement !== undefined && document.documentElement.scrollTop ) {   // for ie
			scr += document.documentElement.scrollTop;
		}

		$('#box'+boxid).css('top',scr).css('z-index', ++this.box_last_zindex );
    },

    close: function( box, permanent ) {

        if ( typeof box == 'string' ) {
            box = $('#box'+box);
        }

        if ( permanent ) {
            box.remove();
        } else {
            box.fadeOut( 300, function(){
                box.remove();
            });
        }
    }
};

orgup.process_response = {

	// парсер ответа
	parse_code: function( xml, data ){

        var messages = { error: [], notification: []};
		$(xml).find('error').each(function(){
			messages.error.push( $(this).text() );
		});
		$(xml).find('message').each(function(){
			messages.notification.push( $(this).text() );
		});

		var html = [];
		$(xml).find('html').each(function(){
			html[ $(this).attr('key') ] = $(this).text();
		});

        var scripts = [];
        $(xml).find('script').each(function(){
            scripts.push( $(this).text() );
        });

        if ( scripts.length > 0 ) {

            $('head').find('script').each(function(){

                for ( i in scripts ) {

                    if (!scripts.hasOwnProperty(i))
                        continue;

                    if ( scripts[i] == $(this).attr('src') ) {
                        delete scripts[i];
                    }
                }
            });
            for ( i in scripts ) {

                orgup.vars._await_script = true;

                if ( !scripts.hasOwnProperty(i) )
                    continue;

                $.getScript(scripts[i], function(){
                    orgup.vars._await_script = false;
                });
            }
        }

        var functions = [],
			i = 0;
		$(xml).find('function').each(function(){

			functions[i] = { name : $(this).find('funcname').text(), params: {} };

			$(this).find('parameter').each(function(){
				functions[i]['params'][ $(this).find('name').text() ] = $(this).find('value').text();
			});

			i++;
		});

        var answer = [ html, functions, messages ];

        var cache = $(xml).find('cachetime').text();
        if ( cache && data !== undefined && data.data !== undefined ) {

            var newcacheid = ++orgup.common.ajax_last_cache_id;
            orgup.common.ajaxcache[newcacheid] = { data: data.data, response: answer };

            if ( cache != -1 ) {
                setTimeout(function() {
                    delete(orgup.common.ajaxcache[newcacheid]);
                }, cache * 1000 );
            }
        }

		// debug
        if ( orgup.vars.debug !== undefined ) {
            $('#dbg-inner').html( $(xml).find('log').text() );
        }

		return answer;
	},

	init: function( xml, data ) {
		this.start_functions( this.parse_code( xml, data ) );
	},

	start_functions: function( result ) {

        var i;

        if ( orgup.vars._await_script !== undefined && orgup.vars._await_script ) {

            setTimeout( function(){ orgup.process_response.start_functions( result )}, 40 );
            return false;
        }

        /* notifications anf errors */
        for( var type in result[2] ) {
            if ( result[2][type].length > 0 )
                for( i = 0; i < result[2][type].length; i++ ) {
                    orgup.common.showmess( result[2][type][i], type );
                }
        }

        /* functions */
		if ( result[1].length > 0 ) {
			for( i = 0; i < result[1].length; i++ ) {
				if ( typeof(orgup.ajax_functions[result[1][i].name] ) == 'function' ) {
					orgup.ajax_functions[result[1][i].name]( result[1][i].params, result[0] );
                }
			}
		}
	}
};

jQuery.extend({

	gajax: function(s) {

		if (s.action === undefined) {
			alert('prop "action" not allowed in ajax request');
			return;
		}

        var i;

		if ( !('data' in s) || s.data.length === 0 ) {
			s.data = {};
			s.data.ajaxaction = s.action;
		} else if ( s.data instanceof Object ) {
			s.data.ajaxaction = s.action;
		} else {
			s.data = s.data+'&ajaxaction='+s.action;
		}

        // check cache
        for( i in orgup.common.ajaxcache ) {
            if (!orgup.common.ajaxcache.hasOwnProperty(i))
                continue;

            if ( orgup.common.check_object_or_string( orgup.common.ajaxcache[i].data, s.data ) ) {
				orgup.process_response.start_functions( orgup.common.ajaxcache[i].response );
                return;
            }
        }

		var options = {
			url: '/ajax.php',
			type: 'post',
			dataType: 'xml',
			success: function(xml){
				orgup.process_response.init(xml, s );
			},
			timeout: 30000,
			error: function(s,err){
				if ( s.status == 200 && err == 'parsererror' ) {
                    orgup.common.showmess(s.responseText, 'error', 0, 700 );
				}
                else if ( err == 'timeout' ) {
					alert('Connection to the server was reset on timeout.');
				}
                else if ( s.status == 12029 || s.status == 0 ) {
                    alert('No connection with network.');
                }
			}
		};

		var possible_options = ['beforeSend', 'complete', 'success', 'data' ];

		for ( i = 0; i < possible_options.length; i++ ) {
			if ( possible_options[i] in s )
				options[possible_options[i]] = s[possible_options[i]];
		}

		$.ajax(options);
	}
});

Object.equal = function( firstObj, secondObject ){

    // fix for IE <= 8: Object.keys not supported
    if ( $.browser.msie !== undefined && $.browser.version <= 8 )
        return false;

	var keysFirstObj = Object.keys( firstObj );
	var keysSecondObject = Object.keys( secondObject );
	if ( keysFirstObj.length != keysSecondObject.length ) {
		return false;
	}
	return !keysFirstObj.filter(function( key ){
		if ( typeof firstObj[key] == "object" || Array.isArray( firstObj[key] ) ) {
			return !Object.equal(firstObj[key], secondObject[key]);
		} else {
            return firstObj[key] !== secondObject[key];
		}
	}).length;
};

$(document).ready(function(){

    if ( document.getElementById('messageframe') === null ) {
		$('body').append('<div id="messageframe"></div>');
	}

    $('body').addClass('js');

    orgup.common.for_message_manage();

    if ( orgup.vars.debug !== undefined ) {

        var dbg_height = $('#dbg-wrapper').height();
        $('#dbg-wrapper').height(0);
        $('#open_debug').toggle(
            function() {
                // with fix for chrome  ,function(){$('#dbg-slider').height('inherit');}
                $('#dbg-wrapper').animate({height:dbg_height},function(){$('#dbg-slider').height('inherit');});
            },
            function () {
                $('#dbg-wrapper').animate({height: 0});
            }
        );

        $('#dbg-resize').mousedown(function(e){

            var dbg_wrapper = $('#dbg-wrapper');
            var old_height = dbg_wrapper.height();
            var old_clientY = e.clientY;

            $(document).mousemove(function(e){

                var clientY = e.clientY;
                if ( clientY < 200 )
                    clientY = 200;

                dbg_height = old_height + old_clientY - clientY;
                if ( dbg_height < 50 )
                    dbg_height = 50;

                dbg_wrapper.height(dbg_height);
                $('#dbg-slider').height('inherit');
                // fix for chrome
                $('#open_debug').css('bottom', dbg_height );
            });
        });

        $(document).mouseup(function(){
            $(this).off('mousemove');
        });
    }

    // start functions
	if ( orgup.vars.scripts !== undefined ) {
        var script;
        for ( script in orgup.vars.scripts ) {
            if ( orgup.pages[orgup.vars.scripts[script]] !== undefined &&
                    orgup.pages[orgup.vars.scripts[script]].run !== undefined &&
                    typeof( orgup.pages[orgup.vars.scripts[script]].run ) == 'function' ) {
		        orgup.pages[orgup.vars.scripts[script]].run();
            } else if ( orgup.vars.debug !== undefined ) {
                alert( 'script '+orgup.vars.scripts[script]+' not found!');
            }
        }
	}

    if ( orgup.vars.form_errors !== undefined ) {
        var field_name;
        for( field_name in orgup.vars.form_errors){
            $('input[name="'+orgup.vars.form_errors[field_name]+'"]').addClass("ui-field-empty-error").focus(function(){
                $(this).removeClass('ui-field-empty-error').unbind('focus');
            });
        }
    }
});