( function ( $ ) {
    'use strict';
  
    
        $('.form-repeater').repeater({
            
            show: function () {
                $(this).slideDown();
            },

            hide: function (deleteElement) {
                if(confirm('Are you sure you want to delete this element?')) {
                    $(this).slideUp(deleteElement);
                }
            },
        })

  
  }(jQuery) )