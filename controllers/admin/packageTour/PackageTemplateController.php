<?php

namespace app\Controllers\admin\packageTour;

use app\Core\AdminAuth;
use app\Core\Controller;
use app\Core\Csrf;
use app\Core\Flash;
use app\Models\Currency;
use app\Models\TourPackageTag;
use app\Models\TourPackageTemplate;

class PackageTemplateController extends Controller
{
    public function index()
    {
        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'status' => trim((string) ($_GET['status'] ?? '')),
        ];

        return $this->render('admin/packageTour/templates/list', [
            'page_title' => 'Plantillas de paquetes',
            'page_subtitle' => 'Administra estructuras reutilizables para crear paquetes más rápido.',
            'active' => 'package_templates',
            'templates' => TourPackageTemplate::adminList($filters),
            'filters' => $filters,
        ], 'adminUserLayout');
    }

    public function create()
    {
        return $this->render('admin/packageTour/templates/create', [
            'page_title' => 'Crear plantilla',
            'page_subtitle' => 'Define contenido reutilizable para paquetes turísticos.',
            'active' => 'package_templates',
            'errors' => [],
            'old' => [
                'currency' => 'COP',
                'is_active' => '1',
                'includes' => ['Tiquetes aéreos', 'Alojamiento', 'Alimentación según itinerario', 'Traslados', 'Guía local', 'Asistencia médica'],
                'excludes' => ['No incluye ningún servicio no especificado'],
                'highlights' => [],
                'condition_titles' => [],
                'condition_contents' => [],
                'itinerary_day_number' => [],
                'itinerary_title' => [],
                'itinerary_content' => [],
                'tag_ids' => [],
            ],
            'tags' => TourPackageTag::activeList(),
            'currencies' => Currency::activeList(),
        ], 'adminUserLayout');
    }

    public function store()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $data = $this->normalize($_POST);
        $errors = $this->validate($data);

        if ($errors !== []) {
            return $this->render('admin/packageTour/templates/create', [
                'page_title' => 'Crear plantilla',
                'page_subtitle' => 'Define contenido reutilizable para paquetes turísticos.',
                'active' => 'package_templates',
                'errors' => $errors,
                'old' => $_POST,
                'tags' => TourPackageTag::activeList(),
                'currencies' => Currency::activeList(),
            ], 'adminUserLayout');
        }

        $created = TourPackageTemplate::create([
            'uuid' => uuid(),
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'],
            'payload_json' => json_encode($data['payload'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'is_active' => $data['is_active'],
            'created_by_admin_id' => AdminAuth::user()?->id ? (int) AdminAuth::user()->id : null,
            'updated_by_admin_id' => AdminAuth::user()?->id ? (int) AdminAuth::user()->id : null,
        ]);

        if (!$created) {
            Flash::error('No fue posible crear la plantilla.');
            redirect('/admin/packageTour/templates/create');
            exit;
        }

        Flash::success('Plantilla creada correctamente. Ya puedes usarla en Plantilla rápida.');
        redirect('/admin/packageTour/templates');
        exit;
    }

    public function edit()
    {
        $template = $this->findTemplateOrRedirect();
        $old = $this->templateToOld($template);

        return $this->render('admin/packageTour/templates/edit', [
            'page_title' => 'Editar plantilla',
            'page_subtitle' => 'Actualiza el contenido reutilizable de la plantilla.',
            'active' => 'package_templates',
            'template' => $template,
            'errors' => [],
            'old' => $old,
            'tags' => TourPackageTag::activeList(),
            'currencies' => Currency::activeList(),
        ], 'adminUserLayout');
    }

    public function update()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $template = $this->findTemplateOrRedirect((int) ($_POST['id'] ?? 0));
        $data = $this->normalize($_POST, (int) $template->id);
        $errors = $this->validate($data, (int) $template->id);

        if ($errors !== []) {
            return $this->render('admin/packageTour/templates/edit', [
                'page_title' => 'Editar plantilla',
                'page_subtitle' => 'Actualiza el contenido reutilizable de la plantilla.',
                'active' => 'package_templates',
                'template' => $template,
                'errors' => $errors,
                'old' => $_POST,
                'tags' => TourPackageTag::activeList(),
                'currencies' => Currency::activeList(),
            ], 'adminUserLayout');
        }

        $template->update([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'],
            'payload_json' => json_encode($data['payload'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'is_active' => $data['is_active'],
            'updated_by_admin_id' => AdminAuth::user()?->id ? (int) AdminAuth::user()->id : null,
        ]);

        Flash::success('Plantilla actualizada correctamente.');
        redirect('/admin/packageTour/templates');
        exit;
    }

    public function toggle()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $template = $this->findTemplateOrRedirect((int) ($_POST['id'] ?? 0));
        $newValue = (int) ($template->is_active ?? 0) === 1 ? 0 : 1;
        $template->update([
            'is_active' => $newValue,
            'updated_by_admin_id' => AdminAuth::user()?->id ? (int) AdminAuth::user()->id : null,
        ]);

        Flash::success($newValue === 1 ? 'Plantilla activada correctamente.' : 'Plantilla desactivada correctamente.');
        redirect('/admin/packageTour/templates');
        exit;
    }

    public function delete()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $template = $this->findTemplateOrRedirect((int) ($_POST['id'] ?? 0));
        $template->delete();
        Flash::success('Plantilla eliminada correctamente.');
        redirect('/admin/packageTour/templates');
        exit;
    }

    private function normalize(array $input, ?int $ignoreId = null): array
    {
        $name = trim((string) ($input['name'] ?? ''));
        $slug = $this->slugify(trim((string) ($input['slug'] ?? '')) ?: $name);
        $currency = strtoupper(trim((string) ($input['currency'] ?? 'COP'))) ?: 'COP';

        $payload = [
            'title' => trim((string) ($input['title'] ?? '')),
            'subtitle' => trim((string) ($input['subtitle'] ?? '')),
            'location_name' => trim((string) ($input['location_name'] ?? '')),
            'price_from' => trim((string) ($input['price_from'] ?? '')),
            'short_description' => trim((string) ($input['short_description'] ?? '')),
            'general_description' => trim((string) ($input['general_description'] ?? '')),
            'currency' => $currency,
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

        return [
            'id' => (int) ($input['id'] ?? 0),
            'name' => $name,
            'slug' => $slug,
            'description' => trim((string) ($input['description'] ?? '')) ?: null,
            'is_active' => isset($input['is_active']) ? 1 : 0,
            'payload' => $payload,
        ];
    }

    private function validate(array $data, ?int $ignoreId = null): array
    {
        $errors = [];

        if ($data['name'] === '') {
            $errors['name'][] = 'El nombre de la plantilla es obligatorio.';
        }
        if ($data['slug'] === '') {
            $errors['slug'][] = 'El slug es obligatorio.';
        }

        $existing = TourPackageTemplate::findBySlug($data['slug']);
        if ($existing && (!$ignoreId || (int) $existing->id !== (int) $ignoreId)) {
            $errors['slug'][] = 'Ya existe una plantilla con este slug.';
        }

        if (!Currency::findByCode((string) ($data['payload']['currency'] ?? 'COP'))) {
            $errors['currency'][] = 'Selecciona una moneda válida.';
        }

        return $errors;
    }

    private function templateToOld(TourPackageTemplate $template): array
    {
        $payload = $template->payload();
        $conditions = $payload['conditions'] ?? [];
        $itinerary = $payload['itinerary'] ?? [];

        return [
            'id' => (int) $template->id,
            'name' => (string) ($template->name ?? ''),
            'slug' => (string) ($template->slug ?? ''),
            'description' => (string) ($template->description ?? ''),
            'is_active' => (int) ($template->is_active ?? 0) === 1 ? '1' : '',
            'title' => (string) ($payload['title'] ?? ''),
            'subtitle' => (string) ($payload['subtitle'] ?? ''),
            'location_name' => (string) ($payload['location_name'] ?? ''),
            'price_from' => (string) ($payload['price_from'] ?? ''),
            'short_description' => (string) ($payload['short_description'] ?? ''),
            'general_description' => (string) ($payload['general_description'] ?? ''),
            'currency' => (string) ($payload['currency'] ?? 'COP'),
            'duration_days' => (string) ($payload['duration_days'] ?? ''),
            'duration_nights' => (string) ($payload['duration_nights'] ?? ''),
            'includes' => $payload['includes'] ?? [],
            'excludes' => $payload['excludes'] ?? [],
            'highlights' => $payload['highlights'] ?? [],
            'condition_titles' => array_map(fn($item) => (string) ($item['title'] ?? ($item[0] ?? '')), $conditions),
            'condition_contents' => array_map(fn($item) => (string) ($item['content'] ?? ($item[1] ?? '')), $conditions),
            'itinerary_day_number' => array_map(fn($item) => (string) ($item['day_number'] ?? ($item['day'] ?? '')), $itinerary),
            'itinerary_title' => array_map(fn($item) => (string) ($item['title'] ?? ''), $itinerary),
            'itinerary_content' => array_map(fn($item) => (string) ($item['content'] ?? ''), $itinerary),
            'no_itinerary' => !empty($payload['no_itinerary']) ? '1' : '',
            'tag_ids' => array_map('intval', $payload['tag_ids'] ?? []),
            'is_featured' => !empty($payload['is_featured']) ? '1' : '',
            'is_popular' => !empty($payload['is_popular']) ? '1' : '',
        ];
    }

    private function findTemplateOrRedirect(?int $id = null): TourPackageTemplate
    {
        $id = $id ?: (int) ($_GET['id'] ?? 0);
        $template = TourPackageTemplate::find($id);

        if (!$template) {
            Flash::error('La plantilla no fue encontrada.');
            redirect('/admin/packageTour/templates');
            exit;
        }

        return $template;
    }

    private function cleanStringList($value): array
    {
        if (!is_array($value)) $value = [$value];
        $items = array_map(fn($item) => trim((string) $item), $value);
        return array_values(array_filter($items, fn($item) => $item !== ''));
    }

    private function cleanConditions($titles, $contents): array
    {
        if (!is_array($titles)) $titles = [$titles];
        if (!is_array($contents)) $contents = [$contents];

        $items = [];
        $max = max(count($titles), count($contents));
        for ($i = 0; $i < $max; $i++) {
            $title = trim((string) ($titles[$i] ?? ''));
            $content = trim((string) ($contents[$i] ?? ''));
            if ($title === '' && $content === '') continue;
            $items[] = ['title' => $title ?: 'Condición', 'content' => $content];
        }
        return $items;
    }

    private function cleanItinerary($dayNumbers, $titles, $contents): array
    {
        if (!is_array($dayNumbers)) $dayNumbers = [$dayNumbers];
        if (!is_array($titles)) $titles = [$titles];
        if (!is_array($contents)) $contents = [$contents];

        $items = [];
        $max = max(count($dayNumbers), count($titles), count($contents));
        for ($i = 0; $i < $max; $i++) {
            $day = (int) ($dayNumbers[$i] ?? 0);
            $title = trim((string) ($titles[$i] ?? ''));
            $content = trim((string) ($contents[$i] ?? ''));
            if ($day <= 0 && $title === '' && $content === '') continue;
            if ($day > 0 && $title === '' && $content === '') continue;
            $day = $day > 0 ? $day : count($items) + 1;
            $items[] = ['day_number' => $day, 'title' => $title ?: ('Día ' . $day), 'content' => $content];
        }
        return $items;
    }

    private function slugify(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT', $value) ?: $value;
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);
        $value = trim((string) $value, '-');
        return $value !== '' ? $value : 'plantilla';
    }
}
