(function ($) {
    'use strict';
    
    $(function() {
        let verticalExample = document.getElementById('perfect-vertical');
        let vertical = new PerfectScrollbar(verticalExample, {
            wheelPropagation: false,
        });

        let horizontalExample = document.getElementById('perfect-horizontal');
        let horizontal = new PerfectScrollbar(horizontalExample, {
            wheelPropagation: false,
            suppressScrollY: true
            
        });

        let bothScrollbarsExample = document.getElementById('perfect-both-scrollbars');
        let bothScrollbars = new PerfectScrollbar(bothScrollbarsExample, {
            wheelPropagation: false,
        });
    });
}(jQuery) )