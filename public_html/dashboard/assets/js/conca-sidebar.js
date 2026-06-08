( function ( $ ) {
    'use strict';

    window.addEventListener('DOMContentLoaded', function(){
        
    
        const mainPath = window.location.pathname;
        let url = window.location.origin + mainPath;
        let allLinks = document.querySelectorAll('.app-sidebar-menu a');
        allLinks.forEach(link => {
            let linkArr = link.href.split('../../index.html');
            let lastPath = linkArr[linkArr.length - 1];
            let isHomePage = lastPath.includes('index.html') && mainPath === '../../index.html';
            if (mainPath.includes(lastPath) || isHomePage) {
                link.classList.add('menu-current');
            }
        });
    
    
        // update sidebar menu height
        function update_sidebar_menu_height() {
            let headerHeight = 60;
            let footerHeight = 60;
            let menuHeight = $(window).height() - (headerHeight + footerHeight)
            $('.app-sidebar-menu').css('height',  menuHeight + 'px');
        }
    
        $(window).on('resize', function(){
            update_sidebar_menu_height()
        });
    
    
        // initialize
        (function() {
            update_sidebar_menu_height();
        })();
    
    
        // add scrollbar to sidebar menu
        if(document.querySelector('.app-sidebar-menu')){
            new PerfectScrollbar(document.querySelector('.app-sidebar-menu'), {
                suppressScrollX: true
            });
        }

        $('.app-sidebar-menu-item').each(function() {
            let $this = $(this);
            if ($this.find('.menu-current').length > 0) {
                $this.find('.menu-current').addClass('active');
                $this.parent().show()
                $this.parent().parent().children('.menu-link').addClass('active')
            }
        });
    
        // list open hidden
        $('.menu-link').on('click', function() {
            $(this).toggleClass('active');
            $(this).next('.app-sidebar-submenu').slideToggle(300);
        });
        
    })

    $('.app-sidebar-open-btn').on('click', function(e){
        e.preventDefault();
        $('.app-sidebar').removeClass('open');
        if($(this).hasClass('collapsed')) {
            $(this).removeClass('collapsed');
            $('.app-sidebar').removeClass('collapsed')
        }
        else {
            $(this).addClass('collapsed');
            $('.app-sidebar').addClass('collapsed')
        }
    })

    $('.app-sidebar-mobile-open').on('click', function(){
        $('.app-sidebar').removeClass('collapsed').addClass('open');
        $('.app-backdrop').addClass('show');
    });

    $('.app-sidebar-mobile-close').on('click', function(){
        $('.app-sidebar').removeClass('collapsed').removeClass('open');
        $('.app-backdrop').removeClass('show');
    });

    $('.app-backdrop').on('click', function(){
        $('#app-wrapper').removeClass('open');
        $('#app-sidebar').removeClass('open');
        $(this).removeClass('show');
    });
    
}(jQuery) ) 