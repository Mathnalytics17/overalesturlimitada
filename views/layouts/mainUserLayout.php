<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="/styles/vendor/bootstrap.min.css">
<link rel="stylesheet" href="/styles/vendor/all.min.css">
<script src="/js/vendor/bootstrap.bundle.min.js"></script>

  <!-- CSS global (componentes) -->
  <link rel="stylesheet" href="/styles/header.css">
  <link rel="stylesheet" href="/styles/footer.css">

  <!-- CSS por página (opcional): string o array -->
  <?php if (!empty($pageCss)): ?>
    <?php foreach ((array)$pageCss as $cssFile): ?>
      <link rel="stylesheet" href="<?= htmlspecialchars($cssFile, ENT_QUOTES, 'UTF-8') ?>">
    <?php endforeach; ?>
  <?php endif; ?>

  <title><?= isset($title) ? htmlspecialchars($title, ENT_QUOTES, 'UTF-8') : 'Document' ?></title>
</head>
<body>

  <?php include_once __DIR__ . '/../../shared/header.php'; ?>

  {{content}}

  <?php include_once __DIR__ . '/../../shared/footer.php'; ?>



  <!-- JS por página (opcional): string o array -->
  <?php if (!empty($pageJs)): ?>
    <?php foreach ((array)$pageJs as $jsFile): ?>
      <script src="<?= htmlspecialchars($jsFile, ENT_QUOTES, 'UTF-8') ?>"></script>
    <?php endforeach; ?>
  <?php endif; ?>

</body>
</html>