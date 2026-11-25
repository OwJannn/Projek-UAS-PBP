<?php
$flash = get_flash();
if ($flash):
    $allowedTypes = ['primary', 'secondary', 'success', 'danger', 'warning', 'info'];
    $type = in_array($flash['type'] ?? '', $allowedTypes, true) ? $flash['type'] : 'info';
?>
<div class="alert alert-<?= e($type) ?> alert-dismissible fade show" role="alert">
    <?= e($flash['message'] ?? '') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
