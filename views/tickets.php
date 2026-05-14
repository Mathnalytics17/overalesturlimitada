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
      tiquetes hacia tu destino preferido. Tambien puedes indicar si viajas
      solo o con varias personas, por ejemplo 3 adultos y 4 ninos, y nuestro
      equipo te ayudara a conseguir la mejor opcion para tu viaje.
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
      <div class="mb-2 form-check">
        <input type="checkbox" class="form-check-input" id="customDestination" name="customDestination" value="1" <?= !empty($old['customDestination'] ?? null) ? "checked" : "" ?> />
        <label class="form-check-label" for="customDestination">Ingresar mi propio destino</label>
      </div>

      <div class="form-grid" id="guidedDestinationFields">
        <div class="mb-3">
          <label for="countrySelect" class="form-label">Pais destino</label>
          <select class="form-control" id="countrySelect">
            <option value="">Selecciona un pais</option>
          </select>
          <small class="field-help">Selecciona un pais de la lista cargada desde nuestro catalogo local.</small>
        </div>

        <div class="mb-3">
          <label for="citySelect" class="form-label">Ciudad destino</label>
          <select class="form-control" id="citySelect" disabled>
            <option value="">Selecciona primero un pais</option>
          </select>
          <small class="field-help">La ciudad se habilita segun el pais que elijas.</small>
        </div>
      </div>

      <div class="form-grid" id="customDestinationFields" style="display:none;">
        <div class="mb-3">
          <label for="countryCustom" class="form-label">Pais destino</label>
          <input
            type="text"
            class="form-control"
            id="countryCustom"
            autocomplete="off"
            placeholder="Escribe el pais"
            value="<?= e((string) (($old['country'] ?? ''))) ?>"
          />
          <small class="field-help">Usa esta opcion si tu pais no aparece en la lista.</small>
        </div>

        <div class="mb-3">
          <label for="cityCustom" class="form-label">Ciudad destino</label>
          <input
            type="text"
            class="form-control"
            id="cityCustom"
            autocomplete="off"
            placeholder="Escribe la ciudad"
            value="<?= e((string) (($old['city'] ?? ''))) ?>"
          />
          <small class="field-help">Asi puedes enviarnos cualquier ciudad aunque no este precargada.</small>
        </div>
      </div>

      <input type="hidden" id="country" name="country" value="<?= e((string) (($old['country'] ?? ''))) ?>" />
      <input type="hidden" id="city" name="city" value="<?= e((string) (($old['city'] ?? ''))) ?>" />

      <div class="mb-3">
        <label class="form-label">Viajeros</label>
        <div class="form-grid form-grid-3">
          <div>
            <label for="adults" class="form-label">Adultos</label>
            <input type="number" class="form-control" id="adults" name="adults" min="1" max="20" value="<?= e((string) (($old['adults'] ?? '1'))) ?>" required />
          </div>
          <div>
            <label for="children" class="form-label">Ninos</label>
            <input type="number" class="form-control" id="children" name="children" min="0" max="20" value="<?= e((string) (($old['children'] ?? '0'))) ?>" />
          </div>
          <div>
            <label for="infants" class="form-label">Bebes</label>
            <input type="number" class="form-control" id="infants" name="infants" min="0" max="10" value="<?= e((string) (($old['infants'] ?? '0'))) ?>" />
          </div>
        </div>
        <small class="field-help">Ejemplo: 3 adultos, 4 ninos y 1 bebe.</small>
      </div>

      <p class="passenger-summary" id="passengerSummary">Viaja 1 adulto.</p>

      <div class="mb-3">
        <label class="form-label">Necesidades adicionales del viaje</label>
        <div class="form-grid form-grid-3">
          <div class="checkbox-container">
            <input
              type="checkbox"
              id="travelWithPet"
              name="travelWithPet"
              value="1"
              <?= !empty($old['travelWithPet'] ?? null) ? "checked" : "" ?>
            >
            <label for="travelWithPet">Viaja con mascota</label>
          </div>

          <div class="checkbox-container">
            <input
              type="checkbox"
              id="needWheelchair"
              name="needWheelchair"
              value="1"
              <?= !empty($old['needWheelchair'] ?? null) ? "checked" : "" ?>
            >
            <label for="needWheelchair">Necesita silla de ruedas</label>
          </div>

          <div class="checkbox-container">
            <input
              type="checkbox"
              id="sportsEquipment"
              name="sportsEquipment"
              value="1"
              <?= !empty($old['sportsEquipment'] ?? null) ? "checked" : "" ?>
            >
            <label for="sportsEquipment">Lleva articulo deportivo</label>
          </div>
        </div>
      </div>

      <div class="mb-3">
        <label for="specialRequestNotes" class="form-label">Detalles adicionales</label>
        <textarea id="specialRequestNotes" name="specialRequestNotes" rows="4" placeholder="Cuéntanos si la mascota necesita guacal, si el articulo deportivo es tabla, bicicleta, etc."><?= e((string) (($old['specialRequestNotes'] ?? ''))) ?></textarea>
      </div>

      <!-- FECHAS -->
      <div class="mb-3">
        <label for="departureDate" class="form-label">Fecha de ida</label>
        <input type="date" class="form-control" id="departureDate" name="departureDate" value="<?= e((string) (($old['departureDate'] ?? ''))) ?>" required />
      </div>

      <div class="mb-2 form-check">
        <input type="checkbox" class="form-check-input" id="oneWay" name="oneWay" value="1" <?= !empty($old['oneWay'] ?? null) ? "checked" : "" ?> />
        <label class="form-check-label" for="oneWay">Solo ida (sin fecha de regreso)</label>
      </div>

      <div class="mb-3">
        <label for="returnDate" class="form-label">Fecha de regreso (opcional)</label>
        <input type="date" class="form-control" id="returnDate" name="returnDate" value="<?= e((string) (($old['returnDate'] ?? ''))) ?>" />
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
  const countryEl = document.getElementById("country");
  const cityEl = document.getElementById("city");
  const countrySelectEl = document.getElementById("countrySelect");
  const citySelectEl = document.getElementById("citySelect");
  const countryCustomEl = document.getElementById("countryCustom");
  const cityCustomEl = document.getElementById("cityCustom");
  const customDestinationEl = document.getElementById("customDestination");
  const guidedDestinationFieldsEl = document.getElementById("guidedDestinationFields");
  const customDestinationFieldsEl = document.getElementById("customDestinationFields");

  const departureEl = document.getElementById("departureDate");
  const returnEl = document.getElementById("returnDate");
  const oneWayEl = document.getElementById("oneWay");
  const adultsEl = document.getElementById("adults");
  const childrenEl = document.getElementById("children");
  const infantsEl = document.getElementById("infants");
  const passengerSummaryEl = document.getElementById("passengerSummary");
  const formEl = document.querySelector("form[action='/tickets']");
  const oldCountry = countryEl.value;
  const oldCity = cityEl.value;

  let destinationData = [];

  function toLookupKey(value) {
    return (value || "")
      .toString()
      .trim()
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "");
  }

  function findCountryEntry(name) {
    const lookup = toLookupKey(name);
    return destinationData.find((country) => toLookupKey(country.name) === lookup) || null;
  }

  function renderCountries() {
    countrySelectEl.innerHTML = '<option value="">Selecciona un pais</option>';

    for (const country of destinationData.slice().sort((a, b) => a.name.localeCompare(b.name, "es"))) {
      const opt = document.createElement("option");
      opt.value = country.name;
      opt.textContent = country.name;
      countrySelectEl.appendChild(opt);
    }
  }

  function renderCities(countryName) {
    citySelectEl.innerHTML = '<option value="">Selecciona una ciudad</option>';
    const country = findCountryEntry(countryName);

    if (!country || !Array.isArray(country.cities) || country.cities.length === 0) {
      citySelectEl.disabled = true;
      return;
    }

    for (const city of country.cities) {
      const opt = document.createElement("option");
      opt.value = city;
      opt.textContent = city;
      citySelectEl.appendChild(opt);
    }

    citySelectEl.disabled = false;
  }

  function applyDestinationMode() {
    const useCustom = customDestinationEl.checked;

    guidedDestinationFieldsEl.style.display = useCustom ? "none" : "";
    customDestinationFieldsEl.style.display = useCustom ? "" : "none";

    countrySelectEl.disabled = useCustom;
    citySelectEl.disabled = useCustom || countrySelectEl.value === "";
    countryCustomEl.disabled = !useCustom;
    cityCustomEl.disabled = !useCustom;
  }

  function syncDestinationValues() {
    if (customDestinationEl.checked) {
      countryEl.value = countryCustomEl.value.trim();
      cityEl.value = cityCustomEl.value.trim();
      return true;
    }

    countryEl.value = countrySelectEl.value.trim();
    cityEl.value = citySelectEl.value.trim();
    return true;
  }

  function hydrateInitialDestination() {
    const matchingCountry = findCountryEntry(oldCountry);
    const hasOldDestination = oldCountry !== "" || oldCity !== "";
    const cityMatches = !!(matchingCountry && matchingCountry.cities.includes(oldCity));
    const shouldUseCustom = !!(
      customDestinationEl.checked ||
      (hasOldDestination && (!matchingCountry || (oldCity !== "" && !cityMatches)))
    );

    customDestinationEl.checked = shouldUseCustom;

    if (matchingCountry) {
      countrySelectEl.value = matchingCountry.name;
      renderCities(matchingCountry.name);

      if (matchingCountry.cities.includes(oldCity)) {
        citySelectEl.value = oldCity;
      }
    } else {
      renderCities("");
    }

    countryCustomEl.value = oldCountry;
    cityCustomEl.value = oldCity;
    applyDestinationMode();
  }

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

  function applyOneWay() {
    const isOneWay = oneWayEl.checked;
    returnEl.disabled = isOneWay;
    returnEl.required = false; // opcional siempre
    if (isOneWay) returnEl.value = "";
  }

  function pluralize(value, singular, plural) {
    return `${value} ${value === 1 ? singular : plural}`;
  }

  function updatePassengerSummary() {
    const adults = Math.max(1, parseInt(adultsEl.value || "1", 10) || 1);
    const children = Math.max(0, parseInt(childrenEl.value || "0", 10) || 0);
    const infants = Math.max(0, parseInt(infantsEl.value || "0", 10) || 0);
    const parts = [pluralize(adults, "adulto", "adultos")];

    if (children > 0) {
      parts.push(pluralize(children, "nino", "ninos"));
    }

    if (infants > 0) {
      parts.push(pluralize(infants, "bebe", "bebes"));
    }

    passengerSummaryEl.textContent = `Viajan ${parts.join(", ")}.`;
  }

  countrySelectEl.addEventListener("change", (e) => {
    renderCities(e.target.value);
    citySelectEl.value = "";
  });

  departureEl.addEventListener("change", () => {
    syncDateConstraints();
  });

  oneWayEl.addEventListener("change", () => {
    applyOneWay();
  });

  customDestinationEl.addEventListener("change", () => {
    applyDestinationMode();
  });

  adultsEl.addEventListener("input", updatePassengerSummary);
  childrenEl.addEventListener("input", updatePassengerSummary);
  infantsEl.addEventListener("input", updatePassengerSummary);

  formEl.addEventListener("submit", (event) => {
    syncDestinationValues();

    if (!countryEl.value || !cityEl.value) {
      event.preventDefault();
      alert("Debes indicar un pais y una ciudad destino.");
    }
  });

  fetch("/data/tickets-destinations.json")
    .then((response) => response.json())
    .then((data) => {
      destinationData = Array.isArray(data.countries) ? data.countries : [];
      renderCountries();
      hydrateInitialDestination();
    })
    .catch(() => {
      customDestinationEl.checked = true;
      applyDestinationMode();
    })
    .finally(() => {
      applyOneWay();
      syncDateConstraints();
      updatePassengerSummary();
    });
</script>
</body>
</html>
