<?php
$pagination = $pagination ?? [];
$paginationBaseUrl = $paginationBaseUrl ?? '';
$paginationFilters = $paginationFilters ?? [];
$currentPage = (int) ($pagination['page'] ?? 1);
$lastPage = (int) ($pagination['last_page'] ?? 1);

if (!function_exists('admin_pagination_url')) {
    function admin_pagination_url(string $baseUrl, array $filters, int $page): string
    {
        $filters['page'] = $page;
        $filters = array_filter($filters, static fn($value) => $value !== '' && $value !== null);
        return $baseUrl . '?' . http_build_query($filters);
    }
}
?>
<div class="admin-pagination">
  <div class="admin-pagination-summary">
    Mostrando <?= (int) ($pagination['from'] ?? 0) ?>-<?= (int) ($pagination['to'] ?? 0) ?>
    de <?= (int) ($pagination['total'] ?? 0) ?> registros.
    Pagina <?= $currentPage ?> de <?= $lastPage ?>.
  </div>

  <?php if ($lastPage > 1): ?>
    <nav class="admin-pagination-links" aria-label="Paginacion">
      <?php if ($currentPage > 1): ?>
        <a href="<?= e(admin_pagination_url($paginationBaseUrl, $paginationFilters, $currentPage - 1)) ?>">Anterior</a>
      <?php endif; ?>

      <?php for ($page = max(1, $currentPage - 2); $page <= min($lastPage, $currentPage + 2); $page++): ?>
        <a
          href="<?= e(admin_pagination_url($paginationBaseUrl, $paginationFilters, $page)) ?>"
          class="<?= $page === $currentPage ? 'active' : '' ?>">
          <?= $page ?>
        </a>
      <?php endfor; ?>

      <?php if ($currentPage < $lastPage): ?>
        <a href="<?= e(admin_pagination_url($paginationBaseUrl, $paginationFilters, $currentPage + 1)) ?>">Siguiente</a>
      <?php endif; ?>
    </nav>
  <?php endif; ?>
</div>
