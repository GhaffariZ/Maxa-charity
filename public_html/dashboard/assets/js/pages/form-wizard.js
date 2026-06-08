( function ( $ ) {
    'use strict';
  
    $(function() {
        let wz_class = ".wizard";
        
        // تشخیص جهت صفحه (RTL یا LTR)
        const isRtl = $('html').attr('dir') === 'rtl' || $('body').css('direction') === 'rtl';

        const args = {
          "wz_class": wz_class,
          "wz_nav_style": "dots",
          "wz_button_style": ".btn .btn-sm .mx-1",
          "wz_ori": "horizontal",
          "buttons": true,
          "navigation": "all",
          
          // تنظیمات فارسی‌سازی
          "prev": "قبلی",
          "next": "بعدی",
          "finish": "ثبت نهایی", 
          
          // فعال‌سازی حالت راست‌چین بر اساس وضعیت صفحه
          "rtl": isRtl, 
          
          "bubble": true,
          validate: true,
        };
        
        const wizard = new Wizard(args);
        wizard.init();
        
        let $wz_doc = document.querySelector(wz_class);
        
        // هندل کردن سابمیت فرم
        if ($wz_doc) {
            $wz_doc.addEventListener("wz.form.submit", function (e) {
                alert("فرم با موفقیت ارسال شد");
            });
        }
    });
  
}(jQuery) )