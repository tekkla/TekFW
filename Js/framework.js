// ---------------------------------------------------------------------------- 
// Function with commands to use on "ready" and in/after ajax requests
// ----------------------------------------------------------------------------

var coreFw = {

    readyAndAjax : function() {

        // Bind datepicker
        $('.form-datepicker').coreDatepicker();

        // Bind error popover
        $('.form-control[data-error]').coreErrorPop();
        
     	$('[data-toggle="popover"]').popover();


        // beautifiying xdebug oputput including ajax return values add styling
        // hooks to any XDEBUG output
        $('font>table').addClass('xdebug-error');
        $('font>table *').removeAttr('style').removeAttr('bgcolor');
        $('font>table tr:first-child').addClass('xdebug-error_description');
        $('font>table tr:nth-child(2)').addClass('xdebug-error_callStack');

        // Fade out elements
        $('.fadeout').delay(fadeout_time).slideUp(800, function() {
            $(this).remove();
        });
   
        
        $(".sortable tbody").sortable({
            axis: 'y',
            update: function(event, ui) {
                
                var data = $(this).sortable('serialize');
                
                if ($(this).data('url') !== undefined) {
                    // POST to server using $.post or $.ajax
                    $.ajax({
                        data: data,
                        type: 'POST',
                        url: $(this).data('url') + '/ajax'
                    });
                }
            }
        });

    },

    loadAjax : function(element, callback, ajaxOptions) {

        if (ajaxOptions === undefined) {
            var ajaxOptions = {};
        }

        // On success the response parser is called
        if (ajaxOptions.hasOwnProperty('success') === false) {
            ajaxOptions.success = this.parseJSON;
        }

        // RETURNTYPE IS JSON
        if (ajaxOptions.hasOwnProperty('dataType') === false) {
            ajaxOptions.dataType = 'json';
        }

        // Which url to reqest? The data attribute "form"
        // indicates that we are going to send a
        // form. Without this, it is a normal link, that we are
        // going to load.
        if ($(element).data('form') === undefined && $(element).attr('form') === undefined) {

            // Ext links will be handled by GET
            ajaxOptions.type = 'GET';

            // Try to get url either from links href attribute or
            if ($(element).attr('href') !== undefined) {
                var url = $(element).attr('href');
            }
            else if ($(element).data('href') !== undefined) {
                var url = $(element).data('href');
            }
            else if ($(element).data('url') !== undefined) {
                var url = $(element).data('url');
            }
            else {
                console.log('CoreFW ajax GET: No URI to query found. Neither as "href", as "data-href" or "data-url". Aborting request.');
                return false;
            }
        }
        else {

            // Ext forms will be handled py POST
            ajaxOptions.type = 'POST';

            // Get form id
            switch (true) {
                case ($(element).attr('form') !== undefined):
                    // Get the form ID from the clicked link
                    var id = $(element).attr('form');
                    break;

                case ($(element).data('form') !== undefined):
                    // Get the form ID from the clicked link
                    var id = $(element).data('form');
                    break;
                default:
                    console.log('CoreFW ajax POST: No form id to submit found. Neither as "form" nor as "data-form" attribute. Aborting request.');
                    return false;
            }

            // Get action url
            switch (true) {
                // Buttons formaction attribute
                case ($(element).attr('formaction') !== undefined):
                    // Get the form ID from the clicked link
                    var url = $(element).attr('formaction');
                    break;
                // Action from data attributes url or href
                case ($(element).data('url') !== undefined):
                    var url = $(element).data('url');
                    break;
                case ($(element).data('href') !== undefined):
                    // Get the form ID from the clicked link
                    var url = $(element).data('href');
                    break;
                case ($('#' + id).attr('action') !== undefined):
                    var url = $('#' + id).attr('action');
                    break;
                default:
                    console
                            .log('CoreFW ajax POST: No form action for submit found. Neither as "formaction" nor as "data-url", "data-href" or "action" attribute from the form itself. Aborting request.');
                    return false;
            }

            // experimental usage of ckeditor 4 inline editor. id is
            // the div where the content is present
            // control the hidden form where we put the content
            // before serialization gathers the form data
            // for ajax post.
            if ($(element).data('inline-id') !== undefined && $(element).data('inline-control') !== undefined) {

                var control = $(element).data('inline-control');
                var content = $('#' + $(element).data('inline-id')).html();

                $('#' + control).val(content);
            }

            // Since this is a form post, get the data to send to
            // server
            ajaxOptions.data = $('#' + id).serialize();
        }

        // Set the url to use
        ajaxOptions.url = url + '/ajax';

        // Add error handler
        ajaxOptions.error = function(XMLHttpRequest, textStatus, errorThrown) {
            var errortext = XMLHttpRequest !== undefined ? XMLHttpRequest.responseText : 'Ajax Request Error: ' + textStatus;
        };

        // Fire ajax request!
        $.ajax(ajaxOptions);

        if (ajaxOptions.type !== 'POST' && $(element).data('nostate') === undefined) {
            history.pushState({}, '', url);
        }

        if (callback !== undefined) {
            callback();
        }

        return this;
    },

    // ----------------------------------------------------------------------------
    // Json parser for Ext ajax response
    // ----------------------------------------------------------------------------
    parseJSON : function(json) {
        
    	console.log(json);
    	
        $.each(json, function(type, stack) {

            // DOM manipulations
            if (type == 'dom') {
                $.each(stack, function(id, cmd) {

                    if ($(id).length) {

                        $.each(cmd, function(i, x) {

                            if (jQuery.isFunction($()[x.f])) {
                                selector = $(id)[x.f](x.a);
                            }
                            else {
                                console.log('Unknown method/function "' + x.f + '"');
                            }
                        });

                    }
                    else {
                        console.log('Selector "' + id + '" not found.');
                    }
                });
            }

            // Specific actions
            if (type == 'act') {
                $.each(stack, function(i, cmd) {

                    switch (cmd.f) {
                        case "error":
                            $(cmd.a[0]).addClass('fade in').append('<p>' + cmd.a[1] + '</p>');
                            $(cmd.a[0]).bind('closed.bs.alert', function() {
                                $(this).removeClass().html('').unbind('closed.bs.alert');
                            });
                            break;
                        case 'getScript':
                            $.getScript(cmd.a);
                            break;
                        case 'href':
                            window.location.href = cmd.a;
                            return;
                        default:
                        	[cmd.f](cmd.a);
                        	break;
                    }
                });
            }
        });

        coreFw.readyAndAjax();
    }

}

