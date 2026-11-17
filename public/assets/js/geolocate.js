/* Reusable "use my location" button.
   Markup contract: a button with [data-geolocate], and two sibling/related
   hidden inputs referenced by data-lat-target / data-lng-target (CSS
   selectors), plus an optional [data-geolocate-status] element for feedback. */
(function ($) {
  'use strict';

  $(document).on('click', '[data-geolocate]', function () {
    var $btn = $(this);
    var $lat = $($btn.data('lat-target'));
    var $lng = $($btn.data('lng-target'));
    var $status = $($btn.data('status-target'));

    if (!navigator.geolocation) {
      $status.text('Location is not supported by this browser.').removeClass('hidden text-gray-500').addClass('text-danger-600');
      return;
    }

    var original = $btn.html();
    $btn.prop('disabled', true).html('<i class="ti ti-loader-2 animate-spin"></i><span>Locating…</span>');

    navigator.geolocation.getCurrentPosition(
      function (pos) {
        $lat.val(pos.coords.latitude.toFixed(7));
        $lng.val(pos.coords.longitude.toFixed(7));
        $status
          .text('Location set (±' + Math.round(pos.coords.accuracy) + 'm). This powers nearest-donor matching.')
          .removeClass('hidden text-danger-600').addClass('text-success-600');
        $btn.prop('disabled', false).html(original);
      },
      function (err) {
        var msg = err.code === err.PERMISSION_DENIED
          ? 'Location permission denied. You can still submit without it.'
          : 'Could not get your location. You can still submit without it.';
        $status.text(msg).removeClass('hidden text-success-600').addClass('text-danger-600');
        $btn.prop('disabled', false).html(original);
      },
      { enableHighAccuracy: true, timeout: 10000 },
    );
  });
})(jQuery);
