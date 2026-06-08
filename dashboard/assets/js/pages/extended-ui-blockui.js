(function($) {
    'use strict';
    
    $(function() {

      $('.btn-page-block').on('click', function () {
          $.blockUI({
            message: '<div class="spinner-border text-primary" role="status"></div>',
            timeout: 1000,
            css: {
              backgroundColor: 'transparent',
              border: '0'
            },
            overlayCSS: {
              opacity: 0.5
            }
          });
      });

      $('.btn-page-block-overlay').on('click', function () {
          $.blockUI({
            message: '<div class="spinner-border text-primary" role="status"></div>',
            timeout: 1000,
            css: {
              backgroundColor: 'transparent',
              color: 'var(--bs-card-bg)',
              border: '0'
            },
            overlayCSS: {
              backgroundColor: 'var(--bs-card-bg)',
              opacity: 0.8
            }
          });
      });

      $('.btn-page-block-custom').on('click', function () {
          $.blockUI({
            message: '<div class="d-flex justify-content-center"><p class="mb-0 text-white fs-4">لطفا صبرکنید...</p></div>',
            timeout: 1000,
            css: {
              backgroundColor: 'transparent',
              border: '0'
            },
            overlayCSS: {
              backgroundColor: '#000',
              opacity: 0.8
            }
          });
      });

      $('.btn-page-block-multiple').on('click', function () {
        $.blockUI({
          message:
            '<div class="d-flex justify-content-center"><p class="mb-0 text-white fs-4">لطفا صبرکنید...</p></div>',
          css: {
            backgroundColor: 'transparent',
            color: '#fff',
            border: '0'
          },
          overlayCSS: {
            opacity: 0.5
          },
          timeout: 1000,
          onUnblock: function () {
            $.blockUI({
              message: '<p class="mb-0 text-white fs-4">تقریبا انجام شده..</p>',
              timeout: 1000,
              css: {
                backgroundColor: 'transparent',
                border: '0'
              },
              overlayCSS: {
                opacity: 0.25
              },
              onUnblock: function () {
                $.blockUI({
                  message: '<div class="p-3 bg-success rounded text-white">موفقیت آمیز بود.</div>',
                  timeout: 500,
                  css: {
                    backgroundColor: 'transparent',
                    border: '0'
                  },
                  overlayCSS: {
                    opacity: 0.25
                  }
                });
              }
            });
          }
        });

      })

      let formBlock = $('#form-block');

      $('.btn-form-block').on('click', function () {
        formBlock.block({
          message: '<div class="spinner-border text-primary" role="status"></div>',
          timeout: 1000,
          css: {
            backgroundColor: 'transparent',
            color: 'var(--bs-card-bg)',
            border: '0'
          },
          overlayCSS: {
            opacity: 0.5
          }
        });
      });

      $('.btn-form-block-overlay').on('click', function () {
        formBlock.block({
          message: '<div class="spinner-border text-primary" role="status"></div>',
          timeout: 1000,
          css: {
            backgroundColor: 'transparent',
            color: 'var(--bs-card-bg)',
            border: '0'
          },
          overlayCSS: {
            backgroundColor: 'var(--bs-card-bg)',
            opacity: 0.8
          }
        });
      });

      $('.btn-form-block-custom').on('click', function () {
        formBlock.block({
          message: '<div class="d-flex justify-content-center"><p class="mb-0 text-white fs-4">لطفا صبرکنید...</p></div>',
          timeout: 1000,
          css: {
            backgroundColor: 'transparent',
            color: '#fff',
            border: '0'
          },
          overlayCSS: {
            backgroundColor: '#000',
            opacity: 0.8
          }
        });
      });

      $('.btn-form-block-multiple').on('click', function () {
        formBlock.block({
          message:
            '<div class="d-flex justify-content-center"><p class="mb-0 text-white fs-4">لطفا صبرکنید...</p></div>',
          css: {
            backgroundColor: 'transparent',
            color: 'var(--bs-card-bg)',
            border: '0'
          },
          overlayCSS: {
            opacity: 0.5
          },
          timeout: 1000,
          onUnblock: function () {
            formBlock.block({
              message: '<p class="mb-0 text-white fs-4">تقریبا انجام شده..</p>',
              timeout: 1000,
              css: {
                backgroundColor: 'transparent',
                color: 'var(--bs-card-bg)',
                border: '0'
              },
              overlayCSS: {
                opacity: 0.25
              },
              onUnblock: function () {
                formBlock.block({
                  message: '<div class="p-3 bg-success rounded text-white">موفقیت آمیز بود.</div>',
                  timeout: 500,
                  css: {
                    backgroundColor: 'transparent',
                    color: 'var(--bs-card-bg)',
                    border: '0'
                  },
                  overlayCSS: {
                    opacity: 0.25
                  }
                });
              }
            });
          }
        });
      })

    })
    
}(jQuery) )