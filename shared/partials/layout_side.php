<?php
$currentAdmin = \app\Core\AdminAuth::user();
$user_name = $currentAdmin->full_name ?? $currentAdmin->first_name ?? 'Usuario';
$user_role = $currentAdmin->role ?? 'Administrador';
?>

<aside class="sidebar">
  <div class="brand">
    <div class="logo">OA</div>
    <div class="brand-text">
      <div class="brand-title">Over Alestur</div>
      <div class="brand-sub">Admin Panel</div>
    </div>
  </div>

  <nav class="nav" aria-label="Navegación principal del panel">
    <a class="<?= $active === 'dashboard' ? 'active' : ''; ?>" href="/admin" data-label="Dashboard" title="Dashboard" aria-label="Dashboard">
      <span class="nav-icon"><i data-lucide="layout-dashboard"></i><span class="nav-fallback">⌂</span></span>
      <span class="nav-text">Dashboard</span>
    </a>

    <a class="<?= $active === 'leads' ? 'active' : ''; ?>" href="/admin/leads" data-label="Contactos potenciales" title="Contactos potenciales" aria-label="Contactos potenciales">
      <span class="nav-icon"><i data-lucide="users"></i><span class="nav-fallback">☷</span></span>
      <span class="nav-text">Contactos potenciales</span>
    </a>

    <a class="<?= $active === 'sales' ? 'active' : ''; ?>" href="/admin/sales" data-label="Seguimiento de ventas" title="Seguimiento de ventas" aria-label="Seguimiento de ventas">
      <span class="nav-icon"><i data-lucide="trending-up"></i><span class="nav-fallback">↗</span></span>
      <span class="nav-text">Seguimiento de ventas</span>
    </a>

    <a class="<?= $active === 'sales_orders' ? 'active' : ''; ?>" href="/admin/sales-orders" data-label="Ventas / Reservas" title="Ventas / Reservas" aria-label="Ventas / Reservas">
      <span class="nav-icon"><i data-lucide="receipt-text"></i><span class="nav-fallback">▤</span></span>
      <span class="nav-text">Ventas / Reservas</span>
    </a>

    <a class="<?= $active === 'chatbot' ? 'active' : ''; ?>" href="/admin/chatbot" data-label="Chatbot WhatsApp" title="Chatbot WhatsApp" aria-label="Chatbot WhatsApp">
      <span class="nav-icon"><i data-lucide="message-circle"></i><span class="nav-fallback">◌</span></span>
      <span class="nav-text">Chatbot WhatsApp</span>
    </a>

<?php $currentAdmin = \app\Core\AdminAuth::user(); ?>
<?php if ($currentAdmin && $currentAdmin->can('manage_users')): ?>
    <a class="<?= $active === 'users' ? 'active' : ''; ?>" href="/admin/users" data-label="Usuarios" title="Usuarios" aria-label="Usuarios">
      <span class="nav-icon"><i data-lucide="user-cog"></i><span class="nav-fallback">◎</span></span>
      <span class="nav-text">Usuarios</span>
    </a>
