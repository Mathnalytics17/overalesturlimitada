<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nueva oportunidad</title>
  <link rel="stylesheet" href="/styles/admin.css">
  <style>
    .sales-create-card {
      background:#fff;
      border:1px solid #e5e7eb;
      border-radius:20px;
      padding:24px;
    }

    .sales-form-grid {
      display:grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap:16px;
    }

    .sales-field {
      display:flex;
      flex-direction:column;
      gap:8px;
    }

    .sales-field.full {
      grid-column:1 / -1;
    }

    .sales-field label {
      font-size:14px;
      font-weight:800;
      color:#0f172a;
    }

    .sales-field input,
    .sales-field select,
    .sales-field textarea {
      width:100%;
      border:1px solid #cbd5e1;
      border-radius:14px;
      padding:12px 14px;
      font:inherit;
      background:#fff;
    }

    .sales-field textarea {
      min-height:120px;
      resize:vertical;
    }

    .sales-error {
      color:#b91c1c;
      font-size:13px;
      font-weight:700;
    }

    .sales-alert {
      margin-bottom:16px;
      padding:12px 14px;
      border-radius:12px;
      background:#fee2e2;
      border:1px solid #fecaca;
      color:#991b1b;
      font-weight:700;
    }

    .sales-actions {
      display:flex;
      justify-content:flex-end;
      gap:10px;
      margin-top:20px;
      flex-wrap:wrap;
    }

    .btn-main,
    .btn-outline {
      border:none;
      border-radius:14px;
      padding:12px 18px;
      font:inherit;
      font-weight:800;
      cursor:pointer;
      text-decoration:none;
      display:inline-flex;
      align-items:center;
      justify-content:center;
    }

    .btn-main {
      background:#0ea5e9;
      color:#fff;
    }

    .btn-outline {
      background:#fff;
      color:#4c1d95;
      border:1px solid #d1d5db;
    }

    @media (max-width: 900px) {
      .sales-form-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <div class="app">
    <main class="main">
      <header class="topbar">
        <div class="top-left">
          <div class="page-title">
            <h1>Nueva oportunidad</h1>
            <p>Crea una oportunidad manual para clientes que escriben directo al número.</p>
          </div>
        </div>
      </header>

      <section class="content">
        <?php
          $mode = 'create';
          $action = '/admin/sales/create';
          $submitText = 'Crear oportunidad';
          $cancelUrl = '/admin/sales';
          include __DIR__ . '/_form.php';
        ?>
      </section>
    </main>
  </div>
</body>
</html>