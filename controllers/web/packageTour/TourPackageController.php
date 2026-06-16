<?php

namespace app\Controllers\web\packageTour;

use app\Core\Controller;
use app\Models\TourPackage;
use app\Models\TourPackageCondition;
use app\Models\TourPackageHighlight;
use app\Models\TourPackageImage;
use app\Models\TourPackageInclusion;
use app\Models\TourPackageTag;
use app\Models\TourPackageTagItem;
use app\Models\TourPackageItinerary;
use app\Models\TravelExperience;
use app\Core\CustomerAuth;
use app\Core\Csrf;
use app\Core\Request;
use app\Core\Flash;
use app\Services\Package\CustomerPackageService;

class TourPackageController extends Controller
{
    protected CustomerPackageService $customerPackages;

    public function __construct()
    {
        $this->customerPackages = new CustomerPackageService();
    }

    public function index()
    {
        return $this->render('packagesTourist/list', ['title' => 'Paquetes Turisticos'], 'mainUserLayout');
    }


    protected function assetUrl(?string $path, string $fallback = '/img/packages/default.jpg'): string
{
    $path = trim((string)$path);

    if ($path === '') {
        return $fallback;
    }

    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    $path = str_replace('\\', '/', $path);

    return '/' . ltrim($path, '/');
}
   public function apiList()
{
    header('Content-Type: application/json; charset=utf-8');

    $cursor = max(0, (int)($_GET['cursor'] ?? 0));
    $limit = max(1, min(24, (int)($_GET['limit'] ?? 6)));
    $q = trim($_GET['q'] ?? '');

    $packages = $this->customerPackages->mostVisitedPublished($q);
    $customerId = $this->customerPackages->currentCustomerId();
    $favoriteIds = $this->customerPackages->favoritePackageIds($customerId);
    $total = count($packages);

    $slice = array_slice($packages, $cursor, $limit);
    $nextCursor = $cursor + count($slice);
    $hasMore = $nextCursor < $total;

    $items = array_map(function ($package) use ($favoriteIds) {
        $cover = TourPackageImage::coverByPackage((int)$package->id);
        $coverPath = $this->assetUrl($cover->image_path ?? null);

        $tagItems = TourPackageTagItem::byPackage((int)$package->id);
        $tagMap = [];
        foreach (TourPackageTag::activeList() as $tag) {
            $tagMap[(int)$tag->id] = $tag;
        }

        $tags = [];
        foreach ($tagItems as $item) {
            if (isset($tagMap[(int)$item->tag_id])) {
                $tags[] = $tagMap[(int)$item->tag_id]->name;
            }
        }

        return [
            'id' => (int)$package->id,
            'slug' => $package->slug,
            'title' => $package->title,
            'image' => $coverPath,
            'badge' => !empty($package->is_featured) ? 'Destacado' : '',
            'is_favorite' => in_array((int) $package->id, $favoriteIds, true),
            'tags' => $tags,
            'location' => $package->location_name,
            'price_from' => (float)($package->price_from ?? 0),
            'currency' => $package->currency ?? 'COP',
            'url' => '/packagesTourist/package?slug=' . urlencode($package->slug ?? ''),
        ];
    }, $slice);

    echo json_encode([
        'items' => $items,
        'nextCursor' => $nextCursor,
        'hasMore' => $hasMore,
        'total' => $total,
        'authenticated' => $customerId !== null,
        'csrf' => $customerId !== null ? Csrf::token() : null,
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

    public function show()
    {
        $slug = trim($_GET['slug'] ?? '');
        $package = TourPackage::findBySlug($slug);

        if (!$package || $package->status !== 'published') {
            http_response_code(404);
            return $this->render('_404_public', ['title' => 'Página no encontrada | Over Alestur'], 'mainUserLayout');
        }

        $customerId = $this->customerPackages->currentCustomerId();
        $this->customerPackages->recordView($package);

        $cover = TourPackageImage::coverByPackage((int)$package->id);
        $gallery = TourPackageImage::byPackage((int)$package->id);
        $includes = TourPackageInclusion::byPackageAndType((int)$package->id, 'include');
        $excludes = TourPackageInclusion::byPackageAndType((int)$package->id, 'exclude');
        $conditions = TourPackageCondition::byPackage((int)$package->id);
        $highlights = TourPackageHighlight::byPackage((int)$package->id);
$itinerary = TourPackageItinerary::byPackage((int)$package->id);
        $tagItems = TourPackageTagItem::byPackage((int)$package->id);
        $tagMap = [];
        foreach (TourPackageTag::activeList() as $tag) {
            $tagMap[(int)$tag->id] = $tag;
        }

        $tags = [];
        foreach ($tagItems as $item) {
            if (isset($tagMap[(int)$item->tag_id])) {
                $tags[] = $tagMap[(int)$item->tag_id];
            }
        }

        return $this->render('packagesTourist/package', [
            'package' => $package,
            'cover' => $cover,
            'gallery' => $gallery,
            'includes' => $includes,
            'excludes' => $excludes,
            'conditions' => $conditions,
            'highlights' => $highlights,
            'itinerary' => $itinerary,
            'experiences' => TravelExperience::approvedByPackageSlug((string)$package->slug, 3),
            'tags' => $tags,
            'customer' => $this->currentCustomer(),
            'isFavorite' => $this->customerPackages->isFavorite($customerId, (int) $package->id),
            'recommendations' => $customerId
                ? $this->customerPackages->recommendations($customerId, 3, [(int) $package->id])
                : [],
            'pageCss' => '/styles/home.css',
        ], 'mainUserLayout');
    }

    public function toggleFavorite(Request $request)
    {
        $account = CustomerAuth::user();
        if (!$account) {
            \redirect('/users/login');
        }

        $packageId = (int) $request->input('package_id', 0);
        $package = TourPackage::find($packageId);

        if (!$package || (string) $package->status !== 'published') {
            \redirect('/packagesTourist');
        }

        $this->customerPackages->toggleFavorite((int) $account->customer_id, $packageId);

        $returnTo = (string) $request->input('return_to', '/packagesTourist');
        if (!str_starts_with($returnTo, '/') || str_starts_with($returnTo, '//')) {
            $returnTo = '/packagesTourist';
        }

        \redirect($returnTo);
    }

    protected function currentCustomer(): ?\app\Models\Customer
    {
        $account = CustomerAuth::user();
        return $account ? \app\Models\Customer::find((int) $account->customer_id) : null;
    }
}
