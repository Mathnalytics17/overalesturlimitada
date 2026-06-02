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

  <nav class="nav">
    <a class="<?= $active === 'dashboard' ? 'active' : ''; ?>" href="/admin">
      <i data-lucide="layout-dashboard"></i>
      <span class="nav-text">Dashboard</span>
    </a>

    <a class="<?= $active === 'leads' ? 'active' : ''; ?>" href="/admin/leads">
      <i data-lucide="users"></i>
      <span class="nav-text">Contactos potenciales</span>
    </a>

    <a class="<?= $active === 'sales' ? 'active' : ''; ?>" href="/admin/sales">
      <i data-lucide="line-chart"></i>
      <span class="nav-text">Seguimiento de ventas</span>
    </a>

    <a class="<?= $active === 'chatbot' ? 'active' : ''; ?>" href="/admin/chatbot">
      <i data-lucide="message-circle"></i>
      <span class="nav-text">Chatbot WhatsApp</span>
    </a>

<?php $currentAdmin = \app\Core\AdminAuth::user(); ?>
<?php if ($currentAdmin && $currentAdmin->can('manage_users')): ?>
  <a class="<?= $active==='users'?'active':''; ?>" href="/admin/users">
    <i data-lucide="user-cog"></i>
    <span class="nav-text">Usuarios</span>
  </a>
<?php endif; ?>
    <a class="<?= $active === 'packages' ? 'active' : ''; ?>" href="/admin/packageTour">
      <i data-lucide="plane"></i>
      <span class="nav-text">Paquetes</span>
    </a>

      <a class="<?= $active === 'docs' ? 'active' : ''; ?>" href="/admin/experiences">
      <i data-lucide="laugh"></i>
      <span class="nav-text">Experiencias</span>
    </a>

    <a class="<?= $active === 'docs' ? 'active' : ''; ?>" href="/admin/pqrs">
      <i data-lucide="file-text"></i>
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

<main class="main">
  <header class="topbar">
    <div class="top-left">
      <button class="icon-btn" id="toggleSidebar" aria-label="Menú">
        <i data-lucide="menu"></i>
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
      <?= strtoupper(substr($user_name, 0, 1)); ?><?= strtoupper(substr($user_role, 0, 1)); ?>
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