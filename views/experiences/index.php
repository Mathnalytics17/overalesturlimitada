<?php
$items = $items ?? [];

function experience_asset_url(?string $path, string $fallback = '/img/default-experience.jpg'): string
{
    $path = trim((string)$path);

    if ($path === '') {
        return $fallback;
    }

    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    $path = str_replace('\\', '/', $path);

    return '/' . ltrim($path, '/');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Experiencias</title>
  <style>
    .exp-wrap {
      max-width: 1180px;
      margin: 0 auto;
      padding: 36px 20px 70px;
    }

    .exp-head {
      display:flex;
      justify-content:space-between;
      gap:20px;
      align-items:flex-end;
      flex-wrap:wrap;
      margin-bottom:28px;
    }

    .exp-head h1 {
      margin:0;
      font-size:44px;
      color:#0f172a;
      line-height:1.05;
    }

    .exp-head p {
      margin:10px 0 0;
      color:#64748b;
      max-width:760px;
      line-height:1.8;
      font-size:15px;
    }

    .exp-btn {
      background:#b61f2a;
      color:#fff;
      border:none;
      text-decoration:none;
      padding:13px 18px;
      border-radius:14px;
      font-weight:800;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      white-space:nowrap;
    }

    .exp-grid {
      display:grid;
      grid-template-columns:repeat(3,1fr);
      gap:20px;
    }

    .exp-card {
      background:#fff;
      border:1px solid #e5e7eb;
      border-radius:22px;
      overflow:hidden;
      box-shadow:0 10px 24px rgba(15,23,42,.04);
      display:flex;
      flex-direction:column;
    }

    .exp-cover {
      width:100%;
      height:220px;
      object-fit:cover;
      display:block;
      background:#f1f5f9;
    }

    .exp-body {
      padding:18px;
      display:flex;
      flex-direction:column;
      gap:10px;
      flex:1;
    }

    .exp-stars {
      color:#b61f2a;
      font-weight:900;
      font-size:18px;
      line-height:1;
    }

    .exp-title {
      font-size:21px;
      font-weight:900;
      color:#0f172a;
      line-height:1.25;
    }

    .exp-meta {
      color:#64748b;
      font-size:14px;
      line-height:1.7;
    }

    .exp-story {
      color:#334155;
      line-height:1.75;
      font-size:15px;
      flex:1;
    }

    .exp-tag-row {
      display:flex;
      flex-wrap:wrap;
      gap:8px;
      margin-top:6px;
    }

    .exp-tag {
      display:inline-flex;
      align-items:center;
      padding:6px 10px;
      border-radius:999px;
      background:#f8fafc;
      border:1px solid #e2e8f0;
      color:#475569;
      font-size:12px;
      font-weight:800;
    }

    .exp-empty {
      grid-column:1/-1;
      color:#64748b;
      padding:20px;
      border:1px dashed #cbd5e1;
      border-radius:18px;
      background:#fff;
    }

    @media (max-width: 980px) {
      .exp-grid {
        grid-template-columns:1fr 1fr;
      }
    }

    @media (max-width: 680px) {
      .exp-grid {
        grid-template-columns:1fr;
      }

      .exp-head h1 {
        font-size:34px;
      }

      .exp-cover {
        height:210px;
      }
    }
  </style>
</head>
<body>
  <main class="exp-wrap">
    <div class="exp-head">
      <div>
        <h1>Experiencias</h1>
        <p>Historias reales de viajeros y clientes que han vivido servicios con nosotros. Cada experiencia fue revisada antes de publicarse.</p>
      </div>
      <a class="exp-btn" href="/experiences/share">Compartir mi experiencia</a>
    </div>

    <div class="exp-grid">
      <?php if (empty($items)): ?>
        <div class="exp-empty">Aún no hay experiencias publicadas.</div>
      <?php else: ?>
        <?php foreach ($items as $item): ?>
          <?php
            $coverPath = experience_asset_url($item->cover_image->image_path ?? null);
            $displayName = $item->display_name ?: $item->customer_name ?: 'Cliente';
            $location = trim((string)(($item->city_destination ?? '') . ' ' . ($item->country_destination ?? '')));
            $excerpt = mb_strimwidth((string)($item->story ?? ''), 0, 260, '...');
          ?>
          <article class="exp-card">
            <img
              class="exp-cover"
              src="<?= htmlspecialchars($coverPath) ?>"
              alt="<?= htmlspecialchars($item->title ?? 'Experiencia') ?>"
            >

            <div class="exp-body">
              <div class="exp-stars"><?= str_repeat('★', max(1, min(5, (int)($item->rating ?? 5)))) ?></div>

              <div class="exp-title"><?= htmlspecialchars($item->title ?? '') ?></div>

              <div class="exp-meta">
                <?= htmlspecialchars($displayName) ?><br>
                <?= htmlspecialchars($location !== '' ? $location : 'Destino no especificado') ?>
              </div>

              <div class="exp-story"><?= nl2br(htmlspecialchars($excerpt)) ?></div>

              <div class="exp-tag-row">
                <span class="exp-tag"><?= htmlspecialchars($item->experience_type ?? 'general') ?></span>

                <?php if (!empty($item->package_slug)): ?>
                  <span class="exp-tag">Paquete: <?= htmlspecialchars($item->package_slug) ?></span>
                <?php endif; ?>

                <?php if (!empty($item->extra_service_slug)): ?>
                  <span class="exp-tag">Servicio: <?= htmlspecialchars($item->extra_service_slug) ?></span>
                <?php endif; ?>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </main>
</body>


<?php $flashModal = $flashModal ?? null; ?>

<?php if (!empty($flashModal)): ?>
  <div id="experience-modal-overlay" style="
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 20px;
  ">
    <div style="
      width: 100%;
      max-width: 520px;
      background: #fff;
      border-radius: 18px;
      padding: 24px;
      box-shadow: 0 20px 60px rgba(0,0,0,.18);
      position: relative;
      animation: modalIn .2s ease-out;
    ">
      <button
        type="button"
        onclick="closeExperienceModal()"
        style="
          position:absolute;
          top:12px;
          right:12px;
          border:none;
          background:transparent;
          font-size:24px;
          cursor:pointer;
          line-height:1;
        "
      >&times;</button>

      <div style="
        width:56px;
        height:56px;
        border-radius:999px;
        background:#dcfce7;
        color:#166534;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:28px;
        font-weight:700;
        margin-bottom:16px;
      ">
        ✓
      </div>

      <h3 style="margin:0 0 10px 0;font-size:24px;color:#111827;">
        <?= htmlspecialchars($flashModal['title'] ?? 'Aviso') ?>
      </h3>

      <p style="margin:0 0 20px 0;color:#4b5563;font-size:16px;line-height:1.5;">
        <?= htmlspecialchars($flashModal['message'] ?? '') ?>
      </p>

      <button
        type="button"
        onclick="closeExperienceModal()"
        style="
          width:100%;
          border:none;
          border-radius:12px;
          background:#111827;
          color:#fff;
          font-weight:700;
          padding:14px 18px;
          cursor:pointer;
        "
      >
        Entendido
      </button>
    </div>
  </div>

  <style>
    @keyframes modalIn {
      from { transform: translateY(10px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }
  </style>

  <script>
    function closeExperienceModal() {
      const modal = document.getElementById('experience-modal-overlay');
      if (modal) modal.remove();
    }
  </script>
<?php endif; ?>
</html>