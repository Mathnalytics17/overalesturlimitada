<?php
  // Variables opcionales por página:
  // $page_title = "Dashboard";
  // $page_subtitle = "Resumen general";
  // $active = "dashboard"|"leads"|"sales"|"users"|"packages"|"package_tags"|"docs";
  $page_title = $page_title ?? "Panel";
  $page_subtitle = $page_subtitle ?? "Bienvenido";

  if (!isset($active)) {
      $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/admin', PHP_URL_PATH) ?: '/admin';
      $active = match (true) {
          str_starts_with($currentPath, '/admin/leads') => 'leads',
          str_starts_with($currentPath, '/admin/sales-orders') => 'sales_orders',
          str_starts_with($currentPath, '/admin/sales') || str_starts_with($currentPath, '/admin/salesOrder') => 'sales',
          str_starts_with($currentPath, '/admin/chatbot') => 'chatbot',
          str_starts_with($currentPath, '/admin/users') => 'users',
          str_starts_with($currentPath, '/admin/packageTour/analytics') => 'package_analytics',
          str_starts_with($currentPath, '/admin/packageTour/tags') => 'package_tags',
          str_starts_with($currentPath, '/admin/packageTour') => 'packages',
          str_starts_with($currentPath, '/admin/experiences') => 'experiences',
          str_starts_with($currentPath, '/admin/pqrs') => 'pqrs',
          default => 'dashboard',
      };
  }

  $user_name = $user_name ?? "Luis";
  $user_role = $user_role ?? "Administrador";
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo htmlspecialchars($page_title); ?></title>
  <link rel="stylesheet" href="/styles/admin.css" />
  <!-- Iconos opcionales por CDN. Si no cargan, el CSS/HTML muestra fallback local. -->
  <script src="https://unpkg.com/lucide@latest" defer></script>
</head>
<body>
<div class="app">