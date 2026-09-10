<?php /** @var int $status @var string $message */ ?>
<?= $this->partial('public/_status', [
    'code'    => $status ?? 500,
    'heading' => 'Something went wrong',
    'body'    => $message !== '' ? $message : 'An unexpected error occurred. Please try again in a moment.',
    'icon'    => 'alert-triangle',
]) ?>
