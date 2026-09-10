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

    // Mobile sidebar slide-over
    var $sidebar = $('[data-sidebar]');
    var $overlay = $('[data-sidebar-overlay]');
    function openSidebar() {
      $sidebar.removeClass('-translate-x-full');
      $overlay.removeClass('hidden');
    }
    function closeSidebar() {
      $sidebar.addClass('-translate-x-full');
      $overlay.addClass('hidden');
    }
    $(document).on('click', '[data-sidebar-toggle]', function (e) {
      e.stopPropagation();
      if ($sidebar.hasClass('-translate-x-full')) { openSidebar(); } else { closeSidebar(); }
    });
    $(document).on('click', '[data-sidebar-overlay]', closeSidebar);

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
