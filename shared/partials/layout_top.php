<?php
  // Variables opcionales por página:
  // $page_title = "Dashboard";
  // $page_subtitle = "Resumen general";
  // $active = "dashboard"|"leads"|"sales"|"users"|"packages"|"docs";
  $page_title = $page_title ?? "Panel";
  $page_subtitle = $page_subtitle ?? "Bienvenido";
  $active = $active ?? "dashboard";
  $user_name = $user_name ?? "Luis";
  $user_role = $user_role ?? "Administrador";
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo htmlspecialchars($page_title); ?></title>
  <link rel="stylesheet" href="/public/styles/admin.css" />
</head>
<body>
<div class="app">