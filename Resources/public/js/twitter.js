window.ZenstuckMedia = {
    element: null,
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
    },

    initialize: function() {
        this.element = $('#zenstruck-media');

        // tooltips
        $('a[title]', this.element).tooltip({
            container: 'body'
        });

        // thumbnail actions hover
        $('li', '#zenstruck-media-thumb').hover(function() {
            $('.zenstruck-media-actions', $(this)).toggleClass('hide');
        });

        // rename click
        $('.zenstruck-media-rename', this.element).on('click', function(e) {
            e.preventDefault();
            var name = $(this).data('name');
            var url = $(this).data('url');

            // set up form
            $('#zenstruck-media-rename-new-name').val(name).focus(function() {
                this.select();
            });
            $('form', '#zenstruck-media-rename').attr('action', url);

            // launch dialog
            $('#zenstruck-media-rename').modal();
        });

        // delete action
        $('.zenstruck-media-actions .delete', this.element).on('click', function(e) {
            e.preventDefault();

            if (!confirm("Are you sure you want to delete?")) {
                return;
            }

            // create form
            $('<form></form>')
                .attr('method', 'POST')
                .attr('action', $(this).attr('href'))
                .append('<input type="hidden" name="_method" value="DELETE" />')
                .appendTo($('body'))
                .submit()
            ;
        });

        // file selection
        $('.zenstruck-media-file-select', this.element).on('click', function(e) {
            e.preventDefault();

            // iframe file select
            if ('iframe' === ZenstuckMedia.element.data('layout').toLowerCase()) {
                parent.ZenstuckMedia.currentMediaInputFile = $(this).data('file');
                parent.jQuery.fancybox.close();
                return;
            }

            // ckeditor
            var ckeditor = ZenstuckMedia.element.data('ckeditor');
            if (ckeditor) {
                if (!window.opener.CKEDITOR) {
                    return;
                }

                window.opener.CKEDITOR.tools.callFunction(ckeditor, $(this).data('file'));
                window.close();
            }
        });

        this.initMediaWidget();
    }
};

$(function() {
    ZenstuckMedia.initialize();
});