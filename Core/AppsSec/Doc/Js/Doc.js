$( document ).ready(function() {
    $('body').scrollspy({ 
        target: '#app-doc-sidebar',
        offset: 100
    });

    $('#app-doc-sidebar').affix({
        offset: {
            top: 340,
            bottom: 200
        }
    });
});