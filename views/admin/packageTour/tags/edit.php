<?php
$formTitle = 'Editar etiqueta';
$action = '/admin/packageTour/tags/edit';
$submitText = 'Guardar cambios';
$cancelUrl = '/admin/packageTour/tags';
include __DIR__ . '/_form.php';
?>

<?php if (($usageCount ?? 0) > 0): ?>
  <div class="card" style="max-width:860px; margin-top:16px;">
    <strong>Uso actual:</strong>
    <span class="badge info"><?= (int) $usageCount ?> paquete(s)</span>
    <p class="card-sub">Si la desactivas, dejará de aparecer para nuevas selecciones, pero no se pierden las relaciones ya guardadas.</p>
  </div>
<?php endif; ?>
