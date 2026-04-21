<script>
document.addEventListener("DOMContentLoaded", () => {
  const servicesDropdown = document.getElementById("servicesDropdown");
  const navToggle = document.getElementById("navToggle");
  const navMenu = document.getElementById("navMenu");
  const userDropdown = document.getElementById("userDropdown");

  if (navToggle && navMenu) {
    navToggle.addEventListener("click", (e) => {
      e.stopPropagation();
      navToggle.classList.toggle("active");
      navMenu.classList.toggle("open");
    });
  }

  if (servicesDropdown) {
    const button = servicesDropdown.querySelector(".dropbtn");
    const menu = servicesDropdown.querySelector(".dropdown-menu");

    button.addEventListener("click", (e) => {
      e.stopPropagation();
      servicesDropdown.classList.toggle("open");
    });

    if (menu) {
      menu.addEventListener("click", (e) => e.stopPropagation());
    }
  }

  if (userDropdown) {
    const userButton = userDropdown.querySelector(".user-btn");
    const userMenu = userDropdown.querySelector(".user-menu");

    userButton.addEventListener("click", (e) => {
      e.stopPropagation();
      userDropdown.classList.toggle("open");
    });

    if (userMenu) {
      userMenu.addEventListener("click", (e) => e.stopPropagation());
    }
  }

  document.addEventListener("click", () => {
    if (servicesDropdown) servicesDropdown.classList.remove("open");
    if (userDropdown) userDropdown.classList.remove("open");
    if (navMenu) navMenu.classList.remove("open");
    if (navToggle) navToggle.classList.remove("active");
  });
});
</script>

<header class="topbar">
  <nav class="navbar">
    <div class="navbar-container">

      <a href="/" class="brand-home" aria-label="Inicio">
        <i class="fa-regular fa-house"></i>
      </a>

      <button class="nav-toggle" id="navToggle" type="button" aria-label="Abrir menú">
        <span></span>
        <span></span>
        <span></span>
      </button>

      <div class="nav-menu" id="navMenu">
        <ul class="nav-links">
          <li><a href="/aboutUs">Nosotros</a></li>

          <li class="dropdown" id="servicesDropdown">
            <button type="button" class="dropbtn" aria-expanded="false" aria-controls="servicesMenu">
              Servicios <span class="caret">▾</span>
            </button>

            <ul class="dropdown-menu" id="servicesMenu">
              <li><a href="/tickets">Nuevos destinos</a></li>
              <li><a href="/packagesTourist">Paquetes de viajes</a></li>
              <li><a href="/extra-services">Más servicios</a></li>
            </ul>
          </li>

          <li><a href="/contact">Contáctanos</a></li>
          <li><a href="/experiences">Experiencias</a></li>
          <li><a href="/pqrs">PQRS</a></li>
        </ul>

        <div class="nav-actions">
          <div class="dropdown user-dropdown" id="userDropdown">
            <button type="button" class="user-btn" aria-label="Usuario">
              <i class="fa-solid fa-user"></i>
            </button>

            <div class="user-menu">
              <?php if (\app\Core\CustomerAuth::check()): ?>
                <a href="/users/user">Mi cuenta</a>

                <form method="POST" action="/users/logout" class="logout-form">
                  <?= \app\Core\Csrf::input(); ?>
                  <button type="submit" class="logout-btn">Cerrar sesión</button>
                </form>
              <?php else: ?>
                <a href="/users/login">Iniciar sesión</a>
                <a href="/users/register">Registrarse</a>
              <?php endif; ?>
            </div>
          </div>

          <img class="image-colombia" src="/img/header/image (29).png" alt="Bandera de Colombia">
        </div>
      </div>

    </div>
  </nav>
</header>