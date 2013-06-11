$(function() {
    ZenstruckMediaWidget.init(null, function() {
        jQuery.fancybox.close();
    });

    // init fancybox
    $('.zenstruck-media-select', '.zenstruck-media-widget').fancybox({
        type:    'iframe',
        width:   '70%',
        height:  '70%',
        autoSize: false
    });
});