(function($) {
  'use strict'


    // enable tooltip
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    // enable popover
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))

   
    const THEME_KEY = "conca_theme";

    const getStoredTheme = () => localStorage.getItem(THEME_KEY);
    const setStoredTheme = (theme) => localStorage.setItem(THEME_KEY, theme);


    const getSystemTheme = () =>
        window.matchMedia("(prefers-color-scheme: dark)").matches
            ? "dark"
            : "light";

    const getPreferredTheme = () => {
        return getStoredTheme() || "auto";
    };

    function updateLogo(theme) {
        const logoBlack = document.querySelector(".logo-black");
        const logoWhite = document.querySelector(".logo-white");

        if (!logoBlack || !logoWhite) return;

        if (theme === "dark") {
            logoWhite.classList.remove("d-none");
            logoBlack.classList.add("d-none");
        } else {
            logoWhite.classList.add("d-none");
            logoBlack.classList.remove("d-none");
        }
    }

    function updateBodyClass(theme) {
        if( theme === "auto") {
            $("body").removeClass("theme-light theme-dark").addClass('theme-auto');
        } else if (theme === "dark") {
            $("body").removeClass("theme-light theme-auto").addClass('theme-dark');
        } else {
            $("body").removeClass("theme-dark theme-auto").addClass('theme-light');
        }
    }

    const applyTheme = (theme) => {
        const resolvedTheme = theme === "auto" ? getSystemTheme() : theme;

        document.documentElement.setAttribute(
            "data-bs-theme",
            resolvedTheme
        );

        updateLogo(resolvedTheme);
        updateBodyClass(theme);
    };

    const getActiveTheme = () => document.documentElement.getAttribute("data-bs-theme");


    const updateActiveButton = () => {
        const storedTheme = getPreferredTheme();

        document
            .querySelectorAll("[data-bs-theme-value]")
            .forEach((el) => el.classList.remove("active"));

        const activeBtn = document.querySelector(
            `[data-bs-theme-value="${storedTheme}"]`
        );

        if (activeBtn) activeBtn.classList.add("active");
    };

    window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", () => {
        if (getPreferredTheme() === "auto") {
            applyTheme("auto");
        }
    });

    window.addEventListener("DOMContentLoaded", () => {

        applyTheme(getPreferredTheme());
        updateActiveButton();

        document.querySelectorAll("[data-bs-theme-value]").forEach((toggle) => {
            toggle.addEventListener("click", () => {
                const theme = toggle.getAttribute("data-bs-theme-value");

                setStoredTheme(theme);
                applyTheme(theme);
                updateActiveButton();
            });
        });
    });


    $('[data-width]').each(function() {
        $(this).css('width', $(this).data('width'))
    });
    $('[data-height]').each(function() {
        $(this).css('height', $(this).data('height'))
    });
    $('[data-background]').each(function() {
        $(this).css('background-image', $(this).data('background'))
    });
    $('.profile-cover-img').each(function() {
        $(this).css({
            'background-image': 'url(' + $(this).data('background') + ')',
        })
    });
    $('[data-bg-color]').each(function() {
        $(this).css('background-color', $(this).data('bg-color'))
    });
    $('[data-color]').each(function() {
        $(this).css('color', $(this).data('color'))
    });

  // password toggle
  let passwordToggle = $('.password-toggle');

  passwordToggle.each(function(){
    $(this).on('click', function(e){
        e.preventDefault();
        let closedEye = $(this).find('.close-eye');
        let openEye = $(this).find('.open-eye');
        let input = $(this).prev('input');

        if(input.attr('type') === 'password'){
            input.attr('type', 'text')
            closedEye.addClass('d-none');
            openEye.removeClass('d-none');
        } else {
            input.attr('type', 'password')
            closedEye.removeClass('d-none');
            openEye.addClass('d-none');
        }
    })
  })

    window.setBarPosition = (item, bar) => {
        const itemWidth = item.offsetWidth;
        const itemLeft = item.offsetLeft;

        bar.style.width = `${itemWidth}px`;
        bar.style.left = `${itemLeft}px`;
    }

    function initRtl() {
        // مقدار رو از حافظه بگیر
        const isRtl = localStorage.getItem('isRtl');

        // اگر مقدار دقیقاً 'false' نبود (یعنی یا true هست یا بار اوله که وارد سایت شده)، RTL رو فعال کن
        if (isRtl !== 'false') {
            $('html').attr('dir', 'rtl');
            $('#bootstrap-css').attr('href', 'assets/css/bootstrap-rtl.css');
            // برای اطمینان، مقدار رو ذخیره کن (اختیاری)
            if(isRtl === null) localStorage.setItem('isRtl', 'true');
        } else {
            $('html').attr('dir', 'ltr');
            $('#bootstrap-css').attr('href', 'assets/css/bootstrap.css');
        }
    }
    initRtl();

    $(document).on('click', '.app-set-ltr', function() {
        $('html').attr('dir', 'ltr');
        $('#bootstrap-css').attr('href', 'assets/css/bootstrap.css');
        localStorage.setItem('isRtl', 'false');
    });

    $(document).on('click', '.app-set-rtl', function() {
        $('html').attr('dir', 'rtl');
        $('#bootstrap-css').attr('href', 'assets/css/bootstrap-rtl.css');
        localStorage.setItem('isRtl', 'true');
    });
   

    document.querySelectorAll('.pure-slideable-tab-wrapper').forEach((wrapper, index) => {
        const activeItem = wrapper.querySelector('.pure-slide-tab-item.active');
        const bar = wrapper.querySelector('.pure-slide-tab-bar');
        
        window.setBarPosition(activeItem, bar);
        wrapper.addEventListener('resize', () => {
            window.setBarPosition(activeItem, bar);
        });
    });


    document.querySelectorAll('.pure-slideable-tab-wrapper .pure-slide-tab-item').forEach((item, index) => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const wrapper = this.closest('.pure-slideable-tab-wrapper');
            const bar = wrapper.querySelector('.pure-slide-tab-bar');
            const activeItem = wrapper.querySelector('.pure-slide-tab-item.active');

            activeItem.classList.remove('active');
            this.classList.add('active');
            
            window.setBarPosition(this, bar);
        });
    });


    if($('.table-check-parent').length > 0 && $('.select-all').length > 0 && $('.row-check').length > 0) {
        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll(".table-check-parent").forEach(parent => {
                const selectAll = parent.querySelector(".select-all");
                const rowChecks = parent.querySelectorAll(".row-check");
    
                const updateSelectAllState = () => {
                    const total = rowChecks.length;
                    const checkedCount = parent.querySelectorAll(".row-check:checked").length;
    
                    if (checkedCount === total && total > 0) {
                        selectAll.checked = true;
                        selectAll.indeterminate = false;
                    } else if (checkedCount > 0) {
                        selectAll.checked = false;
                        selectAll.indeterminate = true;
                    } else {
                        selectAll.checked = false;
                        selectAll.indeterminate = false;
                    }
                };
    
                updateSelectAllState();
    
                selectAll.addEventListener("change", () => {
                    rowChecks.forEach(chk => chk.checked = selectAll.checked);
                    updateSelectAllState();
                });
    
                rowChecks.forEach(chk => {
                    chk.addEventListener("change", updateSelectAllState);
                });
            });
        });
    }

    $('.notification-remove-btn').on('click', function(e) {
        e.preventDefault();
        $(this).closest('.notification-list-item').remove();
    });

    $('#footer-year').text(new Date().getFullYear());

}(jQuery))

