<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Paquetes turísticos</title>

  <style>
    :root{
      --red:#b61f2a;
      --text:#222;
      --muted:#666;
      --border:#e6e6e6;
      --skeleton:#e9ecef;
      --cardRadius: 16px;
    }
    *{ box-sizing:border-box; }
    body{
      margin:0;
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      color:var(--text);
      background:#fff;
    }
    .container{
      max-width: 1100px;
      margin: 0 auto;
      padding: 18px 16px 40px;
    }
    .top-line{ height: 2px; background: var(--red); width: 100%; margin-bottom: 16px; }
    h1{ margin: 0 0 10px; color: var(--red); font-size: 18px; font-weight: 900; }

    .search{ display:flex; gap:10px; align-items:center; margin-bottom:22px; }
    .searchbox{ position: relative; flex:1; }
    .searchbox input{
      width:100%; border:0; outline:none; background:#d9d9d9; border-radius:999px;
      padding:10px 38px 10px 14px; font-size:13px; font-weight:700; color:#222;
    }
    .searchbox .icon{ position:absolute; right:12px; top:50%; transform:translateY(-50%); opacity:.7; font-size:16px; user-select:none; }

    .section-title{ margin:18px 0 12px; color:var(--red); font-size:16px; font-weight:900; }

    .grid{ display:grid; grid-template-columns:repeat(3, 1fr); gap:22px; }
    .card{ background:#fff; border-radius:var(--cardRadius); }
    .thumb{
  border-radius:14px;
  overflow:hidden;
  aspect-ratio: 4 / 5;
  border:1px solid var(--border);
  background:#f7f7f7;
  position:relative;
}
    .favorite-form{position:absolute;top:10px;right:10px;z-index:2;margin:0;}
    .favorite-btn{
      width:38px;height:38px;border:0;border-radius:999px;background:#fff;color:#b61f2a;
      box-shadow:0 4px 14px rgba(0,0,0,.18);font-size:20px;cursor:pointer;
    }
    .favorite-btn.active{background:#b61f2a;color:#fff;}

.thumb img{
  width:100%;
  height:100%;
  object-fit:cover;
  display:block;
}
    .badge{
      position:absolute; top:10px; left:10px; background:#ffd200; color:#111;
      font-weight:900; font-size:12px; padding:6px 9px; border-radius:999px;
      box-shadow:0 4px 14px rgba(0,0,0,.15);
    }
    .card-body{ padding:10px 2px 0; }
    .name{ margin:8px 0; color:var(--red); font-weight:900; font-size:14px; }
    .tags{ display:flex; gap:8px; flex-wrap:wrap; margin:0 0 10px; }
    .tag{ background:#cfcfcf; color:#222; font-size:11px; font-weight:800; padding:6px 10px; border-radius:999px; }
    .meta{ font-size:12px; color:#666; }
    .more{ display:inline-block; margin-top:8px; color:#111; font-weight:800; font-size:12px; text-decoration:none; }
    .more:hover{ text-decoration:underline; }

    .skeleton{
      animation: shimmer 1.2s infinite linear;
      background: linear-gradient(90deg, var(--skeleton) 25%, #f6f7f8 50%, var(--skeleton) 75%);
      background-size: 200% 100%;
    }
    @keyframes shimmer{
      0%{ background-position:200% 0; }
      100%{ background-position:-200% 0; }
    }
    .sk-thumb{
  aspect-ratio: 4 / 5;
  border-radius:14px;
  border:1px solid var(--border);
}
    .sk-line{ height:14px; border-radius:999px; margin-top:10px; width:70%; }
    .sk-tags{ display:flex; gap:8px; margin-top:10px; }
    .sk-tag{ width:78px; height:22px; border-radius:999px; }
    .sk-more{ width:60px; height:12px; border-radius:999px; margin-top:12px; }

    .status{ margin:16px 0 0; font-size:13px; color:var(--muted); }
    #sentinel{ height: 180px; }

    @media (max-width: 900px){ .grid{ grid-template-columns:repeat(2, 1fr); } }
    @media (max-width: 620px){
      .grid{ grid-template-columns:1fr; }
     
    }
  </style>
</head>

<body>
  <div class="top-line"></div>

  <main class="container">
    <h1>Descubra paquetes turísticos</h1>

    <div class="search">
      <div class="searchbox">
        <input id="q" type="search" placeholder="Escribe. Busca. Viaja. Ej: Punta Cana, playa, todo incluido..." />
        <div class="icon">🔎</div>
      </div>
    </div>

    <div class="section-title">Paquetes más visitados</div>
    <section class="grid" id="grid"></section>

    <div id="sentinel"></div>
    <p class="status" id="status"></p>
  </main>

  <script>
    const gridEl = document.getElementById("grid");
    const statusEl = document.getElementById("status");
    const sentinelEl = document.getElementById("sentinel");
    const qEl = document.getElementById("q");

    const PAGE_SIZE = 6;

    let cursor = 0;
    let loading = false;
    let hasMore = true;
    let query = "";
    let authenticated = false;
    let csrf = "";

    function skeletonCardHTML(){
      return `
        <article class="card">
          <div class="sk-thumb skeleton"></div>
          <div class="card-body">
            <div class="sk-line skeleton"></div>
            <div class="sk-tags">
              <div class="sk-tag skeleton"></div>
              <div class="sk-tag skeleton"></div>
            </div>
            <div class="sk-more skeleton"></div>
          </div>
        </article>
      `;
    }

    function showSkeletons(count){
      const frag = document.createDocumentFragment();
      for(let i = 0; i < count; i++){
        const div = document.createElement("div");
        div.innerHTML = skeletonCardHTML();
        frag.appendChild(div.firstElementChild);
      }
      gridEl.appendChild(frag);
    }

    function removeSkeletons(){
      gridEl.querySelectorAll(".sk-thumb").forEach(el => {
        const card = el.closest(".card");
        if(card) card.remove();
      });
    }

    function cardHTML(pkg){
      const tags = (pkg.tags || []).map(t => `<span class="tag">${escapeHtml(t)}</span>`).join("");
      const badge = pkg.badge ? `<div class="badge">${escapeHtml(pkg.badge)}</div>` : "";
      const favorite = authenticated ? `
        <form class="favorite-form" method="post" action="/packagesTourist/favorite">
          <input type="hidden" name="_csrf" value="${escapeAttr(csrf)}">
          <input type="hidden" name="package_id" value="${Number(pkg.id)}">
          <input type="hidden" name="return_to" value="/packagesTourist">
          <button class="favorite-btn ${pkg.is_favorite ? "active" : ""}" type="submit" aria-label="${pkg.is_favorite ? "Quitar de favoritos" : "Guardar en favoritos"}">
            ${pkg.is_favorite ? "♥" : "♡"}
          </button>
        </form>` : "";

      return `
        <article class="card">
          <div class="thumb">
            ${badge}
            ${favorite}
            <img src="${escapeAttr(pkg.image)}" alt="${escapeAttr(pkg.title)}" loading="lazy">
          </div>
          <div class="card-body">
            <div class="name">${escapeHtml(pkg.title)}</div>
            <div class="tags">${tags}</div>
            <div class="meta">
              ${pkg.location ? escapeHtml(pkg.location) + " · " : ""}
              Desde $${formatPrice(pkg.price_from)}
            </div>
            <a class="more" href="${escapeAttr(pkg.url)}">Ver más</a>
          </div>
        </article>
      `;
    }

    function appendCards(items){
      const frag = document.createDocumentFragment();
      for(const pkg of items){
        const div = document.createElement("div");
        div.innerHTML = cardHTML(pkg);
        frag.appendChild(div.firstElementChild);
      }
      gridEl.appendChild(frag);
    }

    function visibleCardsCount(){
      return gridEl.querySelectorAll(".thumb").length;
    }

    function updateStatus(total){
      const visible = visibleCardsCount();
      if(!hasMore) statusEl.textContent = `Fin. Mostrando ${visible} de ${total}.`;
      else statusEl.textContent = `Mostrando ${visible} de ${total}...`;
    }

    async function fetchPackages({ cursorStart, limit, q }){
      const params = new URLSearchParams({
        cursor: String(cursorStart),
        limit: String(limit),
        q: q || ""
      });

      const res = await fetch(`/api/packagesTourist?${params.toString()}`, {
        headers: { "Accept": "application/json" }
      });

      if(!res.ok){
        throw new Error("Error al cargar paquetes");
      }

      return await res.json();
    }

    async function loadNext(){
      if(loading || !hasMore) return;
      loading = true;

      showSkeletons(3);

      try{
        const res = await fetchPackages({
          cursorStart: cursor,
          limit: PAGE_SIZE,
          q: query
        });

        removeSkeletons();
        authenticated = Boolean(res.authenticated);
        csrf = res.csrf || "";
        appendCards(res.items || []);

        cursor = res.nextCursor || 0;
        hasMore = Boolean(res.hasMore);

        updateStatus(res.total || 0);
      }catch(err){
        console.error(err);
        removeSkeletons();
        hasMore = false;
        statusEl.textContent = "Error cargando paquetes. Intenta nuevamente.";
      }finally{
        loading = false;
      }
    }

    const observer = new IntersectionObserver((entries) => {
      if(entries[0].isIntersecting){
        loadNext();
      }
    }, { rootMargin: "800px 0px", threshold: 0.01 });

    observer.observe(sentinelEl);

    let raf = false;
    function nearBottom(px = 700){
      const scrollBottom = window.scrollY + window.innerHeight;
      const docHeight = document.documentElement.scrollHeight;
      return (docHeight - scrollBottom) < px;
    }

    window.addEventListener("scroll", () => {
      if(raf) return;
      raf = true;
      requestAnimationFrame(() => {
        raf = false;
        if(nearBottom(900)) loadNext();
      });
    }, { passive:true });

    window.addEventListener("resize", () => {
      if(nearBottom(900)) loadNext();
    });

    let t = null;
    qEl.addEventListener("input", () => {
      clearTimeout(t);
      t = setTimeout(() => {
        query = qEl.value.trim();
        resetAndLoad();
      }, 300);
    });

    function resetAndLoad(){
      gridEl.innerHTML = "";
      statusEl.textContent = "";
      cursor = 0;
      hasMore = true;
      loading = false;
      loadNext();
    }

    function formatPrice(value){
      try{
        return Number(value || 0).toLocaleString("es-CO", {
          minimumFractionDigits: 0,
          maximumFractionDigits: 0
        });
      }catch(e){
        return value || "0";
      }
    }

    function escapeHtml(str){
      return String(str)
        .replaceAll("&","&amp;")
        .replaceAll("<","&lt;")
        .replaceAll(">","&gt;")
        .replaceAll('"',"&quot;")
        .replaceAll("'","&#039;");
    }

    function escapeAttr(str){ return escapeHtml(str); }

    loadNext();
  </script>
</body>
</html>
