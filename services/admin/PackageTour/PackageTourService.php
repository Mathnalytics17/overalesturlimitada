<?php

namespace app\Services\Admin\PackageTour;

use app\Models\TourPackage;
use app\Models\TourPackageCondition;
use app\Models\TourPackageHighlight;
use app\Models\TourPackageImage;
use app\Models\TourPackageInclusion;
use app\Models\TourPackageTagItem;
use app\Models\TourPackageItinerary;
class PackageTourService
{
    public function create(array $input, ?int $adminId = null, array $files = []): array
    {
        $data = $this->normalize($input);
        $errors = $this->validate($data, true, $files);

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Revisa los campos del formulario.',
                'errors' => $errors,
                'old' => $input,
                'data' => [],
            ];
        }

        $existing = TourPackage::findBySlug($data['slug']);
        if ($existing) {
            return [
                'success' => false,
                'message' => 'El slug ya existe.',
                'errors' => [
                    'slug' => ['El slug ya está en uso.'],
                ],
                'old' => $input,
                'data' => [],
            ];
        }

        $packageUuid = \uuid();

        $package = TourPackage::create([
            'uuid' => $packageUuid,
            'slug' => $data['slug'],
            'title' => $data['title'],
            'subtitle' => $data['subtitle'],
            'short_description' => $data['short_description'],
            'general_description' => $data['general_description'],
            'location_name' => $data['location_name'],
            'country_id' => null,
            'city_id' => null,
            'price_from' => $data['price_from'],
            'currency' => $data['currency'],
            'duration_days' => $data['duration_days'],
            'duration_nights' => $data['duration_nights'],
            'status' => $data['status'],
            'is_featured' => $data['is_featured'],
            'is_popular' => $data['is_popular'],
            'sort_order' => $data['sort_order'],
            'cover_image_id' => null,
            'published_at' => $data['status'] === 'published' ? date('Y-m-d H:i:s') : null,
            'created_by_admin_id' => $adminId,
            'updated_by_admin_id' => $adminId,
        ]);

        if (!$package) {
            return [
                'success' => false,
                'message' => 'No fue posible crear el paquete.',
                'errors' => [
                    'system' => ['Error interno al crear el paquete.'],
                ],
                'old' => $input,
                'data' => [],
            ];
        }

        $uploadService = new PackageTourImageUploadService();
        $uploadResult = $uploadService->uploadPackageImages($packageUuid, $data['slug'], $files);

        if (!$uploadResult['success']) {
            return [
                'success' => false,
                'message' => 'No fue posible subir las imágenes.',
                'errors' => $uploadResult['errors'] ?? [],
                'old' => $input,
                'data' => [],
            ];
        }

        $coverPath = $uploadResult['data']['cover_path'] ?? null;
        $galleryPaths = $uploadResult['data']['gallery_paths'] ?? [];

        if ($coverPath || !empty($galleryPaths)) {
            $this->syncImages((int)$package->id, $coverPath, $galleryPaths);
        }

        $this->syncTags((int)$package->id, $data['tag_ids']);
