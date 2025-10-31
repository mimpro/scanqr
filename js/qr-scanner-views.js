(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.scanqrViews = {
    attach: function (context) {
      $('form.views-exposed-form', context).once('scanqr-views').each(function () {
        var $form = $(this);

        $form.find('[data-views-autosubmit="1"]', context).each(function () {
          var $wrapper = $(this);
          // Locate actual input inside wrapper, or use wrapper if it's an input.
          var $input = $wrapper.is('input,textarea,select') ? $wrapper :
            $wrapper.find('input[type="text"],input[type="search"],textarea,select').first();

          if ($input.length === 0) {
            return;
          }

          var submitForm = function () {
            var val = $input.val();
            if (val && String(val).length > 0) {
              var $btn = $form.find('.form-submit, input[type="submit"]').first();
              if ($btn.length) {
                $btn.trigger('click');
              }
              else {
                $form.trigger('submit');
              }
            }
          };

          // Fire when scanner populates the input or triggers our custom event.
          $input.on('input change scanqr:detected', submitForm);
        });
      });
    }
  };

})(jQuery, Drupal);
