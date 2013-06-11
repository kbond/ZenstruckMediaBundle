window.ZenstruckMediaWidget = {
    // current element
    element: null,
    selectCallback: null,

    selectFile: function(file) {
        this.element.val(file);
        this.element = null;

        // trigger select hook
        if (this.closeCallback) {
            this.closeCallback();
        }
    },

    init: function(openCallback, selectCallback) {

        // set select hook
        if (jQuery.isFunction(selectCallback)) {
            this.selectCallback = selectCallback;
        }

        // clear selection
        $('.zenstruck-media-clear', '.zenstruck-media-widget').on('click', function(e) {
            e.preventDefault();
            $(this).siblings('.zenstruck-media-input').val('');
        });

        // set element
        $('.zenstruck-media-select', '.zenstruck-media-widget').on('click', function(e) {
            e.preventDefault();
            ZenstruckMediaWidget.element = $(this).siblings('.zenstruck-media-input');
        });

        // trigger open hook
        if (jQuery.isFunction(openCallback)) {
            $('.zenstruck-media-select', '.zenstruck-media-widget').on('click', openCallback);
        }
    }
};