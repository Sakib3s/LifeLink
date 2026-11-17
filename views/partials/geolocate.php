<?php
/**
 * "Use my current location" button + hidden lat/lng inputs + status text.
 * Drop inside a <form>; submits latitude/longitude alongside the rest.
 *
 * @var string $latName   input name for latitude  (default "latitude")
 * @var string $lngName   input name for longitude (default "longitude")
 * @var mixed  $latValue
 * @var mixed  $lngValue
 */
$latName = $latName ?? 'latitude';
$lngName = $lngName ?? 'longitude';
$latValue = $latValue ?? old($latName, '');
$lngValue = $lngValue ?? old($lngName, '');
$uid = 'geo-' . substr(md5($latName . $lngName), 0, 8);
?>
<div class="rounded-md border border-gray-200 bg-gray-50 p-3">
    <input type="hidden" id="<?= e($uid) ?>-lat" name="<?= e($latName) ?>" value="<?= e($latValue) ?>">
    <input type="hidden" id="<?= e($uid) ?>-lng" name="<?= e($lngName) ?>" value="<?= e($lngValue) ?>">

    <button type="button" class="btn btn-sm btn-secondary"
            data-geolocate
            data-lat-target="#<?= e($uid) ?>-lat"
            data-lng-target="#<?= e($uid) ?>-lng"
            data-status-target="#<?= e($uid) ?>-status">
        <?= icon('map-pin') ?><span>Use my current location</span>
    </button>
    <p id="<?= e($uid) ?>-status" class="mt-2 text-xs <?= $latValue !== '' ? 'text-success-600' : 'text-gray-500' ?>">
        <?= $latValue !== '' ? 'Location set. This powers nearest-donor matching.' : 'Optional, but strongly recommended — distance-based matching needs it.' ?>
    </p>
</div>
