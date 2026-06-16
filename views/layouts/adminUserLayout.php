<?php
use app\Core\Flash;

$adminToasts = Flash::getAll();

require __DIR__ . "/../../shared/partials/layout_top.php";
require __DIR__ . "/../../shared/partials/layout_side.php";
?>

{{content}}

<div id="admin-toast-container" class="admin-toast-container"></div>

<?php if (!empty($adminToasts)): ?>
<script>
  window.ADMIN_FLASH_TOASTS = <?= json_encode($adminToasts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>
<?php else: ?>
<script>
  window.ADMIN_FLASH_TOASTS = [];
</script>
<?php endif; ?>

<script src="/js/admin-toasts.js"></script>

<?php require __DIR__ . "/../../shared/partials/layout_bottom.php"; ?>
