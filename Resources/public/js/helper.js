/**
 * Media helper
 */
window.ZenstuckMedia = {
    currentMediaInputFile: null,

    initMediaWidget: function() {
        if (!$.fancybox) {
            return;
        }

        // clear selection
        $('.zenstruck-media-clear', '.zenstruck-media-widget').on('click', function(e) {
            e.preventDefault();
            $(this).siblings('.zenstruck-media-input').val('');
        });

        // init fancybox
        $('.zenstruck-media-select', '.zenstruck-media-widget').fancybox({
            type:    'iframe',
            width:   '70%',
            height:  '70%',
            autoSize: false,
            beforeShow: function() {
                // reset
                ZenstuckMedia.currentMediaInputFile = null;
            },
            beforeClose: function() {
                var file = ZenstuckMedia.currentMediaInputFile;

                if (file) {
                    this.element.siblings('.zenstruck-media-input').val(file);
                }

                // reset
                ZenstuckMedia.currentMediaInputFile = null;
            }
        });
    }
};

$(function() {
    ZenstuckMedia.initMediaWidget();
});