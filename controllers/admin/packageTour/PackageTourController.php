<?php

namespace app\Controllers\admin\packageTour;

use app\Core\AdminAuth;
use app\Core\Controller;
use app\Core\Csrf;
use app\Models\TourPackage;
use app\Models\TourPackageCondition;
use app\Models\TourPackageHighlight;
use app\Models\TourPackageImage;
use app\Models\TourPackageInclusion;
use app\Models\TourPackageTag;
use app\Models\TourPackageTagItem;
use app\Services\Admin\PackageTour\PackageTourService;
use app\Models\TourPackageItinerary;
use app\Core\Flash;
class PackageTourController extends Controller
{
    public function index()
    {
        $packages = TourPackage::adminList();

        return $this->render('admin/packageTour/list', [
            'packages' => $packages,
        ], 'adminUserLayout');
    }

    public function create()
    {
        $tags = TourPackageTag::activeList();
        $old = [];

        $copyId = (int)($_GET['copy'] ?? 0);
        if ($copyId > 0) {
            $package = TourPackage::find($copyId);

            if ($package) {
                $includes = TourPackageInclusion::byPackageAndType((int)$package->id, 'include');
                $excludes = TourPackageInclusion::byPackageAndType((int)$package->id, 'exclude');
                $conditions = TourPackageCondition::byPackage((int)$package->id);
                $highlights = TourPackageHighlight::byPackage((int)$package->id);
                $selectedTagItems = TourPackageTagItem::byPackage((int)$package->id);

                $old = [
                    'title' => (string)($package->title ?? '') . ' copia',
                    'slug' => '',
                    'subtitle' => (string)($package->subtitle ?? ''),
                    'location_name' => (string)($package->location_name ?? ''),
                    'price_from' => (string)($package->price_from ?? ''),
                    'currency' => (string)($package->currency ?? 'COP'),
                    'duration_days' => (string)($package->duration_days ?? ''),
                    'duration_nights' => (string)($package->duration_nights ?? ''),
                    'status' => 'draft',
                    'sort_order' => (string)($package->sort_order ?? 0),
                    'short_description' => (string)($package->short_description ?? ''),
                    'general_description' => (string)($package->general_description ?? ''),
                    'includes' => array_map(fn($item) => (string)($item->content ?? ''), $includes),
                    'excludes' => array_map(fn($item) => (string)($item->content ?? ''), $excludes),
                    'highlights' => array_map(fn($item) => (string)($item->title ?? ''), $highlights),
                    'condition_titles' => array_map(fn($item) => (string)($item->title ?? ''), $conditions),
                    'condition_contents' => array_map(fn($item) => (string)($item->content ?? ''), $conditions),
                    'tag_ids' => array_map(fn($item) => (int)$item->tag_id, $selectedTagItems),
                    'is_featured' => !empty($package->is_featured) ? '1' : '',
                    'is_popular' => !empty($package->is_popular) ? '1' : '',
                ];
            }
        }

        return $this->render('admin/packageTour/create', [
            'tags' => $tags,
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

        $result = $service->create(
            $_POST,
            $admin?->id ? (int)$admin->id : null,
            $_FILES
        );

        if ($result['success']) {
            \redirect('/admin/packageTour');
        }

        $tags = TourPackageTag::activeList();

        return $this->render('admin/packageTour/create', [
            'tags' => $tags,
            'itinerary' => [],
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
            'old' => $result['old'] ?? $_POST,
        ], 'adminUserLayout');
    }

    public function edit()
    {
        $id = (int)($_GET['id'] ?? 0);
        $package = TourPackage::find($id);

if (!$package) {
    \redirect('/admin/packageTour');
}

$itinerary = TourPackageItinerary::byPackage((int)$package->id);

        $tags = TourPackageTag::activeList();
        $selectedTagItems = TourPackageTagItem::byPackage((int)$package->id);
        $selectedTagIds = array_map(fn($item) => (int)$item->tag_id, $selectedTagItems);

        $cover = TourPackageImage::coverByPackage((int)$package->id);
        $gallery = array_values(array_filter(
            TourPackageImage::byPackage((int)$package->id),
            fn($img) => (int)($img->is_cover ?? 0) !== 1
        ));

        $includes = TourPackageInclusion::byPackageAndType((int)$package->id, 'include');
        $excludes = TourPackageInclusion::byPackageAndType((int)$package->id, 'exclude');
        $conditions = TourPackageCondition::byPackage((int)$package->id);
        $highlights = TourPackageHighlight::byPackage((int)$package->id);

        $old = [
    'title' => (string)($package->title ?? ''),
    'slug' => (string)($package->slug ?? ''),
    'subtitle' => (string)($package->subtitle ?? ''),
    'location_name' => (string)($package->location_name ?? ''),
    'price_from' => (string)($package->price_from ?? ''),
    'currency' => (string)($package->currency ?? 'COP'),
    'duration_days' => (string)($package->duration_days ?? ''),
    'duration_nights' => (string)($package->duration_nights ?? ''),
    'status' => (string)($package->status ?? 'draft'),
    'sort_order' => (string)($package->sort_order ?? 0),
    'short_description' => (string)($package->short_description ?? ''),
    'general_description' => (string)($package->general_description ?? ''),
    'includes' => array_map(fn($item) => (string)($item->content ?? ''), $includes),
    'excludes' => array_map(fn($item) => (string)($item->content ?? ''), $excludes),
    'highlights' => array_map(fn($item) => (string)($item->title ?? ''), $highlights),
    'condition_titles' => array_map(fn($item) => (string)($item->title ?? ''), $conditions),
    'condition_contents' => array_map(fn($item) => (string)($item->content ?? ''), $conditions),
    'itinerary_day_number' => array_map(fn($item) => (string)($item->day_number ?? ''), $itinerary),
    'itinerary_title' => array_map(fn($item) => (string)($item->title ?? ''), $itinerary),
    'itinerary_content' => array_map(fn($item) => (string)($item->content ?? ''), $itinerary),
    'tag_ids' => $selectedTagIds,
    'is_featured' => (string)((int)($package->is_featured ?? 0)),
'is_popular' => (string)((int)($package->is_popular ?? 0)),
];

        return $this->render('admin/packageTour/edit', [
    'package' => $package,
    'tags' => $tags,
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

        $id = (int)($_POST['id'] ?? 0);
        $service = new PackageTourService();
        $admin = AdminAuth::user();
        
        $result = $service->update(
            $id,
            $_POST,
            $admin?->id ? (int)$admin->id : null,
            $_FILES
        );

        if ($result['success']) {
            \redirect('/admin/packageTour');
        }

        $package = TourPackage::find($id);
        $tags = TourPackageTag::activeList();
        $itinerary = $package ? TourPackageItinerary::byPackage((int)$package->id) : [];
        $cover = $package ? TourPackageImage::coverByPackage((int)$package->id) : null;
        $gallery = $package
            ? array_values(array_filter(
                TourPackageImage::byPackage((int)$package->id),
                fn($img) => (int)($img->is_cover ?? 0) !== 1
            ))
            : [];

        $includes = $package ? TourPackageInclusion::byPackageAndType((int)$package->id, 'include') : [];
        $excludes = $package ? TourPackageInclusion::byPackageAndType((int)$package->id, 'exclude') : [];
        $conditions = $package ? TourPackageCondition::byPackage((int)$package->id) : [];
        $highlights = $package ? TourPackageHighlight::byPackage((int)$package->id) : [];

        return $this->render('admin/packageTour/edit', [
            'package' => $package,
            'itinerary' => $itinerary,
            'tags' => $tags,
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
}