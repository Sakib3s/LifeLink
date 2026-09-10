<?= $this->partial('public/_status', [
    'code'    => 403,
    'heading' => 'Access denied',
    'body'    => 'You do not have permission to view this page with your current account.',
    'icon'    => 'lock',
]) ?>
