  </section>
</main>
</div>

<script >

  (function(){
  const app = document.querySelector(".app");
  const toggle = document.querySelector("#toggleSidebar");
  const closeSidebar = document.querySelector("#closeSidebar");
  const sidebarScrim = document.querySelector("#sidebarScrim");
  const userChip = document.querySelector("#userChip");
  const menu = document.querySelector("#userMenu");

  function isMobileViewport() {
    return window.matchMedia("(max-width: 980px)").matches;
  }

  function closeMobileSidebar() {
    if(app){
      app.classList.remove("mobile-sidebar-open");
    }
  }

  if(toggle && app){
    toggle.addEventListener("click", () => {
      if (isMobileViewport()) {
        app.classList.toggle("mobile-sidebar-open");
        return;
      }

      app.classList.toggle("collapsed");
      localStorage.setItem("ui.sidebarCollapsed", app.classList.contains("collapsed") ? "1" : "0");
    });

    const saved = localStorage.getItem("ui.sidebarCollapsed");
    if(saved === "1" && !isMobileViewport()) app.classList.add("collapsed");
  }

  if (closeSidebar) {
    closeSidebar.addEventListener("click", closeMobileSidebar);
  }

  if (sidebarScrim) {
    sidebarScrim.addEventListener("click", closeMobileSidebar);
  }

  window.addEventListener("resize", () => {
    if (!app) return;
    if (isMobileViewport()) {
      app.classList.remove("collapsed");
      return;
    }

    app.classList.remove("mobile-sidebar-open");

    const saved = localStorage.getItem("ui.sidebarCollapsed");
    if(saved === "1") {
      app.classList.add("collapsed");
    }
  });

  // Dropdown usuario
  if(userChip && menu){
    userChip.addEventListener("click", (e) => {
      e.stopPropagation();
      menu.classList.toggle("open");
    });
    document.addEventListener("click", () => menu.classList.remove("open"));
  }

  // Modales genéricos: data-open="#id" y data-close
  document.querySelectorAll("[data-open]").forEach(btn=>{
    btn.addEventListener("click", ()=>{
      const sel = btn.getAttribute("data-open");
      const el = document.querySelector(sel);
      if(el) el.classList.add("open");
    });
  });
  document.querySelectorAll("[data-close]").forEach(btn=>{
    btn.addEventListener("click", ()=>{
      const backdrop = btn.closest(".backdrop");
      if(backdrop) backdrop.classList.remove("open");
    });
  });

  // Cerrar modal clic fuera
  document.querySelectorAll(".backdrop").forEach(b=>{
    b.addEventListener("click", (e)=>{
      if(e.target === b) b.classList.remove("open");
    });
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      closeMobileSidebar();
      if (menu) {
        menu.classList.remove("open");
      }
    }
  });
})();
</script>
</body>
</html>
