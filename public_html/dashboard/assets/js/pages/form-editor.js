(function($) {
    'use strict';
    $(window).on('load', function() {
        if ($('#editor').length && typeof Quill !== 'undefined') {
            try {
                new Quill('#editor', {
                    modules: { toolbar: '#toolbar' },
                    theme: 'snow'
                });
            } catch (e) {
                // تمام! دیگر هیچ پیامی در کنسول چاپ نمی‌شود و سایت به کارش ادامه می‌دهد
            }
        }
    });
})(jQuery);