// ----------------------------------------------------------------------------
// Eventhandler "ready"
// ----------------------------------------------------------------------------
$(document).ready(function() {

    // scroll to top button
    $(window).scroll(function() {

        if ($(this).scrollTop() > 100) {
            $('#core-scrolltotop').fadeIn();
        }
        else {
            $('#core-scrolltotop').fadeOut();
        }
    });

    // Run function with commands to be used on "ready" and "ajaxComplete"
    coreFw.readyAndAjax();

});

// ----------------------------------------------------------------------------
// Eventhandler on "ajaxStart"
// ----------------------------------------------------------------------------
$(document).ajaxStart(function() {

    // Show loading circle on ajax loads
    // $('body').addClass("loading");
});

// ----------------------------------------------------------------------------
// Do this on "ready" and on "ajaxComplete" events
// ----------------------------------------------------------------------------
$(document).ajaxStop(function(event) {

    // Hide loading circle
    // $('body').removeClass("loading");

    coreFw.readyAndAjax();
});

// ----------------------------------------------------------------------------
// Input|textarea maxlength counter
// ----------------------------------------------------------------------------
$(document).on('keyup input paste', 'textarea[maxlength]', function() {

    if ($(this).data('counter') !== undefined) {

        var limit = parseInt($(this).attr('maxlength'));
        var text = $(this).val();
        var chars = text.length;

        if (chars > limit) {
            $(this).val(text.substr(0, limit));
        }

        var counterid = $(this).data('counter');

        if ($(counterid).length > 0) {
            $(counterid).text(limit - chars);
        }
    }
});

// ----------------------------------------------------------------------------
// Scroll to top click handler
// ----------------------------------------------------------------------------
$(document).on('click', '#core-scrolltotop', function(event) {

    if (navigator.userAgent.match(/(iPod|iPhone|iPad|Android)/)) {
        window.scrollTo(0, 0);
    }
    else {
        $('html,body').animate({
            scrollTop : 0,
            scrollLeft : 0
        }, 800, function() {
            $('html,body').clearQueue();
        });
    }

    return false;
});

// ----------------------------------------------------------------------------
// ClickHandler for back button
// ----------------------------------------------------------------------------
$(document).on('click', '.btn-back', function(event) {
    document.history.go(-1);
});

// ----------------------------------------------------------------------------
// ClickHandler for confirms
// ----------------------------------------------------------------------------
$(document).on('click', '*[data-confirm] + *:not([data-ajax])', function(event) {

    // confirmation wanted?
    if ($(this).data('confirm') !== undefined) {

        if (!confirm($(this).data('confirm'))) {
            event.preventDefault();
            return false;
        }
    }
});

// ----------------------------------------------------------------------------
// Ajax based click-handler for links with the data attribute 'data-ajax'
// ----------------------------------------------------------------------------
$(document).on('click', '*[data-ajax]', function(event) {

    if ($(this).data('confirm') !== undefined) {

        if (!confirm($(this).data('confirm'))) {
            return false;
        }
    }

    coreFw.loadAjax(this);

    if ($(this).data('to-top') !== undefined) {
        window.scrollTo(0, 0);
    }

    event.preventDefault();
});

// ----------------------------------------------------------------------------
// WIP: Backbutton on ajax requests
// ----------------------------------------------------------------------------
$(window).on("popstate", function(e) {
    if (e.originalEvent.state !== null) {
        location.href = location.href;
    }
});

// ----------------------------------------------------------------------------
// Autoclose for collapseable navbar on link click
// ----------------------------------------------------------------------------
$(document).on('click', '.navbar-collapse a', function() {
    $(".navbar-collapse").collapse('hide');
});
