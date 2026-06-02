<?php
use app\Core\Flash;

$adminToasts = Flash::getAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($page_title) ? htmlspecialchars($page_title) : 'Admin Panel' ?></title>

  <?php require __DIR__ . "../../../shared/partials/layout_top.php"; ?>

  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

  <?php require __DIR__ . "../../../shared/partials/layout_side.php"; ?>

  {{content}}

  <?php require __DIR__ . "../../../shared/partials/layout_bottom.php"; ?>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (window.lucide) {
        lucide.createIcons();
      }
    });
  </script>

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
</body>
</html>