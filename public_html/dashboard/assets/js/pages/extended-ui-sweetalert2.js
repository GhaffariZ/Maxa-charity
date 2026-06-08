(function($) {
  'use strict'


  // Basic Alert
  $('#basic-alert').on('click', function() {
    Swal.fire({
      title: 'به داشبورد کنکا خوش آمدید',
      confirmButtonText: 'باشه',

      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    })
  })

  // Alert with Title
  $('#with-title-alert').on('click', function() {
    Swal.fire({
      title: 'مطمئنی؟',
      text: 'دیگه نمی‌تونید این فایل خیالی رو برگردونید!',
      confirmButtonText: 'باشه',
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    })
  })

  // Alert with Footer
  $('#with-footer-alert').on('click', function() {
    Swal.fire({
      icon: 'error',
      title: 'اوه...',
      text: 'یه مشکلی پیش اومده!',
      footer: '<a href="#" target="_blank">لطفا از سایت ما بازدید کنید</a>',
      confirmButtonText: 'باشه',
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    })
  })

  // Alert with HTML
  $('#basic-html-alert').on('click', function() {
    Swal.fire({
      title: '<h6>مثال HTML</h6>',
      icon: 'info',
      html: `می‌توانید از <b>متن بولد</b>، <i>متن ایتالیک</i>، <a href="#" target="_blank">لینک‌ها</a> و سایر تگ‌های HTML استفاده کنید`,
      showCloseButton: true,
      showCancelButton: false,
      focusConfirm: false,
      confirmButtonText: 'متوجه شدم',
      customClass: {
        confirmButton: 'btn btn-primary',
      },
      buttonsStyling: false
    })
  })


  /*
  Alert Position
  -------------------------------------------------------------
  */

  // Alert Position Top Start
  $('#alert-top-start').on('click', function() {
    Swal.fire({
      position: 'top-start',
      icon: 'success',
      title: 'شما برای بالا سمت راست کلیک کردید',
      showConfirmButton: false,
      timer: 1500
    })
  })

  // Alert Position Top End
  $('#alert-top-end').on('click', function() {
    Swal.fire({
      position: 'top-end',
      icon: 'success',
      title: 'شما برای بالا سمت چپ کلیک کردید',
      showConfirmButton: false,
      timer: 1500
    })
  })

  // Alert Position Bottom Start
  $('#alert-bottom-start').on('click', function() {
    Swal.fire({
      position: 'bottom-start',
      icon: 'success',
      title: 'شما برای پایین سمت راست کلیک کردید',
      showConfirmButton: false,
      timer: 1500
    })
  })

  // Alert Position Bottom End
  $('#alert-bottom-end').on('click', function() {
    Swal.fire({
      position: 'bottom-end',
      icon: 'success',
      title: 'شما برای پایین سمت چپ کلیک کردید',
      showConfirmButton: false,
      timer: 1500
    })
  })

  /*
  Alert Types
  -------------------------------------------------------------
  */

  // Alert Type Success
  $('#alert-type-success').on('click', function() {
    Swal.fire({
      title: 'عالیه!',
      text: 'شما ۱۰۰ امتیاز کسب کردید',
      icon: 'success',
      confirmButtonText: 'باشه',
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    })
  })

  // Alert Type Error
  $('#alert-type-error').on('click', function() {
    Swal.fire({
      title: 'اوه...',
      text: 'یه مشکلی پیش اومده!',
      icon: 'error',
      confirmButtonText: 'باشه',
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    })
  })

  // Alert Type Warning
  $('#alert-type-warning').on('click', function() {
    Swal.fire({
      title: 'مطمئنی؟',
      text: 'دیگه نمی‌تونید این فایل خیالی رو برگردونید!',
      icon: 'warning',
      confirmButtonText: 'باشه',
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    })
  })

  // Alert Type Info
  $('#alert-type-info').on('click', function() {
    Swal.fire({
      title: 'اطلاعات',
      text: 'این یه پیام حاوی اطلاعاته',
      icon: 'info',
      confirmButtonText: 'باشه',
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    })
  })

  // Alert Type Question
  $('#alert-type-question').on('click', function() {
    Swal.fire({
      title: 'رنگ مورد علاقت چیه؟',
      text: 'می‌توانید بین قرمز، آبی، سبز، زرد، صورتی و بنفش انتخاب کنید',
      icon: 'question',
      confirmButtonText: 'باشه',
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    })
  })

  /*
  Alert Animation
  -------------------------------------------------------------
  */

  $('#alert-fade-in-animation').on('click', function() {
    Swal.fire({
      title: 'Fade In Animation',
      showClass: {
        popup: 'animate__animated animate__fadeIn'
      },
      confirmButtonText: 'باشه',
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    })
  })

  $('#alert-back-in-down-animation').on('click', function() {
    Swal.fire({
      title: 'Fade Up Animation',
      showClass: {
        popup: 'animate__animated animate__backInDown'
      },
      confirmButtonText: 'باشه',
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    })
  })

  $('#alert-rotate-in-animation').on('click', function() {
    Swal.fire({
      title: 'Rotate In Animation',
      showClass: {
        popup: 'animate__animated animate__rotateIn'
      },
      confirmButtonText: 'باشه',
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    })
  })

  $('#alert-roll-in-animation').on('click', function() {
    Swal.fire({
      title: 'Roll In Animation',
      showClass: {
        popup: 'animate__animated animate__rollIn'
      },
      confirmButtonText: 'باشه',
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    })
  })

  $('#alert-jack-in-the-box-animation').on('click', function() {
    Swal.fire({
      title: 'Jack In The Box Animation',
      showClass: {
        popup: 'animate__animated animate__jackInTheBox'
      },
      confirmButtonText: 'باشه',
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    })
  })

  $('#alert-zoom-in-animation').on('click', function() {
    Swal.fire({
      title: 'Zoom In Animation',
      showClass: {
        popup: 'animate__animated animate__zoomIn'
      },
      confirmButtonText: 'باشه',
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    })
  })

  $('#alert-bounce-in-animation').on('click', function() {
    Swal.fire({
      title: 'Bounce In Animation',
      showClass: {
        popup: 'animate__animated animate__bounceIn'
      },
      confirmButtonText: 'باشه',
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    })
  })

  $('#alert-flip-in-animation').on('click', function() {
    Swal.fire({
      title: 'Flip In Animation',
      showClass: {
        popup: 'animate__animated animate__flipInX'
      },
      confirmButtonText: 'باشه',
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    })
  })

  $('#alert-tada-animation').on('click', function() {
    Swal.fire({
      title: 'Tada Animation',
      showClass: {
        popup: 'animate__animated animate__tada'
      },
      confirmButtonText: 'باشه',
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    })
  })

  $('#alert-shake-animation').on('click', function() {
    Swal.fire({
      title: 'Shake Animation',
      showClass: {
        popup: 'animate__animated animate__shakeX'
      },
      confirmButtonText: 'باشه',
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    })
  })


  /*
  Alert Options
  -------------------------------------------------------------
  */
  $('#alert-custom-image').on('click', function() {
    let img = $(this).data('img');
    let imgAlt = $(this).data('img-alt');

    Swal.fire({
      title: 'تصویر دلخواه',
      text: 'هر تصویری بخوای میتونی بزاری',
      imageUrl: img,
      imageWidth: 200,
      imageHeight: 200,
      imageAlt: imgAlt,
      confirmButtonText: 'باشه',
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    })
  })

  $('#alert-ajax-request').on('click', function() {
    Swal.fire({
      title: 'نام کاربری گیت هاب خود را وارد کنید',
      input: 'text',
      inputAttributes: {
        autocapitalize: 'off',
        placeholder: 'نام کاربری',
        className: 'form-control'
      },
      showCancelButton: true,
      confirmButtonText: 'ثبت',
      cancelButtonText: 'لغو',
      showLoaderOnConfirm: true,
      customClass: {
        confirmButton: 'btn btn-primary me-3',
        cancelButton: 'btn btn-label-danger'
      },
      preConfirm: (login) => {
        return fetch(`//api.github.com/users/${login}`)
          .then(response => {
            if (!response.ok) {
              throw new Error(response.statusText)
            }
            return response.json()
          })
          .catch(error => {
            Swal.showValidationMessage(
              `Request failed: ${error}`
            )
          })
      },
      allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: `آواتار${result.value.login}`,
          confirmButtonText: 'باشه',
          imageUrl: result.value.avatar_url
        })
      }
    })
  })

  $('#alert-outside-click').on('click', function() {
    Swal.fire({
      title: 'بیرون رو کلیک کنی هم بسته میشه.',
      allowOutsideClick: true,
      confirmButtonText: 'باشه',
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    })
  })

  $('#alert-auto-close').on('click', function() {
    var timerInterval;
    Swal.fire({
      title: 'خودکار بسته میشه!',
      html: 'من تا <strong></strong> ثانیه دیگه بسته میشم.',
      timer: 5000,
      timerProgressBar: true,
      didOpen: () => {
        Swal.showLoading()
        const b = Swal.getHtmlContainer().querySelector('strong')
        timerInterval = setInterval(() => {
          b.textContent = (Swal.getTimerLeft() / 1000).toFixed(0)
        }, 100)
        
      },
      willClose: () => {
        clearInterval(timerInterval)
      }
    }).then((result) => {
      if (result.dismiss === Swal.DismissReason.timer) {
        console.log('با پایان تایمر بسته شدم!')
      }
    })
  })

  $('#alert-confirm-cancel-action').on('click', function(){
    Swal.fire({
      title: 'مطمئنی؟',
      text: "میخوای پاکش کنی؟",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'آره',
      cancelButtonText: 'نه',
      customClass: {
        confirmButton: 'btn btn-primary me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
      if (result.value) {
        Swal.fire({
          icon: 'success',
          title: 'پاک شد!',
          text: 'فایلت با موفقیت پاک شد!',
          confirmButtonText: 'باشه',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'کنسله!',
          text: 'فایلت همچنان وجود داره.',
          icon: 'error',
          confirmButtonText: 'باشه',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  })

}(jQuery))