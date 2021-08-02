(function($, drupalSettings) {
Drupal.behaviors.ViewsExtend = {
  attach: function(context) {
    $('form.views-exposed-form.auto-hide-submit-buttons input.form-submit').each(function() {
      $(this).addClass('visually-hidden');
    });
  }
}
})(jQuery);
