<?php

namespace app\Controllers\admin\packageTour;

use app\Core\AdminAuth;
use app\Core\Controller;
use app\Core\Csrf;
use app\Core\Flash;
use app\Models\TourPackage;
use app\Models\TourPackageCondition;
use app\Models\TourPackageHighlight;
use app\Models\TourPackageImage;
use app\Models\TourPackageInclusion;
use app\Models\TourPackageItinerary;
use app\Models\TourPackageTag;
use app\Models\TourPackageTagItem;
use app\Models\TourPackageTemplate;
use app\Models\Currency;
use app\Services\Admin\PackageTour\PackageTourService;
use app\Services\Package\PackageNotificationService;

class PackageTourController extends Controller
{
    public function index()
    {
        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'status' => trim((string) ($_GET['status'] ?? '')),
        ];
        $pagination = TourPackage::paginateAdmin(
            $filters,
            (int) ($_GET['page'] ?? 1),
            (int) ($_GET['per_page'] ?? 25)
        );

        return $this->render('admin/packageTour/list', [
            'packages' => $pagination['items'],
            'filters' => $filters,
            'pagination' => $pagination,
            'notificationCounts' => (new PackageNotificationService())->counts(),
        ], 'adminUserLayout');
    }

    public function create()
    {
        $tags = TourPackageTag::activeList();
        $currencies = Currency::activeList();
        $old = [];

        $copyId = (int) ($_GET['copy'] ?? 0);

        if ($copyId > 0) {
            $package = TourPackage::find($copyId);

            if ($package) {
                $includes = TourPackageInclusion::byPackageAndType((int) $package->id, 'include');
                $excludes = TourPackageInclusion::byPackageAndType((int) $package->id, 'exclude');
                $conditions = TourPackageCondition::byPackage((int) $package->id);
                $highlights = TourPackageHighlight::byPackage((int) $package->id);
                $selectedTagItems = TourPackageTagItem::byPackage((int) $package->id);
                $itinerary = TourPackageItinerary::byPackage((int) $package->id);

                $old = [
                    'title' => (string) ($package->title ?? '') . ' copia',
                    'slug' => '',
                    'subtitle' => (string) ($package->subtitle ?? ''),
                    'location_name' => (string) ($package->location_name ?? ''),
                    'price_from' => (string) ($package->price_from ?? ''),
                    'currency' => (string) ($package->currency ?? 'COP'),
                    'currency_id' => (string)($package->currency_id ?? (Currency::idByCode((string)($package->currency ?? 'COP')) ?? '')),
                    'duration_days' => (string) ($package->duration_days ?? ''),
                    'duration_nights' => (string) ($package->duration_nights ?? ''),
                    'status' => 'draft',
                    'sort_order' => (string) ($package->sort_order ?? 0),
                    'short_description' => (string) ($package->short_description ?? ''),
                    'general_description' => (string) ($package->general_description ?? ''),
                    'includes' => array_map(fn($item) => (string) ($item->content ?? ''), $includes),
                    'excludes' => array_map(fn($item) => (string) ($item->content ?? ''), $excludes),
                    'highlights' => array_map(fn($item) => (string) ($item->title ?? ''), $highlights),
                    'condition_titles' => array_map(fn($item) => (string) ($item->title ?? ''), $conditions),
                    'condition_contents' => array_map(fn($item) => (string) ($item->content ?? ''), $conditions),
                    'itinerary_day_number' => array_map(fn($item) => (string) ($item->day_number ?? ''), $itinerary),
                    'itinerary_title' => array_map(fn($item) => (string) ($item->title ?? ''), $itinerary),
                    'itinerary_content' => array_map(fn($item) => (string) ($item->content ?? ''), $itinerary),
                    'no_itinerary' => empty($itinerary) ? '1' : '',
                    'tag_ids' => array_map(fn($item) => (int) $item->tag_id, $selectedTagItems),
                    'is_featured' => !empty($package->is_featured) ? '1' : '',
                    'is_popular' => !empty($package->is_popular) ? '1' : '',
                ];
            } else {
                Flash::error('El paquete que intentas copiar no existe.');
                \redirect('/admin/packageTour/create');
                exit;
            }
        }

        return $this->render('admin/packageTour/create', [
            'tags' => $tags,
            'currencies' => $currencies,
            'templates' => TourPackageTemplate::activeList(),
            'errors' => [],
            'itinerary' => [],
            'message' => null,
            'old' => $old,
        ], 'adminUserLayout');
    }

    public function store()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $service = new PackageTourService();
        $admin = AdminAuth::user();

        if (($_POST['submit_action'] ?? '') === 'save_template') {
            $result = $this->saveTemplateFromPost($_POST, $admin?->id ? (int) $admin->id : null);

            if (!empty($result['success'])) {
                Flash::success($result['message'] ?? 'Plantilla guardada correctamente.');
                
                \redirect('/admin/packageTour/create');
                exit;
            }

            return $this->render('admin/packageTour/create', [
                'tags' => TourPackageTag::activeList(),
                'currencies' => Currency::activeList(),
                'templates' => TourPackageTemplate::activeList(),
                'itinerary' => [],
                'errors' => $result['errors'] ?? [],
                'message' => $result['message'] ?? null,
                'old' => $_POST,
            ], 'adminUserLayout');
        }

        $result = $service->create(
            $_POST,
            $admin?->id ? (int) $admin->id : null,
            $_FILES
        );

        if (!empty($result['success'])) {
            Flash::success($result['message'] ?? 'Paquete creado correctamente.');
            \redirect('/admin/packageTour');
            exit;
        }

        $tags = TourPackageTag::activeList();
        $currencies = Currency::activeList();

        return $this->render('admin/packageTour/create', [
            'tags' => $tags,
            'currencies' => $currencies,
            'templates' => TourPackageTemplate::activeList(),
            'itinerary' => [],
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
            'old' => $result['old'] ?? $_POST,
        ], 'adminUserLayout');
    }

    public function edit()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $package = TourPackage::find($id);

        if (!$package) {
            Flash::error('El paquete no fue encontrado.');
            \redirect('/admin/packageTour');
            exit;
        }

        $itinerary = TourPackageItinerary::byPackage((int) $package->id);
        $tags = TourPackageTag::activeList();
        $currencies = Currency::activeList();
        $selectedTagItems = TourPackageTagItem::byPackage((int) $package->id);
        $selectedTagIds = array_map(fn($item) => (int) $item->tag_id, $selectedTagItems);

        $cover = TourPackageImage::coverByPackage((int) $package->id);
        $gallery = array_values(array_filter(
            TourPackageImage::byPackage((int) $package->id),
            fn($img) => (int) ($img->is_cover ?? 0) !== 1
        ));

        $includes = TourPackageInclusion::byPackageAndType((int) $package->id, 'include');
        $excludes = TourPackageInclusion::byPackageAndType((int) $package->id, 'exclude');
        $conditions = TourPackageCondition::byPackage((int) $package->id);
        $highlights = TourPackageHighlight::byPackage((int) $package->id);

        $old = [
            'title' => (string) ($package->title ?? ''),
            'slug' => (string) ($package->slug ?? ''),
            'subtitle' => (string) ($package->subtitle ?? ''),
            'location_name' => (string) ($package->location_name ?? ''),
            'price_from' => (string) ($package->price_from ?? ''),
            'currency' => (string) ($package->currency ?? 'COP'),
                    'currency_id' => (string)($package->currency_id ?? (Currency::idByCode((string)($package->currency ?? 'COP')) ?? '')),
            'duration_days' => (string) ($package->duration_days ?? ''),
            'duration_nights' => (string) ($package->duration_nights ?? ''),
            'status' => (string) ($package->status ?? 'draft'),
            'sort_order' => (string) ($package->sort_order ?? 0),
            'short_description' => (string) ($package->short_description ?? ''),
            'general_description' => (string) ($package->general_description ?? ''),
            'includes' => array_map(fn($item) => (string) ($item->content ?? ''), $includes),
            'excludes' => array_map(fn($item) => (string) ($item->content ?? ''), $excludes),
            'highlights' => array_map(fn($item) => (string) ($item->title ?? ''), $highlights),
            'condition_titles' => array_map(fn($item) => (string) ($item->title ?? ''), $conditions),
            'condition_contents' => array_map(fn($item) => (string) ($item->content ?? ''), $conditions),
            'itinerary_day_number' => array_map(fn($item) => (string) ($item->day_number ?? ''), $itinerary),
            'itinerary_title' => array_map(fn($item) => (string) ($item->title ?? ''), $itinerary),
            'itinerary_content' => array_map(fn($item) => (string) ($item->content ?? ''), $itinerary),
            'tag_ids' => $selectedTagIds,
            'no_itinerary' => empty($itinerary) ? '1' : '',
            'is_featured' => (string) ((int) ($package->is_featured ?? 0)),
            'is_popular' => (string) ((int) ($package->is_popular ?? 0)),
        ];

        return $this->render('admin/packageTour/edit', [
            'package' => $package,
            'tags' => $tags,
            'currencies' => $currencies,
            'templates' => TourPackageTemplate::activeList(),
            'selectedTagIds' => $selectedTagIds,
            'cover' => $cover,
            'gallery' => $gallery,
            'includes' => $includes,
            'excludes' => $excludes,
            'conditions' => $conditions,
            'highlights' => $highlights,
            'itinerary' => $itinerary,
            'errors' => [],
            'message' => null,
            'old' => $old,
        ], 'adminUserLayout');
    }

    public function update()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $service = new PackageTourService();
        $admin = AdminAuth::user();

        if (($_POST['submit_action'] ?? '') === 'save_template') {
            $result = $this->saveTemplateFromPost($_POST, $admin?->id ? (int) $admin->id : null);

            if (!empty($result['success'])) {
                Flash::success($result['message'] ?? 'Plantilla guardada correctamente.');
                
                \redirect('/admin/packageTour/edit?id=' . $id);
                exit;
            }

            Flash::error($result['message'] ?? 'No fue posible guardar la plantilla.');
            \redirect('/admin/packageTour/edit?id=' . $id);
            exit;
        }

        $result = $service->update(
            $id,
            $_POST,
            $admin?->id ? (int) $admin->id : null,
            $_FILES
        );

        if (!empty($result['success'])) {
            Flash::success($result['message'] ?? 'Paquete actualizado correctamente.');
            \redirect('/admin/packageTour');
            exit;
        }

        $package = TourPackage::find($id);
        $tags = TourPackageTag::activeList();
        $currencies = Currency::activeList();
        $itinerary = $package ? TourPackageItinerary::byPackage((int) $package->id) : [];
        $cover = $package ? TourPackageImage::coverByPackage((int) $package->id) : null;
        $gallery = $package
            ? array_values(array_filter(
                TourPackageImage::byPackage((int) $package->id),
                fn($img) => (int) ($img->is_cover ?? 0) !== 1
            ))
            : [];

        $includes = $package ? TourPackageInclusion::byPackageAndType((int) $package->id, 'include') : [];
        $excludes = $package ? TourPackageInclusion::byPackageAndType((int) $package->id, 'exclude') : [];
        $conditions = $package ? TourPackageCondition::byPackage((int) $package->id) : [];
        $highlights = $package ? TourPackageHighlight::byPackage((int) $package->id) : [];

        return $this->render('admin/packageTour/edit', [
            'package' => $package,
            'itinerary' => $itinerary,
            'tags' => $tags,
            'currencies' => $currencies,
            'templates' => TourPackageTemplate::activeList(),
            'selectedTagIds' => array_map('intval', $_POST['tag_ids'] ?? []),
            'cover' => $cover,
            'gallery' => $gallery,
            'includes' => $includes,
            'excludes' => $excludes,
            'conditions' => $conditions,
            'highlights' => $highlights,
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
            'old' => $result['old'] ?? $_POST,
        ], 'adminUserLayout');
    }


    public function delete()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $package = TourPackage::find($id);

        if (!$package) {
            Flash::error('El paquete no fue encontrado.');
            \redirect('/admin/packageTour');
            exit;
        }

        if ($package->delete()) {
            Flash::success('Paquete eliminado correctamente.');
        } else {
            Flash::error('No fue posible eliminar el paquete.');
        }

        \redirect('/admin/packageTour');
        exit;
    }

    public function toggleStatus()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $package = TourPackage::find($id);

        if (!$package) {
            Flash::error('El paquete no fue encontrado.');
            \redirect('/admin/packageTour');
            exit;
        }

        $requestedStatus = trim((string) ($_POST['status'] ?? ''));
        if ($requestedStatus === '') {
            $requestedStatus = (string) ($package->status ?? 'draft') === 'published' ? 'draft' : 'published';
        }

        $allowed = ['draft', 'published', 'archived'];
        if (!in_array($requestedStatus, $allowed, true)) {
            Flash::error('Estado de paquete inválido.');
            \redirect('/admin/packageTour');
            exit;
        }

        if ($requestedStatus === 'published') {
            $errors = $this->getPublishBlockingErrors($package);
            if ($errors !== []) {
                Flash::error('No se puede publicar todavía: ' . implode(' ', $errors));
                \redirect('/admin/packageTour/edit?id=' . (int) $package->id);
                exit;
            }
        }

        $updated = $package->update([
            'status' => $requestedStatus,
            'published_at' => $requestedStatus === 'published'
                ? ($package->published_at ?: date('Y-m-d H:i:s'))
                : null,
            'updated_by_admin_id' => AdminAuth::user()?->id ? (int) AdminAuth::user()->id : null,
        ]);

        if ($updated) {
            if ($requestedStatus === 'published') {
                $freshPackage = TourPackage::find((int) $package->id) ?? $package;
                $notificationResult = (new PackageNotificationService())->queuePublishedPackageNotifications(
                    $freshPackage,
                    env_int('PACKAGE_NOTIFICATION_MIN_TAG_MATCHES', 3)
                );

                $message = 'Estado actualizado correctamente. Notificaciones encoladas: ' . (int) ($notificationResult['total'] ?? 0) . '.';
                if (env_bool('PACKAGE_NOTIFICATION_SEND_ENABLED', false) && env_bool('PACKAGE_NOTIFICATION_SEND_ON_PUBLISH', false)) {
                    try {
                        $processed = (new PackageNotificationService())->processPending(env_int('PACKAGE_NOTIFICATION_PUBLISH_PROCESS_LIMIT', 50));
                        $message .= ' Correos enviados ahora: ' . (int) ($processed['sent'] ?? 0) . '.';
                    } catch (\Throwable $exception) {
                        $message .= ' No se pudieron enviar inmediatamente: ' . $exception->getMessage();
                    }
                }
                Flash::success($message);
            } else {
                Flash::success('Estado actualizado correctamente.');
            }
        } else {
            Flash::error('No fue posible actualizar el estado.');
        }

        \redirect('/admin/packageTour');
        exit;
    }

    public function toggleFeatured()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $package = TourPackage::find($id);

        if (!$package) {
            Flash::error('El paquete no fue encontrado.');
            \redirect('/admin/packageTour');
            exit;
        }

        $newValue = isset($_POST['is_featured'])
            ? ((int) $_POST['is_featured'] === 1 ? 1 : 0)
            : ((int) ($package->is_featured ?? 0) === 1 ? 0 : 1);

        $updated = $package->update([
            'is_featured' => $newValue,
            'updated_by_admin_id' => AdminAuth::user()?->id ? (int) AdminAuth::user()->id : null,
        ]);

        if ($updated) {
            Flash::success($newValue === 1 ? 'Paquete marcado como destacado.' : 'Paquete quitado de destacados.');
        } else {
            Flash::error('No fue posible actualizar el destacado.');
        }

        \redirect('/admin/packageTour');
        exit;
    }

    public function processNotifications()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        try {
            $result = (new PackageNotificationService())->processPending((int) ($_POST['limit'] ?? 50));
            Flash::success('Notificaciones procesadas. Enviadas: ' . (int) ($result['sent'] ?? 0) . ', fallidas: ' . (int) ($result['failed'] ?? 0) . ', omitidas: ' . (int) ($result['skipped'] ?? 0) . '.');
        } catch (\Throwable $exception) {
            Flash::error('No fue posible procesar la cola: ' . $exception->getMessage());
        }

        edirect('/admin/packageTour');
        exit;
    }

    public function queueWeeklyRecommendations()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        try {
            $queued = (new PackageNotificationService())->queueRecommendationsForSubscribers((int) ($_POST['limit_per_customer'] ?? 1));
            Flash::success('Recomendaciones encoladas: ' . $queued . '. Puedes enviarlas con el botón de pendientes o con el cron de proceso.');
        } catch (\Throwable $exception) {
            Flash::error('No fue posible encolar recomendaciones: ' . $exception->getMessage());
        }

        edirect('/admin/packageTour');
        exit;
    }

    private function flashPackageSaveResult(array $result): void
    {
        $message = $result['message'] ?? 'Paquete guardado correctamente.';
        $notification = $result['data']['notification_result'] ?? null;

        if (is_array($notification)) {
            $queuedTotal = (int) ($notification['queued']['total'] ?? 0);
            $queuedMatches = (int) ($notification['queued']['tag_match'] ?? 0);
            $queuedNew = (int) ($notification['queued']['new_package'] ?? 0);
            $message .= ' Notificaciones encoladas: ' . $queuedTotal . ' (' . $queuedNew . ' avisos generales, ' . $queuedMatches . ' por coincidencia de etiquetas).';

            if (is_array($notification['processed'] ?? null)) {
                $message .= ' Enviadas ahora: ' . (int) ($notification['processed']['sent'] ?? 0) . '.';
                if (!empty($notification['processed']['error'])) {
                    $message .= ' Error de envío inmediato: ' . $notification['processed']['error'];
                }
            }
        }

        Flash::success($message);
    }

    private function getPublishBlockingErrors(TourPackage $package): array
    {
        $errors = [];

        if (trim((string) ($package->title ?? '')) === '') {
            $errors[] = 'falta el título.';
        }

        if (trim((string) ($package->slug ?? '')) === '') {
            $errors[] = 'falta el slug.';
        }

        if (trim((string) ($package->location_name ?? '')) === '') {
            $errors[] = 'falta la ubicación.';
        }

        if ((float) ($package->price_from ?? 0) <= 0) {
            $errors[] = 'el precio debe ser mayor a cero.';
        }

        if (trim((string) ($package->short_description ?? '')) === '') {
            $errors[] = 'falta la descripción corta.';
        }

        if (trim((string) ($package->general_description ?? '')) === '') {
            $errors[] = 'falta la descripción general.';
        }

        if (!TourPackageImage::coverByPackage((int) $package->id)) {
            $errors[] = 'falta la portada.';
        }

        return $errors;
    }

    protected function saveTemplateFromPost(array $input, ?int $adminId = null): array
    {
        $name = trim((string) ($input['template_name'] ?? ''));

        if ($name === '') {
            return [
                'success' => false,
                'message' => 'Escribe un nombre para la plantilla.',
                'errors' => ['template_name' => ['El nombre de la plantilla es obligatorio.']],
            ];
        }

        $payload = [
            'subtitle' => trim((string) ($input['subtitle'] ?? '')),
            'short_description' => trim((string) ($input['short_description'] ?? '')),
            'general_description' => trim((string) ($input['general_description'] ?? '')),
            'currency' => trim((string) ($input['currency'] ?? 'COP')) ?: 'COP',
            'duration_days' => trim((string) ($input['duration_days'] ?? '')),
            'duration_nights' => trim((string) ($input['duration_nights'] ?? '')),
            'includes' => $this->cleanStringList($input['includes'] ?? []),
            'excludes' => $this->cleanStringList($input['excludes'] ?? []),
            'highlights' => $this->cleanStringList($input['highlights'] ?? []),
            'conditions' => $this->cleanConditions($input['condition_titles'] ?? [], $input['condition_contents'] ?? []),
            'itinerary' => !empty($input['no_itinerary']) ? [] : $this->cleanItinerary($input['itinerary_day_number'] ?? [], $input['itinerary_title'] ?? [], $input['itinerary_content'] ?? []),
            'no_itinerary' => !empty($input['no_itinerary']) ? 1 : 0,
            'tag_ids' => array_values(array_unique(array_map('intval', $input['tag_ids'] ?? []))),
            'is_featured' => !empty($input['is_featured']) ? 1 : 0,
            'is_popular' => !empty($input['is_popular']) ? 1 : 0,
        ];

        $baseSlug = $this->slugifyTemplate($name);
        $slug = $baseSlug;
        $counter = 2;

        while (TourPackageTemplate::findBySlug($slug)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        try {
            $template = TourPackageTemplate::create([
                'uuid' => \uuid(),
                'name' => $name,
                'slug' => $slug,
                'description' => trim((string) ($input['template_description'] ?? '')) ?: null,
                'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'is_active' => 1,
                'created_by_admin_id' => $adminId,
            ]);
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'No fue posible guardar la plantilla. Verifica que la migración tour_package_templates esté ejecutada.',
                'errors' => ['template' => ['Tabla de plantillas no disponible o error interno.']],
            ];
        }

        if (!$template) {
            return [
                'success' => false,
                'message' => 'No fue posible guardar la plantilla.',
                'errors' => ['template' => ['Error interno al guardar la plantilla.']],
            ];
        }

        return [
            'success' => true,
            'message' => 'Plantilla guardada correctamente. Ya puedes seleccionarla en Plantilla rápida.',
            'errors' => [],
        ];
    }

    protected function cleanStringList($value): array
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        $items = array_map(fn($item) => trim((string) $item), $value);
        return array_values(array_filter($items, fn($item) => $item !== ''));
    }

    protected function cleanConditions($titles, $contents): array
    {
        if (!is_array($titles)) {
            $titles = [$titles];
        }
        if (!is_array($contents)) {
            $contents = [$contents];
        }

        $items = [];
        $max = max(count($titles), count($contents));

        for ($i = 0; $i < $max; $i++) {
            $title = trim((string) ($titles[$i] ?? ''));
            $content = trim((string) ($contents[$i] ?? ''));

            if ($title === '' && $content === '') {
                continue;
            }

            $items[] = ['title' => $title ?: 'Condición', 'content' => $content];
        }

        return $items;
    }

    protected function cleanItinerary($dayNumbers, $titles, $contents): array
    {
        if (!is_array($dayNumbers)) {
            $dayNumbers = [$dayNumbers];
        }
        if (!is_array($titles)) {
            $titles = [$titles];
        }
        if (!is_array($contents)) {
            $contents = [$contents];
        }

        $items = [];
        $max = max(count($dayNumbers), count($titles), count($contents));

        for ($i = 0; $i < $max; $i++) {
            $day = (int) ($dayNumbers[$i] ?? 0);
            $title = trim((string) ($titles[$i] ?? ''));
            $content = trim((string) ($contents[$i] ?? ''));

            if ($day <= 0 && $title === '' && $content === '') {
                continue;
            }
            if ($day > 0 && $title === '' && $content === '') {
                continue;
            }

            $day = $day > 0 ? $day : count($items) + 1;
            $items[] = ['day_number' => $day, 'title' => $title ?: ('Día ' . $day), 'content' => $content];
        }

        return $items;
    }

    protected function slugifyTemplate(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT', $value) ?: $value;
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);
        $value = trim((string) $value, '-');
        return $value !== '' ? $value : 'plantilla';
    }

}
