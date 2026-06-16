<?php
$package = $package ?? null;
$cover = $cover ?? null;
$gallery = $gallery ?? [];
$includes = $includes ?? [];
$excludes = $excludes ?? [];
$conditions = $conditions ?? [];
$highlights = $highlights ?? [];
$tags = $tags ?? [];

$itinerary = $itinerary ?? [];
$customer = $customer ?? null;
$isFavorite = $isFavorite ?? false;
$recommendations = $recommendations ?? [];

function package_asset_url(?string $path, string $fallback = '/img/packages/default.jpg'): string
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

$coverPath = package_asset_url($cover->image_path ?? null);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($package->title ?? 'Paquete') ?></title>
  <style>
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:#fff;color:#222;}
    .wrap{max-width:1100px;margin:0 auto;padding:20px 16px 50px;}
    .hero{display:grid;grid-template-columns:1.4fr .8fr;gap:24px;align-items:start;}
    .hero img{width:100%;border-radius:18px;display:block;}
    .title{font-size:32px;font-weight:900;margin:0 0 6px;}
    .sub{color:#666;margin:0 0 14px;}
    .tags{display:flex;gap:8px;flex-wrap:wrap;margin:0 0 16px;}
    .tag{background:#ececec;padding:7px 12px;border-radius:999px;font-size:12px;font-weight:800;}
    .card{border:1px solid #e6e6e6;border-radius:16px;padding:16px;background:#fff;}
    .price{font-size:30px;font-weight:900;margin:10px 0;}
    .section{margin-top:28px;}
    .section h2{margin:0 0 12px;font-size:22px;}
    .list{padding-left:18px;}
    .acc{border:1px solid #e6e6e6;border-radius:14px;margin-bottom:12px;overflow:hidden;}
    .acc summary{cursor:pointer;padding:14px 16px;font-weight:800;background:#fafafa;}
    .acc .body{padding:14px 16px;color:#444;}
    .gallery{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-top:18px;}
    .gallery img{width:100%;border-radius:14px;height:180px;object-fit:cover;}
    .actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:16px;}
    .favorite-btn{background:#fff;color:#b61f2a;border:1px solid #b61f2a;padding:11px 16px;border-radius:10px;font-weight:800;cursor:pointer;}
    @media (max-width:900px){.hero{grid-template-columns:1fr;}.gallery{grid-template-columns:1fr 1fr;}}
    @media (max-width:620px){.gallery{grid-template-columns:1fr;}}
  </style>
</head>
<body>
  
  <main class="wrap">
    <div class="hero">
      <div>
        <img src="<?= htmlspecialchars($coverPath) ?>" alt="<?= htmlspecialchars($package->title ?? '') ?>">
      </div>

      <div class="card">
        <h1 class="title"><?= htmlspecialchars($package->title ?? '') ?></h1>
        <p class="sub"><?= htmlspecialchars($package->location_name ?? '') ?></p>

        <div class="tags">
          <?php foreach ($tags as $tag): ?>
            <span class="tag"><?= htmlspecialchars($tag->name ?? '') ?></span>
          <?php endforeach; ?>
        </div>

        <div style="color:#666;">Precio desde</div>
        <div class="price">$<?= number_format((float)($package->price_from ?? 0), 0, ',', '.') ?> <?= htmlspecialchars($package->currency ?? 'COP') ?></div>

        <div style="margin-top:12px;color:#444;">
          <?= htmlspecialchars($package->short_description ?? '') ?>
        </div>

        <div class="actions">
          <?php if (\app\Core\CustomerAuth::check()): ?>
            <form method="post" action="/packagesTourist/favorite">
              <?= \app\Core\Csrf::input(); ?>
              <input type="hidden" name="package_id" value="<?= (int) ($package->id ?? 0) ?>">
              <input type="hidden" name="return_to" value="/packagesTourist/package?slug=<?= urlencode((string) ($package->slug ?? '')) ?>">
              <button class="favorite-btn" type="submit"><?= $isFavorite ? '♥ Quitar de favoritos' : '♡ Guardar en favoritos' ?></button>
            </form>
          <?php else: ?>
            <a href="/users/login" class="favorite-btn" style="text-decoration:none;">♡ Inicia sesión para guardar</a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <?php if (!empty($recommendations)): ?>
      <section class="section">
        <h2>También te puede interesar</h2>
        <div class="gallery">
          <?php foreach ($recommendations as $recommended): ?>
            <a class="card" href="/packagesTourist/package?slug=<?= urlencode((string) $recommended->slug) ?>" style="text-decoration:none;color:#222;">
              <strong><?= htmlspecialchars((string) $recommended->title) ?></strong>
              <div style="margin-top:8px;color:#64748b;"><?= htmlspecialchars((string) $recommended->recommendation_reason) ?></div>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <section class="section">
      <h2>General</h2>
      <p><?= nl2br(htmlspecialchars($package->general_description ?? '')) ?></p>

      <?php if (!empty($highlights)): ?>
        <div class="tags" style="margin-top:16px;">
          <?php foreach ($highlights as $item): ?>
            <span class="tag"><?= htmlspecialchars($item->title ?? '') ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      
    </section>

  <section class="section">
    <h2>Itinerario</h2>

    <?php if (!empty($itinerary)): ?>
      <?php foreach ($itinerary as $day): ?>
        <details class="acc" <?= (int)($day->day_number ?? 0) === 1 ? 'open' : '' ?>>
          <summary>
            Día <?= (int)($day->day_number ?? 0) ?><?= !empty($day->title) ? ' - ' . htmlspecialchars($day->title) : '' ?>
          </summary>
          <div class="body">
            <?= nl2br(htmlspecialchars($day->content ?? '')) ?>
          </div>
        </details>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="card" style="color:#64748b;line-height:1.7;">
        El itinerario no está disponible.
      </div>
    <?php endif; ?>
  </section>

    <section class="section">
      <h2>Incluye</h2>
      <ul class="list">
        <?php foreach ($includes as $item): ?>
          <li><?= htmlspecialchars($item->content ?? '') ?></li>
        <?php endforeach; ?>
      </ul>

      <?php if (!empty($excludes)): ?>
        <h2 style="margin-top:24px;">No incluye</h2>
        <ul class="list">
          <?php foreach ($excludes as $item): ?>
            <li><?= htmlspecialchars($item->content ?? '') ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>

    <?php if (!empty($conditions)): ?>
      <section class="section">
        <h2>Condiciones</h2>
        <?php foreach ($conditions as $condition): ?>
          <details class="acc">
            <summary><?= htmlspecialchars($condition->title ?? '') ?></summary>
            <div class="body"><?= nl2br(htmlspecialchars($condition->content ?? '')) ?></div>
          </details>
        <?php endforeach; ?>
      </section>
    <?php endif; ?>

    <?php if (!empty($gallery)): ?>
      <section class="section">
        <h2>Galería</h2>
        <div class="gallery">
          <?php foreach ($gallery as $image): ?>
            <img
              src="<?= htmlspecialchars(package_asset_url($image->image_path ?? null)) ?>"
              alt="<?= htmlspecialchars($image->alt_text ?? ($package->title ?? '')) ?>"
            >
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>


    <?php $experiences = $experiences ?? []; ?>

<?php if (!empty($experiences)): ?>
<section class="section">
  <h2>Experiencias de viajeros</h2>
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
    <?php foreach ($experiences as $exp): ?>
      <div class="card">
        <div style="font-weight:900;color:#b61f2a;margin-bottom:8px;">
          <?= str_repeat('★', max(1, min(5, (int)($exp->rating ?? 5)))) ?>
        </div>
        <div style="font-size:18px;font-weight:900;margin-bottom:8px;">
          <?= htmlspecialchars($exp->title ?? '') ?>
        </div>
        <div style="color:#475569;line-height:1.7;">
          <?= nl2br(htmlspecialchars(mb_strimwidth((string)($exp->story ?? ''), 0, 220, '...'))) ?>
        </div>
        <div style="margin-top:12px;color:#64748b;font-size:14px;">
          <?= htmlspecialchars($exp->display_name ?: $exp->customer_name ?: 'Cliente') ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div style="margin-top:16px;">
    <a href="/experiences" class="btn btn-secondary">Ver más experiencias</a>
  </div>
</section>
<?php endif; ?>

    <section class="section">
      <h2>Continuar por WhatsApp</h2>
      <div class="card">
        <p style="margin-top:0;color:#475569;">Si este paquete te interesa, registra tu caso y continúa la conversación con un asesor.</p>
        <form method="post" action="/packagesTourist/inquiry" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
  <?= \app\Core\Csrf::input(); ?>
  <input type="hidden" name="package_id" value="<?= (int) ($package->id ?? 0) ?>">
  <input type="hidden" name="package_slug" value="<?= htmlspecialchars($package->slug ?? '') ?>">
  <input type="hidden" name="package_title" value="<?= htmlspecialchars($package->title ?? '') ?>">

  <div>
    <label>Nombre completo</label>
    <input type="text" name="full_name" value="<?= htmlspecialchars((string) ($customer?->fullName() ?? '')) ?>" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:10px;">
  </div>

  <div>
    <label>Correo</label>
    <input type="email" name="email" value="<?= htmlspecialchars((string) ($customer?->email ?? '')) ?>" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:10px;">
  </div>

  <div>
    <label>Teléfono</label>
    <input type="text" name="phone" value="<?= htmlspecialchars((string) ($customer?->phone ?? '')) ?>" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:10px;">
  </div>

  <div>
    <label>Mes estimado de viaje</label>
    <input type="text" name="travel_month" placeholder="Ej: julio 2026" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:10px;">
  </div>

  <div>
    <label>Viajeros</label>
    <input type="number" min="1" name="travelers" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:10px;">
  </div>

  <div style="grid-column:1/-1;">
    <label>Mensaje</label>
    <textarea name="message" rows="4" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:10px;">Hola, me interesa este paquete y quiero continuar la atención por WhatsApp.</textarea>
  </div>

  <div style="grid-column:1/-1;display:flex;align-items:center;gap:8px;">
    <input type="checkbox" id="aceptaWa" name="acepta" value="1">
    <label for="aceptaWa">Acepto el tratamiento de datos personales y deseo continuar por WhatsApp.</label>
  </div>

  <div style="grid-column:1/-1; margin:8px 0;">
    <?= turnstile_widget_html(); ?>
  </div>

  <div style="grid-column:1/-1;">
    <button type="submit" style="background:#16a34a;color:#fff;padding:12px 18px;border:none;border-radius:10px;font-weight:800;cursor:pointer;">
      Registrar y abrir WhatsApp
    </button>
  </div>
</form>
      </div>
    </section>

  </main>
</body>
</html>
