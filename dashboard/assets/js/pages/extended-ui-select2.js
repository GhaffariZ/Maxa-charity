(function($) {
    'use strict';
    
    $(function() {

      const isRtl = $('html').attr('dir') === 'rtl' || $('body').attr('dir') === 'rtl';

      const select2 = $('.select2')
      const select2Icons = $('.select2-icons')

      if (select2.length) {
        select2.each(function () {
          var $this = $(this);
          $this.wrap('<div class="position-relative"></div>').select2({
            dir: isRtl ? 'rtl' : 'ltr',
            placeholder: 'انتخاب کنید',
            dropdownParent: $this.parent()
          });
        });
      }

    if (select2Icons.length) {
      
      function renderIcons(option) {
        if (!option.id) {
          return option.text;
        }
        var $icon = `<i class='${$(option.element).data('icon')} ${isRtl ? 'me-2' : 'ms-2'}'></i>` + option.text;

        return $icon;
      }
      select2Icons.wrap('<div class="position-relative"></div>').select2({
        dir: isRtl ? 'rtl' : 'ltr',
        dropdownParent: select2Icons.parent(),
        templateResult: renderIcons,
        templateSelection: renderIcons,
        escapeMarkup: function (es) {
          return es;
        }
      });
    }
    })
    
}(jQuery) )