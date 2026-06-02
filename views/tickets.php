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
          <input
            type="text"
            class="form-control"
            id="country"
            name="country"
            list="countryOptions"
            placeholder="Escribe o selecciona un país"
            value="<?= e((string) (($old['country'] ?? ''))) ?>"
            required />
          <datalist id="countryOptions"></datalist>
        </div>

        <div class="mb-3">
          <label for="city" class="form-label">Ciudad destino</label>
          <input
            type="text"
            class="form-control"
            id="city"
            name="city"
            list="cityOptions"
            placeholder="Escribe o selecciona una ciudad"
            value="<?= e((string) (($old['city'] ?? ''))) ?>"
            required />
          <datalist id="cityOptions"></datalist>

          <small style="display:block; opacity:.75; margin-top:6px;">
            Si no aparece la ciudad, puedes escribirla manualmente.
          </small>
        </div>

        <!-- FECHAS -->
        <div class="mb-3">
          <label for="departureDate" class="form-label">Fecha de ida</label>
          <input
            type="date"
            class="form-control"
            id="departureDate"
            name="departureDate"
            value="<?= e((string) (($old['departureDate'] ?? ''))) ?>"
            required />
        </div>

        <div class="mb-2 form-check">
          <input
            type="checkbox"
            class="form-check-input"
            id="oneWay"
            name="oneWay"
            value="1"
            <?= !empty($old['oneWay'] ?? null) ? "checked" : "" ?> />
          <label class="form-check-label" for="oneWay">Solo ida (sin fecha de regreso)</label>
        </div>

        <div class="mb-3">
          <label for="returnDate" class="form-label">Fecha de regreso (opcional)</label>
          <input type="date" class="form-control" id="returnDate" name="returnDate" />
          <small style="display:block; opacity:.75; margin-top:6px;">
            Si eliges regreso, debe ser igual o posterior a la fecha de ida.
          </small>
        </div>
        <div class="ticket-section">
          <h3>Viajeros</h3>

          <div class="ticket-grid">
            <div class="mb-3">
              <label for="adults" class="form-label">Adultos</label>
              <input
                type="number"
                class="form-control"
                id="adults"
                name="adults"
                min="1"
                value="<?= e((string) (($old['adults'] ?? '1'))) ?>"
                required />
            </div>

            <div class="mb-3">
              <label for="children" class="form-label">Niños</label>
              <input
                type="number"
                class="form-control"
                id="children"
                name="children"
                min="0"
                value="<?= e((string) (($old['children'] ?? '0'))) ?>" />
            </div>

            <div class="mb-3">
              <label for="babies" class="form-label">Bebés</label>
              <input
                type="number"
                class="form-control"
                id="babies"
                name="babies"
                min="0"
                value="<?= e((string) (($old['babies'] ?? '0'))) ?>" />
            </div>
          </div>
        </div>

        <div class="ticket-section">
          <h3>Necesidades especiales</h3>

          <div class="mb-2 form-check">
            <input
              type="checkbox"
              class="form-check-input"
              id="travelsWithPet"
              name="travelsWithPet"
              value="1"
              <?= !empty($old['travelsWithPet'] ?? null) ? "checked" : "" ?> />
            <label class="form-check-label" for="travelsWithPet">
              Viaja con mascota
            </label>
          </div>

          <div class="mb-2 form-check">
            <input
              type="checkbox"
              class="form-check-input"
              id="needsWheelchair"
              name="needsWheelchair"
              value="1"
              <?= !empty($old['needsWheelchair'] ?? null) ? "checked" : "" ?> />
            <label class="form-check-label" for="needsWheelchair">
              Necesita silla de ruedas
            </label>
          </div>

          <div class="mb-3">
            <label for="sportsEquipment" class="form-label">Artículo deportivo</label>
            <input
              type="text"
              class="form-control"
              id="sportsEquipment"
              name="sportsEquipment"
              placeholder="Ej: bicicleta, tabla de surf, palos de golf"
              value="<?= e((string) (($old['sportsEquipment'] ?? ''))) ?>" />
          </div>
        </div>
        <div class="mb-3 checkbox-container">
          <input
            type="checkbox"
            id="acepta"
            name="acepta"
            value="1"
            <?= !empty($old['acepta'] ?? null) ? "checked" : "" ?>>

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
    const countryEl = document.getElementById("country");
    const cityEl = document.getElementById("city");
    const countryOptionsEl = document.getElementById("countryOptions");
    const cityOptionsEl = document.getElementById("cityOptions");

    const departureEl = document.getElementById("departureDate");
    const returnEl = document.getElementById("returnDate");
    const oneWayEl = document.getElementById("oneWay");

    let DESTINATIONS = [];

    function normalizeText(value) {
      return String(value || "")
        .trim()
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "");
    }

    async function loadDestinations() {
      try {
        const response = await fetch("/data/tickets-destinations.json", {
          cache: "no-store"
        });

        if (!response.ok) {
          throw new Error("No se pudo cargar el archivo de destinos");
        }

        const data = await response.json();
        DESTINATIONS = Array.isArray(data.countries) ? data.countries : [];

        renderCountryOptions();
        renderCityOptions(countryEl.value);
      } catch (error) {
        console.error(error);

        countryOptionsEl.innerHTML = "";
        cityOptionsEl.innerHTML = "";
      }
    }

    function renderCountryOptions() {
      countryOptionsEl.innerHTML = "";

      const countries = [...DESTINATIONS]
        .map((item) => item.name)
        .filter(Boolean)
        .sort((a, b) => a.localeCompare(b, "es"));

      for (const country of countries) {
        const option = document.createElement("option");
        option.value = country;
        countryOptionsEl.appendChild(option);
      }
    }

    function findCountry(countryName) {
      const normalized = normalizeText(countryName);

      return DESTINATIONS.find((item) => {
        return normalizeText(item.name) === normalized;
      });
    }

    function renderCityOptions(countryName) {
      cityOptionsEl.innerHTML = "";

      const country = findCountry(countryName);

      if (!country || !Array.isArray(country.cities)) {
        return;
      }

      const cities = [...country.cities]
        .filter(Boolean)
        .sort((a, b) => a.localeCompare(b, "es"));

      for (const city of cities) {
        const option = document.createElement("option");
        option.value = city;
        cityOptionsEl.appendChild(option);
      }
    }

    countryEl.addEventListener("input", () => {
      renderCityOptions(countryEl.value);
    });

    countryEl.addEventListener("change", () => {
      renderCityOptions(countryEl.value);
    });

    function syncDateConstraints() {
      const dep = departureEl.value;

      if (dep) {
        returnEl.min = dep;

        if (returnEl.value && returnEl.value < dep) {
          returnEl.value = "";
        }
      } else {
        returnEl.min = "";
      }
    }

    function applyOneWay() {
      const isOneWay = oneWayEl.checked;

      returnEl.disabled = isOneWay;
      returnEl.required = false;

      if (isOneWay) {
        returnEl.value = "";
      }
    }

    departureEl.addEventListener("change", () => {
      syncDateConstraints();
    });

    oneWayEl.addEventListener("change", () => {
      applyOneWay();
    });

    loadDestinations();
    applyOneWay();
    syncDateConstraints();
  </script>
</body>

</html>