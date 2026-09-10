/* LifeLink — global UI behaviour. jQuery is loaded before this file. */
(function ($) {
  'use strict';

  $(function () {
    // Dismissible alerts / flash messages
    $(document).on('click', '[data-dismiss]', function () {
      $(this).closest('.alert').slideUp(120, function () {
        $(this).remove();
      });
    });

    // Auto-dismiss success flashes after a few seconds
    window.setTimeout(function () {
      $('[data-flash-region] .alert-success').slideUp(150, function () {
        $(this).remove();
      });
    }, 6000);

    // Generic dropdown toggling: [data-dropdown-toggle="#id"]
    $(document).on('click', '[data-dropdown-toggle]', function (e) {
      e.stopPropagation();
      var target = $($(this).data('dropdown-toggle'));
      $('[data-dropdown]').not(target).addClass('hidden');
      target.toggleClass('hidden');
    });
    $(document).on('click', function () {
      $('[data-dropdown]').addClass('hidden');
    });
    $(document).on('click', '[data-dropdown]', function (e) {
      e.stopPropagation();
    });
  });
})(jQuery);