$this->syncInclusions((int)$package->id, 'include', $data['includes']);
$this->syncInclusions((int)$package->id, 'exclude', $data['excludes']);
$this->syncConditions((int)$package->id, $data['conditions']);
$this->syncHighlights((int)$package->id, $data['highlights']);
$this->syncItinerary((int)$package->id, $data['itinerary']);

        return [
            'success' => true,
            'message' => 'Paquete creado correctamente.',
            'errors' => [],
            'old' => [],
            'data' => [
                'package' => $package->toArray(),
            ],
        ];
    }

    public function update(int $packageId, array $input, ?int $adminId = null, array $files = []): array
    {
        $package = TourPackage::find($packageId);

        if (!$package) {
            return [
                'success' => false,
                'message' => 'Paquete no encontrado.',
                'errors' => [
                    'package' => ['Paquete no encontrado.'],
                ],
                'old' => $input,
                'data' => [],
            ];
        }

        $data = $this->normalize($input);
        $errors = $this->validate($data, false, $files, $package);

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Revisa los campos del formulario.',
                'errors' => $errors,
                'old' => $input,
                'data' => [],
            ];
        }

        $existing = TourPackage::findBySlug($data['slug']);
        if ($existing && (int)$existing->id !== (int)$package->id) {
            return [
                'success' => false,
                'message' => 'El slug ya existe.',
                'errors' => [
                    'slug' => ['El slug ya está en uso.'],
                ],
                'old' => $input,
                'data' => [],
            ];
        }

        $updated = $package->update([
            'slug' => $data['slug'],
            'title' => $data['title'],
            'subtitle' => $data['subtitle'],
            'short_description' => $data['short_description'],
            'general_description' => $data['general_description'],
            'location_name' => $data['location_name'],
            'price_from' => $data['price_from'],
            'currency' => $data['currency'],
            'duration_days' => $data['duration_days'],
            'duration_nights' => $data['duration_nights'],
            'status' => $data['status'],
            'is_featured' => $data['is_featured'],
            'is_popular' => $data['is_popular'],
            'sort_order' => $data['sort_order'],
            'published_at' => $data['status'] === 'published'
                ? ($package->published_at ?? date('Y-m-d H:i:s'))
                : null,
            'updated_by_admin_id' => $adminId,
        ]);

        if (!$updated) {
            return [
                'success' => false,
                'message' => 'No fue posible actualizar el paquete.',
                'errors' => [
                    'system' => ['Error interno al actualizar el paquete.'],
                ],
                'old' => $input,
                'data' => [],
            ];
        }

        $uploadService = new PackageTourImageUploadService();
        $uploadResult = $uploadService->uploadPackageImages((string)$package->uuid, $data['slug'], $files);

        if (!$uploadResult['success']) {
            return [
                'success' => false,
                'message' => 'No fue posible subir las imágenes.',
                'errors' => $uploadResult['errors'] ?? [],
                'old' => $input,
                'data' => [],
            ];
        }

        $newCoverPath = $uploadResult['data']['cover_path'] ?? null;
        $newGalleryPaths = $uploadResult['data']['gallery_paths'] ?? [];

        if (!empty($input['remove_cover'])) {
            $this->removeCover((int)$package->id);
        }

        if (!empty($newCoverPath)) {
            $this->replaceCover((int)$package->id, $newCoverPath);
        }

        if (!empty($input['clear_gallery'])) {
            $this->clearGallery((int)$package->id);
        }

        $removeGalleryIds = array_map('intval', $input['remove_gallery_ids'] ?? []);
        if (!empty($removeGalleryIds)) {
            $this->removeGalleryByIds((int)$package->id, $removeGalleryIds);
        }

        if (!empty($newGalleryPaths)) {
            $this->appendGallery((int)$package->id, $newGalleryPaths);
        }

        $this->replaceTags((int)$package->id, $data['tag_ids']);
        $this->replaceInclusions((int)$package->id, 'include', $data['includes']);
        $this->replaceInclusions((int)$package->id, 'exclude', $data['excludes']);
        $this->replaceConditions((int)$package->id, $data['conditions']);
        $this->replaceHighlights((int)$package->id, $data['highlights']);
        $this->replaceItinerary((int)$package->id, $data['itinerary']);
       
        return [
            'success' => true,
            'message' => 'Paquete actualizado correctamente.',
            'errors' => [],
            'old' => [],
            'data' => [],
        ];
    }

    protected function validate(array $data, bool $isCreate, array $files = [], ?TourPackage $package = null): array
    {
        $errors = [];

        if ($data['title'] === '') {
            $errors['title'][] = 'El título es obligatorio.';
        }

        if ($data['slug'] === '') {
            $errors['slug'][] = 'El slug es obligatorio.';
        }

        $isPublishing = $data['status'] === 'published';

        if ($isPublishing) {
            if ($data['short_description'] === '') {
                $errors['short_description'][] = 'La descripción corta es obligatoria para publicar.';
            }

            if ($data['general_description'] === '') {
                $errors['general_description'][] = 'La descripción general es obligatoria para publicar.';
            }

            if ($data['location_name'] === '') {
                $errors['location_name'][] = 'La ubicación es obligatoria para publicar.';
            }

            if ($data['price_from'] <= 0) {
                $errors['price_from'][] = 'El precio debe ser mayor a cero para publicar.';
            }

            if ($isCreate && empty($files['cover_image_file']['name'] ?? '')) {
                $errors['cover_image_file'][] = 'La portada es obligatoria para publicar.';
            }

            if (!$isCreate && $package) {
                $currentCover = TourPackageImage::coverByPackage((int)$package->id);
                $willRemoveCover = !empty($data['remove_cover']);
                $newCoverSelected = !empty($files['cover_image_file']['name'] ?? '');

                if (!$currentCover && !$newCoverSelected) {
                    $errors['cover_image_file'][] = 'La portada es obligatoria para publicar.';
                }

                if ($currentCover && $willRemoveCover && !$newCoverSelected) {
                    $errors['cover_image_file'][] = 'No puedes publicar sin portada.';
                }
            }
        }

        if ($data['price_from'] < 0) {
            $errors['price_from'][] = 'El precio no puede ser negativo.';
        }

        return $errors;
    }

    protected function normalize(array $input): array
    {
        $title = trim((string)($input['title'] ?? ''));
        $slug = trim((string)($input['slug'] ?? ''));

        if ($slug === '' && $title !== '') {
            $slug = $this->slugify($title);
        }

        $status = trim((string)($input['status'] ?? 'draft'));
        $submitAction = trim((string)($input['submit_action'] ?? ''));

        if ($submitAction === 'publish') {
            $status = 'published';
        } elseif ($submitAction === 'draft') {
            $status = 'draft';
        }

        return [
    'title' => $title,
    'slug' => $slug,
    'subtitle' => trim((string)($input['subtitle'] ?? '')),
    'short_description' => trim((string)($input['short_description'] ?? '')),
    'general_description' => trim((string)($input['general_description'] ?? '')),
    'location_name' => trim((string)($input['location_name'] ?? '')),
    'price_from' => (float)($input['price_from'] ?? 0),
    'currency' => trim((string)($input['currency'] ?? 'COP')) ?: 'COP',
    'duration_days' => $input['duration_days'] !== '' ? (int)$input['duration_days'] : null,
    'duration_nights' => $input['duration_nights'] !== '' ? (int)$input['duration_nights'] : null,
    'status' => in_array($status, ['draft', 'published', 'archived'], true) ? $status : 'draft',
    'is_featured' => !empty($input['is_featured']) ? 1 : 0,
    'is_popular' => !empty($input['is_popular']) ? 1 : 0,
    'sort_order' => (int)($input['sort_order'] ?? 0),
    'includes' => $this->normalizeStringList($input['includes'] ?? []),
    'excludes' => $this->normalizeStringList($input['excludes'] ?? []),
    'highlights' => $this->normalizeStringList($input['highlights'] ?? []),
    'conditions' => $this->normalizeConditions(
        $input['condition_titles'] ?? [],
        $input['condition_contents'] ?? []
    ),
    'itinerary' => $this->normalizeItinerary(
        $input['itinerary_day_number'] ?? [],
        $input['itinerary_title'] ?? [],
        $input['itinerary_content'] ?? []
    ),
    'tag_ids' => array_values(array_unique(array_map('intval', $input['tag_ids'] ?? []))),
    'remove_cover' => !empty($input['remove_cover']) ? 1 : 0,
];
    }


    protected function normalizeItinerary($dayNumbers, $titles, $contents): array
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

    $max = max(count($dayNumbers), count($titles), count($contents));
    $items = [];

    for ($i = 0; $i < $max; $i++) {
        $dayNumber = (int)($dayNumbers[$i] ?? 0);
        $title = trim((string)($titles[$i] ?? ''));
        $content = trim((string)($contents[$i] ?? ''));

        if ($dayNumber <= 0 && $title === '' && $content === '') {
            continue;
        }

        if ($dayNumber <= 0) {
            $dayNumber = count($items) + 1;
        }

        $items[] = [
            'day_number' => $dayNumber,
            'title' => $title !== '' ? $title : 'Día ' . $dayNumber,
            'content' => $content,
        ];
    }

    usort($items, function ($a, $b) {
        return (int)$a['day_number'] <=> (int)$b['day_number'];
    });

    return array_values($items);
}


