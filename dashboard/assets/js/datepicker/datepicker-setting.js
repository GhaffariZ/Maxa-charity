(function($){

    //persian datepicker setting
    // ۱. تاریخ و زمان
    $(".p-date-time").pDatepicker({
        timePicker: { enabled: true, second: { enabled: false } },
        format: 'YYYY/MM/DD HH:mm'
    });

    // ۲. فقط تاریخ
    $(".p-date-only").pDatepicker({
        timePicker: { enabled: false },
        format: 'YYYY/MM/DD',
        autoClose: true
    });

    // ۳. فقط ماه و سال
    $(".p-month-only").pDatepicker({
        timePicker: { enabled: false },
        format: 'MMMM YYYY',
        autoClose: true,
        viewMode: 'month',
        minViewMode: 'month',
        onlySelectOnDate: false 
    });

    // ۴. فقط زمان
    $(".p-time-only").pDatepicker({
        onlyTimePicker: true,
        timePicker: { enabled: true, second: { enabled: false } },
        format: 'HH:mm',
        autoClose: true
    });

    // ۵. شروع تقویم هفته
    $(document).on('mousedown', '.week-input-target', function() {
        var $input = $(this);

        if ($input.data('calendar-linked') === true) return;

        var checkExist = setInterval(function() {
            var datePickerInstance = $input.data('datepicker');
            
            if (datePickerInstance && datePickerInstance.container) {
                var $calendar = $(datePickerInstance.container.element);
                var calendarId = $calendar.attr('id');

                if (calendarId) {
                    $("#" + calendarId).addClass("is-week-mode");
                    $input.data('calendar-linked', true);
                    
                    console.log("اتصال موفق به تقویم اختصاصی: " + calendarId);
                    clearInterval(checkExist);
                }
            } else {
                var $visibleCalendar = $(".datepicker-container").not(".pwt-hide");
                if ($visibleCalendar.length > 0) {
                    $visibleCalendar.addClass("is-week-mode");
                    $input.data('calendar-linked', true);
                    clearInterval(checkExist);
                }
            }
        }, 200);

        setTimeout(function() { clearInterval(checkExist); }, 1500);
    });

    // ۱. تنظیم دیت‌پیکر هفته
    var weekPicker = $(".week-input-target").pDatepicker({
        timePicker: { enabled: false },
        autoClose: false,
        calendar: { showWeekNumbers: true },
        formatter: function(unix) {
            var date = new persianDate(unix);
            return "هفته " + date.format('ww') + "، " + date.format('YYYY');
        }
    });

    $(document).on('click', '.is-week-mode td:not(.new):not(.old)', function() {
        var $tr = $(this).closest('tr');
        var $container = $(this).closest('.is-week-mode');

        var firstDayUnix = $tr.find('td[data-date]').first().data('date');
        if (firstDayUnix) {
            var date = new persianDate(firstDayUnix);
            var weekString = "هفته " + date.format('ww') + "، " + date.format('YYYY');

            $(".week-input-target:focus").val(weekString);
        }

        setTimeout(function() {
            $container.find('td').removeClass('selected-week');
            $tr.find('td').addClass('selected-week');
            }, 20);
    });

    $(document).on('mouseenter', '.is-week-mode td:not(.new):not(.old)', function() {
        $(this).closest('tr').find('td').addClass('hover-week');
    });

    $(document).on('mouseleave', '.is-week-mode td', function() {
        $(this).closest('tr').find('td').removeClass('hover-week');
    });
    // پایان تقویم هفته

    // شروع تقویم رنج
    $(document).on('mousedown', '.range-input-target', function() {
        var $input = $(this);

        if ($input.data('calendar-linked') === true) return;

        var checkExist = setInterval(function() {
            var datePickerInstance = $input.data('datepicker');
            
            if (datePickerInstance && datePickerInstance.container) {
                var $calendar = $(datePickerInstance.container.element);
                var calendarId = $calendar.attr('id');

                if (calendarId) {
                    // اختصاص کلاس مخصوص رنج
                    $("#" + calendarId).addClass("is-range-mode");
                    $input.data('calendar-linked', true);
                    
                    console.log("تقویم رنج متصل شد: " + calendarId);
                    clearInterval(checkExist);
                }
            } else {
                var $visibleCalendar = $(".datepicker-container").not(".pwt-hide");
                if ($visibleCalendar.length > 0) {
                    $visibleCalendar.addClass("is-range-mode");
                    $input.data('calendar-linked', true);
                    clearInterval(checkExist);
                }
            }
        }, 200);

        setTimeout(function() { clearInterval(checkExist); }, 1500);
    });

    // متغیرهایی برای نگه داشتن وضعیت رنج
    var rangeState = {
        start: null,
        end: null
    };

    // مقداردهی اولیه پلاگین
    $(".range-input-target").pDatepicker({
        timePicker: { enabled: false },
        format: 'YYYY/MM/DD',
        autoClose: false,
        onSelect: function(unix) {
            handleDateSelection(unix);
        }
    });

    // تابع اصلی مدیریت انتخاب رنج (هوشمند)
    function handleDateSelection(selectedUnix) {
        var unix = parseInt(selectedUnix);

        // ۱. منطق چرخه انتخاب
        if (!rangeState.start || (rangeState.start && rangeState.end)) {
            // کلیک اول (فرد): شروع جدید
            rangeState.start = unix;
            rangeState.end = null;
        } else {
            // کلیک دوم (زوج): مقایسه و جابه‌جایی هوشمند
            if (unix < rangeState.start) {
                // اگر دومی قبل از اولی بود، جابجا کن (هوشمند)
                rangeState.end = rangeState.start;
                rangeState.start = unix;
            } else {
                rangeState.end = unix;
            }
        }

        updateInputAndStyles();
    }

    // به‌روزرسانی اینپوت و استایل‌های بصری
    function updateInputAndStyles() {
        var startStr = rangeState.start ? new persianDate(rangeState.start).format('YYYY/MM/DD') : "";
        var endStr = rangeState.end ? new persianDate(rangeState.end).format('YYYY/MM/DD') : "";

        var $input = $(".range-input-target:focus").length ? $(".range-input-target:focus") : $(".range-input-target");

        if (rangeState.start && rangeState.end) {
            $input.val(startStr + " تا " + endStr);
        } else if (rangeState.start) {
            $input.val(startStr + " - انتخاب پایان...");
        }

        applyRangeStyles();
    }

    // اعمال کلاس‌ها (حل مشکل رندر مجدد پلاگین)
    function applyRangeStyles() {
        setTimeout(function() {
            var $container = $(".datepicker-container").not(".pwt-hide");
            
            // حذف تمام کلاس‌های قبلی
            $container.find('td').removeClass('range-start-custom range-end-custom range-between-custom');

            // اعمال کلاس مبدأ
            if (rangeState.start) {
                $container.find('td[data-date="' + rangeState.start + '"]').addClass('range-start-custom');
            }

            // اعمال کلاس مقصد و روزهای بین
            if (rangeState.end) {
                $container.find('td[data-date="' + rangeState.end + '"]').addClass('range-end-custom');

                $container.find('td[data-date]').each(function() {
                    var currentUnix = parseInt($(this).attr('data-date'));
                    if (currentUnix > rangeState.start && currentUnix < rangeState.end) {
                        $(this).addClass('range-between-custom');
                    }
                });
            }
        }, 50);
    }

    // حیاتی: اعمال مجدد استایل‌ها وقتی کاربر ماه یا سال را عوض می‌کند
    $(document).on('click', '.pwt-btn-next, .pwt-btn-prev, .month-item, .year-item', function() {
        applyRangeStyles();
    });

    // آرایه برای ذخیره تاریخ‌های انتخاب شده
    var selectedDates = [];

    // ۱. تنظیم اولیه تقویم
    $(".p-multiple-date").pDatepicker({
        timePicker: { enabled: false },
        autoClose: false,
        format: 'YYYY/MM/DD',
        onSelect: function(unix) {
            handleMultipleDates(unix);
        }
    });

    // ۲. تابع مدیریت انتخاب‌ها
    function handleMultipleDates(unix) {
        var clickedUnix = parseInt(unix);
        var index = selectedDates.indexOf(clickedUnix);

        if (index > -1) {
            // اگر قبلاً انتخاب شده بود -> حذفش کن
            selectedDates.splice(index, 1);
        } else {
            // اگر جدید است -> چک کن لیمیت ۱۰ تا پر نشده باشد
            if (selectedDates.length < 10) {
                selectedDates.push(clickedUnix);
            } else {
                alert("شما حداکثر مجاز به انتخاب ۱۰ تاریخ هستید.");
            }
        }

        updateMultipleInputAndStyles();
    }

    // ۳. به‌روزرسانی اینپوت و کلاس‌های بصری
    function updateMultipleInputAndStyles() {
        // به‌روزرسانی مقدار اینپوت (تاریخ‌ها را با کاما جدا می‌کنیم)
        var dateStrings = selectedDates.map(function(u) {
            return new persianDate(u).format('YYYY/MM/DD');
        });
        $(".p-multiple-date").val(dateStrings.join("، "));

        applyMultipleStyles();
    }

    // ۴. اعمال کلاس به خانه‌های انتخاب شده
    function applyMultipleStyles() {
        setTimeout(function() {
            var $container = $(".datepicker-container").not(".pwt-hide");
            
            // پاک کردن کلاس‌های قبلی
            $container.find('td').removeClass('selected-multiple');

            // هایلایت کردن تمام تاریخ‌های موجود در آرایه
            selectedDates.forEach(function(unix) {
                $container.find('td[data-date="' + unix + '"]').addClass('selected-multiple');
            });
        }, 50);
    }

    // ۵. حفظ کلاس‌ها هنگام تغییر ماه/سال
    $(document).on('click', '.pwt-btn-next, .pwt-btn-prev, .month-item, .year-item', function() {
        applyMultipleStyles();
    });

    // ۱. انتخاب کانتینر و اینپوت
    const $holder = $('#inline-calendar-holder');
    const $input = $(".inline-input-target");

    // ۲. اجرای پلاگین مستقیم روی DIV (این تضمین می‌کند که تقویم ساخته شود)
    $holder.pDatepicker({
        inline: true,
        autoClose: false,
        timePicker: { 
            enabled: true, 
            second: { enabled: false },
            meridian: { enabled: true }
        },
        format: 'YYYY/MM/DD HH:mm',
        // وقتی تقویم تغییر کرد، مقدار را در اینپوت بریز
        onSelect: function(unix) {
            const dateStr = new persianDate(unix).format('YYYY/MM/DD HH:mm');
            $input.val(dateStr);
        }
    });

    // ۳. پیدا کردن المان ساخته شده و حذف کلاس‌های مخفی‌ساز
    const $datepicker = $holder.find('.datepicker-container');
    
    if ($datepicker.length > 0) {
        $datepicker.removeClass('pwt-hide').show();
        
        // ناظر برای اینکه اگر پلاگین خواست مخفی‌اش کند، جلویش را بگیریم
        const observer = new MutationObserver(() => {
            if ($datepicker.hasClass('pwt-hide') || $datepicker.css('display') === 'none') {
                $datepicker.removeClass('pwt-hide').show();
            }
        });
        observer.observe($datepicker[0], { attributes: true, attributeFilter: ['class', 'style'] });
    } else {
        // اگر هنوز ساخته نشده بود (احتمال کم)، یک بار متد show را صدا بزن
        const dp = $holder.data('datepicker');
        if (dp) dp.show();
    }

}(jQuery))