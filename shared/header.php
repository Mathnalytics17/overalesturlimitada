<script>
document.addEventListener("DOMContentLoaded", () => {
  const dropdown = document.getElementById("servicesDropdown");
  if (dropdown) {
    const button = dropdown.querySelector(".dropbtn");
    const menu = dropdown.querySelector(".dropdown-menu");

    button.addEventListener("click", (e) => {
      e.stopPropagation();
      dropdown.classList.toggle("open");
    });

    document.addEventListener("click", () => {
      dropdown.classList.remove("open");
    });

    menu.addEventListener("click", (e) => e.stopPropagation());
  }

  const userDropdown = document.getElementById("userDropdown");
  if (userDropdown) {
    const userButton = userDropdown.querySelector(".user-btn");
    const userMenu = userDropdown.querySelector(".user-menu");

    userButton.addEventListener("click", (e) => {
      e.stopPropagation();
      userDropdown.classList.toggle("open");
    });

    document.addEventListener("click", () => {
      userDropdown.classList.remove("open");
    });

    userMenu.addEventListener("click", (e) => e.stopPropagation());
  }
});
</script>

<header class="topbar">
  <nav class="navbar">
    <div class="navbar-container">

      <div>
        <ul>
          <li><a href="/"><i class="fa-regular fa-house"></i></a></li>
          <li><a href="/aboutUs">Nosotros</a></li>

          <li class="dropdown" id="servicesDropdown">
            <button type="button" class="dropbtn" aria-expanded="false" aria-controls="servicesMenu">
              Servicios ▾
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
      </div>

      <div style="display:flex; align-items:center; gap:14px;">

        <div class="dropdown user-dropdown" id="userDropdown">
          <button type="button" class="user-btn" style="background:none;border:none;cursor:pointer;">
            <i class="fa-solid fa-user"></i>
          </button>

          <div class="user-menu" style="
            display:none;
            position:absolute;
            right:0;
            top:calc(100% + 10px);
            min-width:180px;
            background:#fff;
            border:1px solid #ddd;
            border-radius:10px;
            box-shadow:0 8px 20px rgba(0,0,0,.08);
            padding:10px;
            z-index:999;
          ">
            <?php if (\app\Core\CustomerAuth::check()): ?>
              <a href="/users/user" style="display:block;padding:8px 10px;text-decoration:none;color:#111;">
                Mi cuenta
              </a>

              <form method="POST" action="/users/logout" style="margin:0;">
                <?= \app\Core\Csrf::input(); ?>
                <button
                  type="submit"
                  style="
                    width:100%;
                    text-align:left;
                    padding:8px 10px;
                    background:none;
                    border:none;
                    cursor:pointer;
                    color:#b91c1c;
                  "
                >
                  Cerrar sesión
                </button>
              </form>
            <?php else: ?>
              <a href="/users/login" style="display:block;padding:8px 10px;text-decoration:none;color:#111;">
                Iniciar sesión
              </a>

              <a href="/users/register" style="display:block;padding:8px 10px;text-decoration:none;color:#111;">
                Registrarse
              </a>
            <?php endif; ?>
          </div>
        </div>

       

        <div>
          <img class="image-colombia" src="/public/img/header/image (29).png"/>
        </div>

      </div>
    </div>
  </nav>
</header>

<style>
#servicesDropdown {
  position: relative;
}

#servicesDropdown .dropdown-menu {
  display: none;
  position: absolute;
  top: calc(100% + 10px);
  left: 0;
  min-width: 220px;
  background: #fff;
  border: 1px solid #ddd;
  border-radius: 10px;
  box-shadow: 0 8px 20px rgba(0,0,0,.08);
  padding: 10px 0;
  z-index: 999;
  list-style: none;
  margin: 0;
}

#servicesDropdown.open .dropdown-menu {
  display: block;
}

#servicesDropdown .dropdown-menu li {
  list-style: none;
}

#servicesDropdown .dropdown-menu li a {
  display: block;
  padding: 10px 14px;
  text-decoration: none;
  color: #111 !important;
}

#servicesDropdown .dropdown-menu li a:hover {
  background: #f5f5f5;
  color: #b61f2a !important;
}

#userDropdown .user-menu {
  display: none;
}

#userDropdown.open .user-menu {
  display: block !important;
}

#userDropdown {
  position: relative;
}

/* Solo menú principal en blanco */
.navbar > .navbar-container > div:first-child > ul,
.navbar > .navbar-container > div:first-child > ul > li,
.navbar > .navbar-container > div:first-child > ul > li > a,
.navbar .dropbtn,
.navbar .fa-house,
.navbar .fa-user,
.navbar .fa-globe {
  color: #fff !important;
}

.navbar ul li a {
  text-decoration: none;
}

.navbar .dropbtn {
  background: none;
  border: none;
  cursor: pointer;
  font: inherit;
}

.navbar > .navbar-container > div:first-child > ul > li > a:hover,
.navbar .dropbtn:hover {
  color: #fff !important;
  opacity: .9;
}

.user-btn {
  color: #fff !important;
}

.image-colombia {
  display: block;
}
</style>