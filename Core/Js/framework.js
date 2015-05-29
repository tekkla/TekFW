// ---------------------------------------------------------------------------- 
// Function with commands to use on "ready" and in/after ajax requests
// ----------------------------------------------------------------------------
function webReadyAndAjax() {
    
    
    // Bind datepicker
    $('.form-datepicker').webDatepicker();

    // Bind error popover
    $('.form-control[data-error]').webErrorPop();

    // Bind selectpicker
    $('.selectpicker').selectpicker();
    
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
}

// ----------------------------------------------------------------------------
// Eventhandler "ready"
// ----------------------------------------------------------------------------
$(document).ready(function() {

    // scroll to top button
    $(window).scroll(function() {

        if ($(this).scrollTop() > 100) {
            $('#scrolltotop').fadeIn();
        } else {
            $('#scrolltotop').fadeOut();
        }
    });

    // Run function with commands to be used on "ready" and "ajaxComplete"
    webReadyAndAjax()
});

// ----------------------------------------------------------------------------
// Eventhandler on "ajaxStart"
// ----------------------------------------------------------------------------
$(document).ajaxStart(function() {

    // Show loading circle on ajax loads
    $('body').addClass("loading");
});

// ----------------------------------------------------------------------------
// Do this on "ready" and on "ajaxComplete" events
// ----------------------------------------------------------------------------
$(document).ajaxStop(function(event) {

    // Hide loading circle
    $('body').removeClass("loading");
    
    webReadyAndAjax();
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
            var new_text = text.substr(0, limit);
            $(this).val(new_text);
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
$(document).on('click', '#scrolltotop', function(event) {

    if (navigator.userAgent.match(/(iPod|iPhone|iPad|Android)/)) {           
        window.scrollTo(0,0) // first value for left offset, second value for
								// top offset
    } else {
        $('html,body').animate({
            scrollTop: 0,
            scrollLeft: 0
        }, 800, function(){
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
$(document).on('click', '*[data-confirm]', function(event) {

    if ($(this).data('ajax') !== undefined) {
        return;
    }

    // confirmation wanted?
    if ($(this).data('confirm') !== undefined) {
        var result = confirm($(this).data('confirm'));
        if (!result) {
            return false;
        }
    }
});

// ----------------------------------------------------------------------------
// Ajax based click-handler to links with the data attribute 'data-ajax'
// ----------------------------------------------------------------------------
$(document).on('click', '*[data-ajax]', function(event) {
    loadAjax(this);
});

function loadAjax(element) {
    
    
    // confirmation wanted?
    if ($(element).data('confirm') !== undefined) {
        var result = confirm($(element).data('confirm'));
        if (!result) {
            return false;
        }
    }

    // Prepare options object
    var ajaxOptions = {

        // On success the response parser is called
        success : parseJson,

        // Returntype is JSON
        dataType : 'json'
    };

    // Which url to reqest? The data attribute "form"
    // indicates that we are going to send a
    // form. Without this, it is a normal link, that we are
    // going to load.
    if ($(element).data('form') === undefined) {

        // Ext links will be handled by GET
        ajaxOptions.type = 'GET';

        // Try to get url either from links href attribute or
        if ($(element).attr('href') !== undefined) {
            var url = $(element).attr('href');
        } else if ($(element).data('href') !== undefined) {
            var url = $(element).data('href');
        } else {
            alert('Ext Ajax: No URI to query found. Neither as "href" nor as "data-href". Aborting request.');
            return false;
        }
    } 
    else {

        // Ext forms will be handled py POST
        ajaxOptions.type = 'POST';

        // Get the form ID from the clicked link
        var id = $(element).data('form');

        // Get action url
        var url = $('#' + id).attr('action');

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
        console.log(errortext);
    };

    // Fire ajax request!
    $.ajax(ajaxOptions);
    
    event.preventDefault();    
}

// ----------------------------------------------------------------------------
// Json parser for Ext ajax response
// ----------------------------------------------------------------------------
function parseJson(json) {
    
    console.log(json);
    
    $.each(json, function(type, stack) {
        
        // DOM manipulations
        if (type=='dom')
        {
            $.each(stack, function(id, cmd) {
                
                var selector = $(id);
                
                if (selector !== undefined) {
                    $.each(cmd, function(i, x) {
                        selector = selector[x.f](x.a);
                    });
                }
                else {
                    console.log('Selector "' + id + '" not found.');
                }
            });
        }
        
        // Specific actions
        if (type=='act')
        {
            $.each(stack, function(i, cmd) {
              
                switch (cmd.f) {
                    case "alert":
                        bootbox.alert(cmd.a[0]);
                        break;
                    case "error":
                        $('#message').addClass('fade in').append(cmd.a[0]);
                        $('#message').bind('closed.bs.alert', function() {
                            $(this).removeClass().html('').unbind('closed.bs.alert');
                        });
                        break;
                    case "dump":                        
                    case "log":
                    case "console":
                        console.log(cmd.a);
                        break;
                    case "modal":

                        // fill dialog with content
                        $('#modal').html(cmd.a).modal({
                            keyboard : false
                        });
                        break;

                    case 'load_script':
                        $.getScript(cmd.a);
                        break;
                    
                    case 'href':
                        window.location.href = cmd.a;
                        return;
                }
            });
        }
    });
}
