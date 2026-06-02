<?php
$services = $services ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Servicios Extras</title>

  <style>
    :root{
      --red:#b61f2a;
      --redSoft: rgba(182, 31, 42, .10);
      --soft:#f3f3f3;
      --text:#222;
      --line:#e9e9e9;
      --cardBorder:#e3e3e3;
    }

    *{ box-sizing: border-box; }

    body{
      margin:0;
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      color:var(--text);
      background:#fff;
    }

    .extras-top{
      position: sticky;
      top: 0;
      z-index: 50;
      background:#fff;
      padding: 18px 18px 12px;
    }

    .extras-top-inner{
      max-width: 1200px;
      margin: 0 auto;
    }

    .extras-top h1{
      margin:0 0 14px;
      color:var(--red);
      font-size: 52px;
      font-weight: 900;
      letter-spacing: .2px;
      line-height: 1.05;
    }

    .tabs{
      display:flex;
      gap: 18px;
      flex-wrap: nowrap;
      overflow-x: auto;
      padding: 6px 2px 14px;
      align-items:center;

      -ms-overflow-style: none;
      scrollbar-width: none;
      -webkit-overflow-scrolling: touch;
    }
    .tabs::-webkit-scrollbar{ display:none; }

    .tab{
      flex: 0 0 auto;
      border: 0;
      background: var(--soft);
      color:#555;
      padding: 14px 22px;
      border-radius: 16px;
      cursor:pointer;
      font-size: 16px;
      font-weight: 700;
      white-space: nowrap;
      transition: background .15s ease, color .15s ease, outline .15s ease;
    }

    .tab.active{
      background: var(--redSoft);
      color: var(--red);
      outline: 2px solid rgba(182,31,42,.35);
    }

    .top-red-line{
      height: 2px;
      background: var(--red);
      width: 100%;
    }

    main{
      max-width: 1200px;
      margin: 0 auto;
      padding: 18px 18px 70px;
    }

    .section{
      padding: 52px 0 34px;
      border-top: 1px solid var(--line);
      scroll-margin-top: 170px;
    }
    .section:first-child{ border-top:0; }

    .section-title{
      margin: 0 0 22px;
      color: var(--red);
      font-size: 56px;
      font-weight: 900;
      line-height: 1.05;
    }

    .card{
      display:grid;
      grid-template-columns: 1.25fr 1fr;
      border: 1px solid var(--cardBorder);
      border-radius: 10px;
      overflow: hidden;
      background:#fff;
    }

    .img{
      min-height: 230px;
      background: linear-gradient(135deg, #f1f1f1, #d9d9d9);
      overflow: hidden;
    }

    .img img{
      width: 100% !important;
      height: 100% !important;
      object-fit: cover;
      display:block;
    }

    .text{
      padding: 18px;
      border-left: 1px solid var(--cardBorder);
    }

    .text p{
      margin: 0 0 18px;
      color:#333;
      line-height: 1.55;
      font-size: 18px;
    }

    .more{
      display:inline-block;
      background: var(--red);
      color:#fff;
      text-decoration:none;
      font-weight:800;
      font-size: 16px;
      padding: 12px 22px;
      border-radius: 12px;
      border:0;
      cursor:pointer;
    }

    @media (max-width: 900px){
      .extras-top h1{ font-size: 40px; }
      .section-title{ font-size: 40px; }
      .text p{ font-size: 16px; }
    }

    @media (max-width: 760px){
      .card{ grid-template-columns: 1fr; }
      .text{ border-left: 0; border-top: 1px solid var(--cardBorder); }
      .img{ min-height: 210px; }
      .section{ scroll-margin-top: 210px; }
    }
  </style>
</head>

<body>
  <header class="extras-top" id="stickyHeader">
    <div class="extras-top-inner">
      <h1>Servicios Extras</h1>

      <nav class="tabs" aria-label="Selector de servicios">
        <?php foreach ($services as $index => $service): ?>
          <button
            class="tab <?= $index === 0 ? 'active' : '' ?>"
            data-target="<?= e($service->slug) ?>"
            type="button"
          >
            <?= e($service->titulo) ?>
          </button>
        <?php endforeach; ?>
      </nav>
    </div>
    <div class="top-red-line"></div>
  </header>

  <main>
    <?php foreach ($services as $service): ?>
      <section id="<?= e($service->slug) ?>" class="section">
        <h2 class="section-title"><?= e($service->titulo) ?></h2>
        <div class="card">
          <div class="img">
            <img src="<?= e($service->imagen) ?>" alt="<?= e($service->titulo) ?>" width="700" height="400" />
          </div>
          <div class="text">
            <p><?= e($service->descripcion_corta) ?></p>
            <a class="more" href="/extra-services/show?slug=<?= urlencode($service->slug) ?>">Ver más</a>
          </div>
        </div>
      </section>
    <?php endforeach; ?>
  </main>

  <script>
    const headerEl = document.getElementById("stickyHeader");
    const tabs = Array.from(document.querySelectorAll(".tab"));
    const sections = tabs.map(t => document.getElementById(t.dataset.target)).filter(Boolean);

    let lockActiveUpdateUntil = 0;
    let rafPending = false;

    function centerTab(tab){
      tab.scrollIntoView({ behavior: "smooth", inline: "center", block: "nearest" });
    }

    function setActiveTabById(id){
      tabs.forEach(t => t.classList.toggle("active", t.dataset.target === id));
    }

    function headerOffset(){
      return headerEl.getBoundingClientRect().height + 6;
    }

    function updateActiveFromScroll(){
      if (Date.now() < lockActiveUpdateUntil) return;

      const y = window.scrollY + headerOffset();

      let current = sections[0]?.id || null;
      for (const sec of sections) {
        if (sec.offsetTop <= y) current = sec.id;
        else break;
      }

      if (current) setActiveTabById(current);
    }

    function onScroll(){
      if (rafPending) return;
      rafPending = true;
      requestAnimationFrame(() => {
        rafPending = false;
        updateActiveFromScroll();
      });
    }

    tabs.forEach(tab => {
      tab.addEventListener("click", () => {
        const id = tab.dataset.target;
        const el = document.getElementById(id);
        if (!el) return;

        lockActiveUpdateUntil = Date.now() + 900;
        setActiveTabById(id);
        centerTab(tab);

        el.scrollIntoView({ behavior: "smooth", block: "start" });
        history.replaceState(null, "", "#" + id);
      });
    });

    window.addEventListener("scroll", onScroll, { passive: true });
    window.addEventListener("resize", onScroll);

    window.addEventListener("load", () => {
      const id = (location.hash || "").replace("#", "");
      if (id) {
        const el = document.getElementById(id);
        const tab = tabs.find(t => t.dataset.target === id);
        if (el) {
          lockActiveUpdateUntil = Date.now() + 600;
          setActiveTabById(id);
          if (tab) centerTab(tab);
          el.scrollIntoView({ behavior: "smooth", block: "start" });
          return;
        }
      }
      updateActiveFromScroll();
    });
  </script>
</body>
</html>