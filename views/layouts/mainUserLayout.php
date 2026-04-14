<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" rel="stylesheet">

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

  <!-- Bootstrap JS (opcional, necesario si usas componentes como dropdown/carousel con JS) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
          integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
          crossorigin="anonymous"></script>

  <!-- JS por página (opcional): string o array -->
  <?php if (!empty($pageJs)): ?>
    <?php foreach ((array)$pageJs as $jsFile): ?>
      <script src="<?= htmlspecialchars($jsFile, ENT_QUOTES, 'UTF-8') ?>"></script>
    <?php endforeach; ?>
  <?php endif; ?>

</body>
</html>