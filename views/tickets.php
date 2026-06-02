<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Document</title>
  <link rel="stylesheet" href="/styles/contact.css" />
</head>

<body>
<main>
  <div class="container-contact">
    <?php if (!empty($message ?? null)): ?>
      <div class="alert" style="margin-bottom:16px;padding:12px;border-radius:10px;background:<?= ($messageType ?? "success") === "error" ? "#fee2e2" : "#dcfce7" ?>;color:#111;">
        <?= e($message) ?>
      </div>
    <?php endif; ?>
    <h1>¿Tienes algun destino en particular? Cuentanos</h1>

    <p>
      En este apartado podras preguntar acerca de la disponibilidad de
      tiquetes hacia tu destino preferido, nosotros nos encargaremos de
      brindarte la mejor asesoria y guia para conseguirte la mejor opción
      para ti
    </p>

    <form method="post" action="/tickets">
      <?= \app\Core\Csrf::input(); ?>
      <div class="mb-3">
        <label for="firstLastName" class="form-label">Nombres y Apellidos</label>
        <input type="text" class="form-control" id="firstLastName" name="firstLastName" value="<?= e((string) (($old['firstLastName'] ?? ''))) ?>" />
      </div>

      <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <input type="email" class="form-control" id="email" name="email" value="<?= e((string) (($old['email'] ?? ''))) ?>" />
      </div>

      <div class="mb-3">
        <label for="telefono" class="form-label">Teléfono</label>
        <input type="text" class="form-control" id="telefono" name="telefono" value="<?= e((string) (($old['telefono'] ?? ''))) ?>" />
      </div>

      <!-- DESTINO -->
      <div class="mb-3">
        <label for="country" class="form-label">País destino</label>
        <select class="form-control" id="country" name="country" required>
          <option value="" selected disabled>Selecciona un país</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="city" class="form-label">Ciudad destino</label>
        <select class="form-control" id="city" name="city" required disabled>
          <option value="" selected disabled>Selecciona primero un país</option>
        </select>
      </div>

      <!-- FECHAS -->
      <div class="mb-3">
        <label for="departureDate" class="form-label">Fecha de ida</label>
        <input type="date" class="form-control" id="departureDate" name="departureDate" required />
      </div>

      <div class="mb-2 form-check">
        <input type="checkbox" class="form-check-input" id="oneWay" name="oneWay" value="1" />
        <label class="form-check-label" for="oneWay">Solo ida (sin fecha de regreso)</label>
      </div>

      <div class="mb-3">
        <label for="returnDate" class="form-label">Fecha de regreso (opcional)</label>
        <input type="date" class="form-control" id="returnDate" name="returnDate" />
        <small style="display:block; opacity:.75; margin-top:6px;">
          Si eliges regreso, debe ser igual o posterior a la fecha de ida.
        </small>
      </div>

<div class="mb-3 checkbox-container">
  <input 
    type="checkbox" 
    id="acepta" 
    name="acepta" 
    value="1"
    <?= !empty($old['acepta'] ?? null) ? "checked" : "" ?>
  >

  <label for="acepta">
    Acepto la 
    <a href="/politica-datos" target="_blank">
      política de tratamiento de datos personales
    </a>
  </label>
</div>

      <div style="margin:12px 0;">
        <?= turnstile_widget_html(); ?>
      </div>

      <button type="submit" class="btn btn-primary">Enviar</button>
    </form>
  </div>

  <div></div>
</main>

<script>
  // Datos de ejemplo (luego los puedes cargar desde BD o API)
  const COUNTRY_CITY = {
    CO: { name: "Colombia", cities: ["Bogotá", "Medellín", "Cartagena", "Cali"] },
    MX: { name: "México", cities: ["Ciudad de México", "Cancún", "Guadalajara", "Monterrey"] },
    ES: { name: "España", cities: ["Madrid", "Barcelona", "Sevilla", "Valencia"] },
    US: { name: "Estados Unidos", cities: ["Miami", "New York", "Los Angeles", "Orlando"] },
    AR: { name: "Argentina", cities: ["Buenos Aires", "Córdoba", "Mendoza", "Bariloche"] },
  };

  const countryEl = document.getElementById("country");
  const cityEl = document.getElementById("city");

  const departureEl = document.getElementById("departureDate");
  const returnEl = document.getElementById("returnDate");
  const oneWayEl = document.getElementById("oneWay");

  // 1) Cargar países
  function loadCountries() {
    const entries = Object.entries(COUNTRY_CITY)
      .sort((a, b) => a[1].name.localeCompare(b[1].name, "es"));

    for (const [code, data] of entries) {
      const opt = document.createElement("option");
      opt.value = code;
      opt.textContent = data.name;
      countryEl.appendChild(opt);
    }
  }

  // 2) Cargar ciudades según país
  function loadCities(countryCode) {
    cityEl.innerHTML = "";

    const first = document.createElement("option");
    first.value = "";
    first.disabled = true;
    first.selected = true;
    first.textContent = "Selecciona una ciudad";
    cityEl.appendChild(first);

    const data = COUNTRY_CITY[countryCode];
    if (!data) {
      cityEl.disabled = true;
      return;
    }

    for (const city of data.cities) {
      const opt = document.createElement("option");
      opt.value = city;
      opt.textContent = city;
      cityEl.appendChild(opt);
    }

    cityEl.disabled = false;
  }

  // 3) Reglas fechas: regreso >= ida
  function syncDateConstraints() {
    const dep = departureEl.value;
    if (dep) {
      returnEl.min = dep;
      // si ya había regreso y quedó inválido, lo limpiamos
      if (returnEl.value && returnEl.value < dep) {
        returnEl.value = "";
      }
    } else {
      returnEl.min = "";
    }
  }

  // 4) Solo ida: deshabilita regreso
  function applyOneWay() {
    const isOneWay = oneWayEl.checked;
    returnEl.disabled = isOneWay;
    returnEl.required = false; // opcional siempre
    if (isOneWay) returnEl.value = "";
  }

  countryEl.addEventListener("change", (e) => {
    loadCities(e.target.value);
  });

  departureEl.addEventListener("change", () => {
    syncDateConstraints();
  });

  oneWayEl.addEventListener("change", () => {
    applyOneWay();
  });

  // Init
  loadCountries();
  applyOneWay();
  syncDateConstraints();
</script>
</body>
</html>
