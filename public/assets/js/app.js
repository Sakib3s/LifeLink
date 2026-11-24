/* LifeLink — global UI behaviour. jQuery is loaded before this file. */
(function ($) {
  'use strict';

  // Attach the CSRF token (from <meta name="csrf-token">) to every AJAX
  // write, so $.post()/$.ajax() calls pass CsrfMiddleware without each
  // caller having to remember to add it.
  var csrfToken = $('meta[name="csrf-token"]').attr('content');
  if (csrfToken) {
    $.ajaxSetup({ headers: { 'X-CSRF-Token': csrfToken } });
  }

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

    // Notification bell: load the list when opened, mark-all-read, poll the count.
    var $bell = $('[data-notifications]');
    if ($bell.length) {
      var $count = $bell.find('[data-notif-count]');
      var $list = $bell.find('[data-notif-list]');
      var baseUrl = $('meta[name="base-url"]').attr('content') || '';
      var esc = function (t) { return $('<div>').text(t).html(); };
      var setCount = function (n) {
        $count.text(n).toggleClass('hidden', !n);
      };
      var load = function () {
        $.getJSON(baseUrl + '/notifications/recent', function (res) {
          setCount(res.unread);
          if (!res.items.length) {
            $list.html('<li class="px-4 py-6 text-center text-gray-500">No notifications yet</li>');
            return;
          }
          $list.html($.map(res.items, function (n) {
            return '<li class="px-4 py-3' + (n.is_read ? '' : ' bg-brand-50') + '">' +
              '<p class="font-medium text-gray-900">' + esc(n.title) + '</p>' +
              '<p class="text-gray-600">' + esc(n.message) + '</p>' +
              '<p class="mt-1 text-xs text-gray-400">' + esc(n.time) + '</p></li>';
          }).join(''));
        }).fail(function () {
          $list.html('<li class="px-4 py-6 text-center text-gray-500">Could not load notifications</li>');
        });
      };
      $bell.on('click', '[data-dropdown-toggle]', load);
      $bell.on('click', '[data-notif-mark-all]', function () {
        $.post(baseUrl + '/notifications/read-all', function (res) {
          setCount(res.unread);
          load();
        }, 'json');
      });
      window.setInterval(function () {
        $.getJSON(baseUrl + '/notifications/recent', function (res) { setCount(res.unread); });
      }, 60000);
    }
  });
})(jQuery);
