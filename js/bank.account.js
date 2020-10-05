/**
 * @file
 * Javascript for member_photo module.
 */
(function ($) {

  Drupal.behaviors.bank_account = {
    attach: function (context) {

      // Separate credit card numbers
      $('#card-number').on('keypress change', function () {
        $(this).val(function (index, value) {
          return value.replace(/\W/gi, '').replace(/(.{4})/g, '$1 ');
        });
      });

      jQuery('.card-cont').hover(function (e) {
        if (e.type === 'mouseenter') {
          jQuery('.card-overlay', this).css('opacity', '1');
        }
        if (e.type === 'mouseleave') {
          jQuery('.card-overlay', this).css('opacity', '0');
        }
      });

    }
  };

})(jQuery);
