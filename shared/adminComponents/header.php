<script>
document.addEventListener("DOMContentLoaded", () => {
  const dropdown = document.getElementById("servicesDropdown");
  if (!dropdown) return;

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
});
</script>


<header class="topbar">
    
        <nav class="navbar">

            <div class="navbar-container">

                    <div>

                        <ul>
                            <li><a href="/"><i class="fa-regular fa-house"></i></i></a></li>
                            <li><a href="/aboutUs">Nosotros</a></li>
                            <li class="dropdown" id="servicesDropdown">
                                    <button type="button" class="dropbtn" aria-expanded="false" aria-controls="servicesMenu">
                                        Servicios ▾
                                    </button>

                                    <ul class="dropdown-menu" id="servicesMenu">
                                        <li><a href="/tickets">Nuevos destinos</a></li>
                                        <li><a href="/packagesTourist">Paquetes de viajes</a></li>
                                        
                                        
                                        <li><a href="/extraServices">Más servicios</a></li>
                                    </ul>
                                    </li>
                            <li><a href="/contact">Contactanos</a></li>
                            <li><a href="/pqrs">PQRS</a></li>
                           

                        </ul>

                    </div>

                    <div>

                        <div>
                            <i class="fa-solid fa-globe"></i>
                        </div>
                        <div>
                            <img class="image-colombia" src="/img/header/image (29).png"/>
                        </div>

                    </div>
            </div>
            
        
        </nav>
    </header>

    

