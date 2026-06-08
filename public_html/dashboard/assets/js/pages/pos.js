(function($) {
    'use strict';
    
$(function() {

  const select2 = $('.conca-select2');

  if (select2.length) {
      select2.each(function () {
      var $this = $(this);
      $this.wrap('<div class="position-relative"></div>').select2({
        placeholder: 'Select value',
        dropdownParent: $this.parent()
      });
    });
  }

  $(document).on('click', '.conca-btn-increase', function () {
      const group = $(this).closest('.conca-qty-group');
      const input = group.find('.conca-qty-input');
      let current = parseInt(input.val()) || 0;

      input.val(current + 1).trigger('change');
  });

  $(document).on('click', '.conca-btn-decrease', function () {
      const group = $(this).closest('.conca-qty-group');
      const input = group.find('.conca-qty-input');
      let current = parseInt(input.val()) || 0;
      const min = parseInt(input.attr('min')) || 1;

      if (current > min) {
          input.val(current - 1).trigger('change');
      }
  });

  $(document).on('input', '.conca-qty-input', function () {
      const min = parseInt($(this).attr('min')) || 1;
      if ($(this).val() < min) {
          $(this).val(min);
      }
  });


  $(document).on('click', '.pos-product-add-btn', function (e) {
      e.preventDefault();

      const card = $(this).closest('.pos-product-card');
      const title = card.find('.pos-product-title').text().trim();
      const price = card.find('.text-primary').text().trim();
      const imageSrc = card.find('img').attr('src');

      const modal = $('#posProductModal');

      modal.find('.modal-body img').attr('src', imageSrc);
      modal.find('.modal-body h6').text(title);
      modal.find('.modal-body .text-primary').text(price);
      modal.find('.conca-qty-input').val(1);

      modal.modal('show');
  });


})

}(jQuery))