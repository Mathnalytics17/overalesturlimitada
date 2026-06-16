  </section>
</main>
</div>

<script >

  (function(){
  if (window.lucide) {
    window.lucide.createIcons();
  }

  const app = document.querySelector(".app");
  const toggle = document.querySelector("#toggleSidebar");
  const sidebarBackdrop = document.querySelector(".sidebar-backdrop");
  const userChip = document.querySelector("#userChip");
  const menu = document.querySelector("#userMenu");

  if(toggle && app){
    toggle.addEventListener("click", () => {
      if (window.matchMedia("(max-width: 760px)").matches) {
        app.classList.toggle("mobile-sidebar-open");
        return;
      }

      app.classList.toggle("collapsed");
      // opcional: recordar en localStorage
      localStorage.setItem("ui.sidebarCollapsed", app.classList.contains("collapsed") ? "1" : "0");
    });

    const saved = localStorage.getItem("ui.sidebarCollapsed");
    if(saved === "1") app.classList.add("collapsed");
  }

  if(sidebarBackdrop && app){
    sidebarBackdrop.addEventListener("click", () => app.classList.remove("mobile-sidebar-open"));
  }

  document.querySelectorAll(".sidebar .nav a").forEach(link => {
    link.addEventListener("click", () => app?.classList.remove("mobile-sidebar-open"));
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
})();
</script>
</body>
</html>