protected function syncItinerary(int $packageId, array $items): void
{
    $sort = 1;

    foreach ($items as $item) {
        TourPackageItinerary::create([
            'uuid' => \uuid(),
            'tour_package_id' => $packageId,
            'day_number' => (int)($item['day_number'] ?? $sort),
            'title' => $item['title'] ?: ('Día ' . $sort),
            'content' => $item['content'] ?? '',
            'sort_order' => $sort,
        ]);

        $sort++;
    }
}

protected function replaceItinerary(int $packageId, array $items): void
{
    $existing = TourPackageItinerary::byPackage($packageId);

    foreach ($existing as $row) {
        $row->delete();
    }

    $this->syncItinerary($packageId, $items);
}
    protected function normalizeStringList($value): array
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        $items = array_map(fn($item) => trim((string)$item), $value);
        $items = array_filter($items, fn($item) => $item !== '');

        return array_values($items);
    }

    protected function normalizeConditions($titles, $contents): array
    {
        if (!is_array($titles)) {
            $titles = [$titles];
        }

        if (!is_array($contents)) {
            $contents = [$contents];
        }

        $max = max(count($titles), count($contents));
        $items = [];

        for ($i = 0; $i < $max; $i++) {
            $title = trim((string)($titles[$i] ?? ''));
            $content = trim((string)($contents[$i] ?? ''));

            if ($title === '' && $content === '') {
                continue;
            }

            $items[] = [
                'title' => $title !== '' ? $title : 'Condición',
                'content' => $content,
            ];
        }

        return $items;
    }

    protected function syncImages(int $packageId, ?string $coverImage, array $gallery): void
    {
        $sort = 1;
        $coverId = null;

        if (!empty($coverImage)) {
            $cover = TourPackageImage::create([
                'uuid' => \uuid(),
                'tour_package_id' => $packageId,
                'image_path' => $coverImage,
                'alt_text' => null,
                'title' => null,
                'sort_order' => 0,
                'is_cover' => 1,
            ]);

            $coverId = $cover?->id ?? null;
        }

        foreach ($gallery as $path) {
            TourPackageImage::create([
                'uuid' => \uuid(),
                'tour_package_id' => $packageId,
                'image_path' => $path,
                'alt_text' => null,
                'title' => null,
                'sort_order' => $sort++,
                'is_cover' => 0,
            ]);
        }

        if ($coverId) {
            $package = TourPackage::find($packageId);
            $package?->update(['cover_image_id' => $coverId]);
        }
    }

    protected function replaceCover(int $packageId, string $coverPath): void
    {
        $existingCover = TourPackageImage::coverByPackage($packageId);
        if ($existingCover) {
            $existingCover->delete();
        }

        $cover = TourPackageImage::create([
            'uuid' => \uuid(),
            'tour_package_id' => $packageId,
            'image_path' => $coverPath,
            'alt_text' => null,
            'title' => null,
            'sort_order' => 0,
            'is_cover' => 1,
        ]);

        if ($cover) {
            $package = TourPackage::find($packageId);
            $package?->update(['cover_image_id' => $cover->id]);
        }
    }

    protected function removeCover(int $packageId): void
    {
        $cover = TourPackageImage::coverByPackage($packageId);
        if ($cover) {
            $cover->delete();
        }

        $package = TourPackage::find($packageId);
        $package?->update(['cover_image_id' => null]);
    }

    protected function appendGallery(int $packageId, array $galleryPaths): void
    {
        $existing = TourPackageImage::byPackage($packageId);
        $maxSort = 0;

        foreach ($existing as $img) {
            if ((int)($img->is_cover ?? 0) !== 1) {
                $maxSort = max($maxSort, (int)($img->sort_order ?? 0));
            }
        }

        foreach ($galleryPaths as $path) {
            $maxSort++;
            TourPackageImage::create([
                'uuid' => \uuid(),
                'tour_package_id' => $packageId,
                'image_path' => $path,
                'alt_text' => null,
                'title' => null,
                'sort_order' => $maxSort,
                'is_cover' => 0,
            ]);
        }
    }

    protected function clearGallery(int $packageId): void
    {
        $images = TourPackageImage::byPackage($packageId);
        foreach ($images as $image) {
            if ((int)($image->is_cover ?? 0) !== 1) {
                $image->delete();
            }
        }
    }

    protected function removeGalleryByIds(int $packageId, array $ids): void
    {
        $images = TourPackageImage::byPackage($packageId);
        foreach ($images as $image) {
            if ((int)($image->is_cover ?? 0) === 1) {
                continue;
            }

            if (in_array((int)$image->id, $ids, true)) {
                $image->delete();
            }
        }
    }

    protected function syncTags(int $packageId, array $tagIds): void
    {
        foreach ($tagIds as $tagId) {
            TourPackageTagItem::create([
                'tour_package_id' => $packageId,
                'tag_id' => (int)$tagId,
            ]);
        }
    }

    protected function replaceTags(int $packageId, array $tagIds): void
    {
        $existing = TourPackageTagItem::byPackage($packageId);
        foreach ($existing as $row) {
            $row->delete();
        }

        $this->syncTags($packageId, $tagIds);
    }

    protected function syncInclusions(int $packageId, string $type, array $items): void
    {
        $sort = 1;
        foreach ($items as $item) {
            TourPackageInclusion::create([
                'uuid' => \uuid(),
                'tour_package_id' => $packageId,
                'type' => $type,
                'title' => null,
                'content' => $item,
                'sort_order' => $sort++,
                'is_active' => 1,
            ]);
        }
    }

    protected function replaceInclusions(int $packageId, string $type, array $items): void
    {
        $existing = TourPackageInclusion::byPackageAndType($packageId, $type);
        foreach ($existing as $row) {
            $row->delete();
        }

        $this->syncInclusions($packageId, $type, $items);
    }

    protected function syncConditions(int $packageId, array $items): void
    {
        $sort = 1;
        foreach ($items as $item) {
            TourPackageCondition::create([
                'uuid' => \uuid(),
                'tour_package_id' => $packageId,
                'title' => $item['title'] ?: 'Condición',
                'content' => $item['content'] ?: '',
                'sort_order' => $sort++,
                'is_active' => 1,
            ]);
        }
    }

    protected function replaceConditions(int $packageId, array $items): void
    {
        $existing = TourPackageCondition::byPackage($packageId);
        foreach ($existing as $row) {
            $row->delete();
        }

        $this->syncConditions($packageId, $items);
    }

    protected function syncHighlights(int $packageId, array $items): void
    {
        $sort = 1;
        foreach ($items as $item) {
            TourPackageHighlight::create([
                'uuid' => \uuid(),
                'tour_package_id' => $packageId,
                'title' => $item,
                'icon' => null,
                'sort_order' => $sort++,
                'is_active' => 1,
            ]);
        }
    }

    protected function replaceHighlights(int $packageId, array $items): void
    {
        $existing = TourPackageHighlight::byPackage($packageId);
        foreach ($existing as $row) {
            $row->delete();
        }

        $this->syncHighlights($packageId, $items);
    }

    protected function slugify(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/[áàäâ]/u', 'a', $value);
        $value = preg_replace('/[éèëê]/u', 'e', $value);
        $value = preg_replace('/[íìïî]/u', 'i', $value);
        $value = preg_replace('/[óòöô]/u', 'o', $value);
        $value = preg_replace('/[úùüû]/u', 'u', $value);
        $value = preg_replace('/ñ/u', 'n', $value);
        $value = preg_replace('/[^a-z0-9]+/u', '-', $value);
        $value = trim((string)$value, '-');

        return $value !== '' ? $value : 'paquete-' . date('YmdHis');
    }
}