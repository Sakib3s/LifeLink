/* Donor-only AJAX: availability toggle + match accept/decline.
   Loaded only on donor pages via $pageScripts. */
(function ($) {
  'use strict';

  var base = $('meta[name="app-base"]').attr('content') || '';

  function escapeHtml(str) {
    return $('<div>').text(str == null ? '' : str).html();
  }

  // ── Availability toggle (donor profile) ─────────────────────────
  $(document).on('click', '[data-availability-toggle]', function () {
    var $btn = $(this);
    $btn.prop('disabled', true);

    $.post(base + '/donor/availability')
      .done(function (res) {
        var available = res.status === 'available';
        $('[data-availability-label]')
          .text(available ? 'Available' : 'Unavailable')
          .toggleClass('badge-success', available)
          .toggleClass('badge-neutral', !available);
        $btn
          .find('[data-availability-action]')
          .text(available ? 'Mark unavailable' : 'Mark available');
      })
      .fail(function () {
        alert('Could not update availability. Please try again.');
      })
      .always(function () {
        $btn.prop('disabled', false);
      });
  });

  // ── Match accept / decline (donor requests inbox) ───────────────
  function respond($card, action) {
    var id = $card.data('match-id');

    $card.find('[data-match-actions] button').prop('disabled', true);

    $.post(base + '/donor/matches/' + id + '/' + action)
      .done(function (res) {
        $card.find('[data-match-status]')
          .removeClass('badge-warning badge-success badge-danger')
          .addClass(action === 'accept' ? 'badge-success' : 'badge-danger')
          .html((action === 'accept' ? '<i class="ti ti-check"></i>' : '<i class="ti ti-x"></i>') + ' ' + (action === 'accept' ? 'Accepted' : 'Declined'));

        $card.find('[data-match-actions]').remove();

        if (action === 'accept' && res.contact) {
          var c = res.contact;
          var html = '<p class="font-medium text-gray-900"><i class="ti ti-user text-gray-400"></i> ' + escapeHtml(c.name) + '</p>'
            + '<p class="mt-1 text-gray-600"><i class="ti ti-phone text-gray-400"></i> ' + escapeHtml(c.phone || 'Not provided') + '</p>'
            + '<p class="mt-1 text-gray-600"><i class="ti ti-map-pin text-gray-400"></i> ' + escapeHtml(c.address) + ', ' + escapeHtml(c.city) + '</p>';
          $card.find('[data-contact-block]').html(html).removeAttr('hidden').removeClass('hidden');
        }
      })
      .fail(function (xhr) {
        var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Something went wrong. Please refresh and try again.';
        alert(msg);
        $card.find('[data-match-actions] button').prop('disabled', false);
      });
  }

  $(document).on('click', '[data-match-accept]', function () {
    respond($(this).closest('[data-match-id]'), 'accept');
  });

  $(document).on('click', '[data-match-decline]', function () {
    if (!window.confirm('Decline this request? The patient will not be notified, but this match will be closed.')) {
      return;
    }
    respond($(this).closest('[data-match-id]'), 'decline');
  });
})(jQuery);
