<?php

namespace app\Controllers\admin\packageTour;

use app\Core\Controller;
use app\Core\Csrf;
use app\Core\Flash;
use app\Models\TourPackageTag;
use app\Models\TourPackageTagItem;

class PackageTagController extends Controller
{
    public function index()
    {
        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'status' => trim((string) ($_GET['status'] ?? '')),
        ];

        $tags = TourPackageTag::adminList($filters);
        $usage = TourPackageTagItem::usageCounts();

        return $this->render('admin/packageTour/tags/list', [
            'page_title' => 'Etiquetas de paquetes',
            'page_subtitle' => 'Gestiona los estilos de viaje usados en paquetes y preferencias de clientes.',
            'active' => 'package_tags',
            'tags' => $tags,
            'usage' => $usage,
            'filters' => $filters,
        ], 'adminUserLayout');
    }

    public function create()
    {
        return $this->render('admin/packageTour/tags/create', [
            'page_title' => 'Crear etiqueta',
            'page_subtitle' => 'Crea una etiqueta para clasificar paquetes turísticos.',
            'active' => 'package_tags',
            'errors' => [],
            'old' => [
                'color' => '#1FA4CF',
                'is_active' => '1',
            ],
        ], 'adminUserLayout');
    }

    public function store()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $data = $this->normalizeInput($_POST);
        $errors = $this->validate($data);

        if ($errors !== []) {
            return $this->render('admin/packageTour/tags/create', [
                'page_title' => 'Crear etiqueta',
                'page_subtitle' => 'Crea una etiqueta para clasificar paquetes turísticos.',
                'active' => 'package_tags',
                'errors' => $errors,
                'old' => $data,
            ], 'adminUserLayout');
        }

        $created = TourPackageTag::create([
            'uuid' => uuid(),
            'name' => $data['name'],
            'slug' => $data['slug'],
            'color' => $data['color'],
            'is_active' => $data['is_active'],
        ]);

        if (!$created) {
            Flash::error('No se pudo crear la etiqueta. Intenta nuevamente.');
            \redirect('/admin/packageTour/tags/create');
        }

        Flash::success('Etiqueta creada correctamente. Ya puedes usarla en los paquetes.');
        \redirect('/admin/packageTour/tags');
    }

    public function edit()
    {
        $tag = $this->findTagOrRedirect();

        return $this->render('admin/packageTour/tags/edit', [
            'page_title' => 'Editar etiqueta',
            'page_subtitle' => 'Actualiza el nombre, color o estado de la etiqueta.',
            'active' => 'package_tags',
            'tag' => $tag,
            'errors' => [],
            'old' => $tag->toArray(),
            'usageCount' => TourPackageTagItem::countByTagId((int) $tag->id),
        ], 'adminUserLayout');
    }

    public function update()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $tag = $this->findTagOrRedirect((int) ($_POST['id'] ?? 0));
        $data = $this->normalizeInput($_POST, (int) $tag->id);
        $errors = $this->validate($data, (int) $tag->id);

        if ($errors !== []) {
            return $this->render('admin/packageTour/tags/edit', [
                'page_title' => 'Editar etiqueta',
                'page_subtitle' => 'Actualiza el nombre, color o estado de la etiqueta.',
                'active' => 'package_tags',
                'tag' => $tag,
                'errors' => $errors,
                'old' => $data,
                'usageCount' => TourPackageTagItem::countByTagId((int) $tag->id),
            ], 'adminUserLayout');
        }

        $tag->update([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'color' => $data['color'],
            'is_active' => $data['is_active'],
        ]);

        Flash::success('Etiqueta actualizada correctamente.');
        \redirect('/admin/packageTour/tags');
    }

    public function delete()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $tag = $this->findTagOrRedirect((int) ($_POST['id'] ?? 0));
        $usageCount = TourPackageTagItem::countByTagId((int) $tag->id);

        if ($usageCount > 0) {
            $tag->update(['is_active' => 0]);
            Flash::warning('La etiqueta está usada por ' . $usageCount . ' paquete(s). La desactivé para no romper relaciones existentes.');
            \redirect('/admin/packageTour/tags');
        }

        $tag->delete();
        Flash::success('Etiqueta eliminada correctamente.');
        \redirect('/admin/packageTour/tags');
    }

    public function seed()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $seedTags = [
            ['Playa', '#0ea5e9'],
            ['Familiar', '#22c55e'],
            ['Luna de miel', '#e11d48'],
            ['Aventura', '#f97316'],
            ['Internacional', '#6366f1'],
            ['Nacional', '#14b8a6'],
            ['Todo incluido', '#8b5cf6'],
            ['Económico', '#84cc16'],
            ['Premium', '#f59e0b'],
            ['Naturaleza', '#16a34a'],
            ['Cultural', '#a855f7'],
            ['Crucero', '#0284c7'],
            ['Disney', '#ec4899'],
            ['Compras', '#64748b'],
            ['Fin de semana', '#0891b2'],
        ];

        $created = 0;
        foreach ($seedTags as [$name, $color]) {
            $slug = $this->slugify($name);
            $existingTag = TourPackageTag::findBySlug($slug);
            if ($existingTag) {
                if ((int) ($existingTag->is_active ?? 0) !== 1 || (string) ($existingTag->color ?? '') !== strtoupper($color)) {
                    $existingTag->update([
                        'name' => $name,
                        'color' => strtoupper($color),
                        'is_active' => 1,
                    ]);
                }
                continue;
            }

            if (TourPackageTag::create([
                'uuid' => uuid(),
                'name' => $name,
                'slug' => $slug,
                'color' => $color,
                'is_active' => 1,
            ])) {
                $created++;
            }
        }

        Flash::success($created > 0 ? "Se crearon {$created} etiquetas iniciales." : 'Las etiquetas iniciales ya estaban creadas.');
        \redirect('/admin/packageTour/tags');
    }

    private function normalizeInput(array $input, ?int $ignoreId = null): array
    {
        $name = trim((string) ($input['name'] ?? ''));
        $slug = trim((string) ($input['slug'] ?? ''));
        $color = trim((string) ($input['color'] ?? '#1FA4CF'));

        if ($slug === '' && $name !== '') {
            $slug = $this->slugify($name);
        } else {
            $slug = $this->slugify($slug);
        }

        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            $color = '#1FA4CF';
        }

        return [
            'id' => (int) ($input['id'] ?? 0),
            'name' => $name,
            'slug' => $slug,
            'color' => strtoupper($color),
            'is_active' => isset($input['is_active']) ? 1 : 0,
        ];
    }

    private function validate(array $data, ?int $ignoreId = null): array
    {
        $errors = [];

        if ($data['name'] === '') {
            $errors['name'][] = 'El nombre es obligatorio.';
        }

        if ($data['slug'] === '') {
            $errors['slug'][] = 'El slug es obligatorio.';
        }

        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $data['slug'])) {
            $errors['slug'][] = 'Usa un slug válido. Ejemplo: luna-de-miel.';
        }

        $existing = TourPackageTag::findBySlug($data['slug']);
        if ($existing && (int) $existing->id !== (int) $ignoreId) {
            $errors['slug'][] = 'Ya existe una etiqueta con ese slug.';
        }

        return $errors;
    }

    private function findTagOrRedirect(?int $id = null): TourPackageTag
    {
        $id = $id ?: (int) ($_GET['id'] ?? 0);
        $tag = TourPackageTag::find($id);

        if (!$tag) {
            Flash::error('La etiqueta no fue encontrada.');
            \redirect('/admin/packageTour/tags');
        }

        return $tag;
    }

    private function slugify(string $value): string
    {
        $value = trim(mb_strtolower($value));
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?? '';
        $value = trim($value, '-');

        return $value !== '' ? mb_strtolower($value) : 'etiqueta';
    }
}