<?php endif; ?>

    <a class="<?= $active === 'packages' ? 'active' : ''; ?>" href="/admin/packageTour" data-label="Paquetes" title="Paquetes" aria-label="Paquetes">
      <span class="nav-icon"><i data-lucide="plane"></i><span class="nav-fallback">✈</span></span>
      <span class="nav-text">Paquetes</span>
    </a>

    <a class="<?= $active === 'package_tags' ? 'active' : ''; ?>" href="/admin/packageTour/tags" data-label="Etiquetas" title="Etiquetas de paquetes" aria-label="Etiquetas de paquetes">
      <span class="nav-icon"><i data-lucide="tags"></i><span class="nav-fallback">#</span></span>
      <span class="nav-text">Etiquetas</span>
    </a>


    <a class="<?= $active === 'package_templates' ? 'active' : ''; ?>" href="/admin/packageTour/templates" data-label="Plantillas" title="Plantillas de paquetes" aria-label="Plantillas de paquetes">
      <span class="nav-icon"><i data-lucide="copy-plus"></i><span class="nav-fallback">▣</span></span>
      <span class="nav-text">Plantillas</span>
    </a>
    <a class="<?= $active === 'currencies' ? 'active' : ''; ?>" href="/admin/currencies" data-label="Monedas" title="Monedas" aria-label="Monedas">
      <span class="nav-icon"><i data-lucide="coins"></i><span class="nav-fallback">$</span></span>
      <span class="nav-text">Monedas</span>
    </a>

    <a class="<?= $active === 'package_analytics' ? 'active' : ''; ?>" href="/admin/packageTour/analytics" data-label="Analítica de paquetes" title="Analítica de paquetes" aria-label="Analítica de paquetes">
      <span class="nav-icon"><i data-lucide="bar-chart-3"></i><span class="nav-fallback">▦</span></span>
      <span class="nav-text">Analítica de paquetes</span>
    </a>

    <a class="<?= $active === 'experiences' ? 'active' : ''; ?>" href="/admin/experiences" data-label="Experiencias" title="Experiencias" aria-label="Experiencias">
      <span class="nav-icon"><i data-lucide="sparkles"></i><span class="nav-fallback">✦</span></span>
      <span class="nav-text">Experiencias</span>
    </a>

    <a class="<?= $active === 'pqrs' ? 'active' : ''; ?>" href="/admin/pqrs" data-label="PQRS" title="PQRS" aria-label="PQRS">
      <span class="nav-icon"><i data-lucide="file-text"></i><span class="nav-fallback">☰</span></span>
      <span class="nav-text">PQRS</span>
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="pill">
      <span>Estado</span>
      <span class="badge ok">Online</span>
    </div>
  </div>
</aside>
<button class="sidebar-backdrop" type="button" aria-label="Cerrar menu lateral"></button>

<main class="main">
  <header class="topbar">
    <div class="top-left">
      <button class="icon-btn sidebar-toggle" id="toggleSidebar" aria-label="Abrir o cerrar menú" type="button">
        <i data-lucide="menu"></i>
        <span class="menu-fallback" aria-hidden="true">☰</span>
      </button>

      <div class="page-title">
        <h1><?= htmlspecialchars($page_title ?? '') ?></h1>
        <p><?= htmlspecialchars($page_subtitle ?? '') ?></p>
      </div>
    </div>

    <div class="top-right">
      

    <div class="dropdown">
  <div class="userchip" id="userChip">
    <div class="avatar">
      <?php if (profile_photo_url($currentAdmin->profile_photo_path ?? null)): ?>
        <img src="<?= htmlspecialchars(profile_photo_url($currentAdmin->profile_photo_path)) ?>" alt="Foto de perfil">
      <?php else: ?>
        <?= strtoupper(substr($user_name, 0, 1)); ?><?= strtoupper(substr($user_role, 0, 1)); ?>
      <?php endif; ?>
    </div>

    <div class="user-meta">
      <div class="name"><?= htmlspecialchars($user_name) ?></div>
      <div class="role"><?= htmlspecialchars($user_role) ?></div>
    </div>
  </div>

  <div class="menu" id="userMenu">
    <a href="/admin/profile">
      <i data-lucide="user"></i>
      <span>Ver perfil</span>
    </a>

    <a href="/admin/users/editUser">
      <i data-lucide="square-pen"></i>
      <span>Editar perfil</span>
    </a>

    <form method="POST" action="/admin/users/logout" class="menu-logout-form">
      <?= \app\Core\Csrf::input(); ?>
      <button type="submit" class="menu-logout-btn danger">
        <i data-lucide="log-out"></i>
        <span>Cerrar sesión</span>
      </button>
    </form>
  </div>
</div>
    </div>
  </header>

  <section class="content">
