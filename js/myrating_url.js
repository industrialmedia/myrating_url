(function ($, window, Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.myrating_url = {
    attach: function (context, settings) {


      //========================================================================
      // [[ BEGIN ]] Auto submit form
      //========================================================================
      $('form.myrating-url-form', context).once('myrating_url').each(function () {
        var $form = $(this);
        $form.find('select').change(function () {
          $form.find('input[type="submit"]').click();
        });
      });
      // [[ END ]]
      //========================================================================


    }
  };
})(jQuery, window, Drupal, drupalSettings);
