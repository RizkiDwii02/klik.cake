<?php

namespace App\Http\Controllers\Shop;

use App\Enums\AvailabilityType;
use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request, ?string $categorySlug = null): View
    {
        $selectedCategorySlug = $categorySlug ?? $request->query('kategori');
        $selectedPriceRange = $request->query('harga');
        $selectedOccasion = $request->query('acara');
        $selectedDate = $request->query('tanggal');
        $sort = $request->query('sort', 'terbaru');

        // Query builder
        $query = Product::with(['variants', 'collection.parent']);

        $currentCollection = null;
        $breadcrumb = [
            ['title' => 'Beranda', 'url' => route('home')],
        ];

        // 1. Filter by Category / Subcategory
        if ($selectedCategorySlug) {
            $currentCollection = Collection::with('parent', 'children')->where('slug', $selectedCategorySlug)->first();

            if ($currentCollection) {
                $categoryIds = $currentCollection->getSelfAndDescendantIds();
                $query->whereIn('collection_id', $categoryIds);

                // Build Breadcrumb
                if ($currentCollection->parent) {
                    $breadcrumb[] = [
                        'title' => $currentCollection->parent->name,
                        'url' => route('catalog.category', $currentCollection->parent->slug),
                    ];
                }
                $breadcrumb[] = [
                    'title' => $currentCollection->name,
                    'url' => route('catalog.category', $currentCollection->slug),
                ];
            }
        } else {
            $breadcrumb[] = [
                'title' => 'Katalog Menu',
                'url' => route('catalog.index'),
            ];
        }

        // 2. Filter by Price Range
        if ($selectedPriceRange) {
            $query->whereHas('variants', function ($q) use ($selectedPriceRange) {
                if ($selectedPriceRange === 'under_50k') {
                    $q->where('price', '<', 50000);
                } elseif ($selectedPriceRange === '50k_150k') {
                    $q->whereBetween('price', [50000, 150000]);
                } elseif ($selectedPriceRange === 'over_150k') {
                    $q->where('price', '>', 150000);
                }
            });
        }

        // 3. Filter by Occasion (Acara)
        if ($selectedOccasion) {
            if ($selectedOccasion === 'ulang-tahun') {
                $query->where(function ($q) {
                    $q->where('name', 'LIKE', '%cake%')
                        ->orWhere('name', 'LIKE', '%birthday%')
                        ->orWhere('description', 'LIKE', '%ulang tahun%')
                        ->orWhere('portion_info', 'LIKE', '%orang%');
                });
            } elseif ($selectedOccasion === 'kantor-acara') {
                $query->where(function ($q) {
                    $q->where('name', 'LIKE', '%snackbox%')
                        ->orWhere('description', 'LIKE', '%acara%')
                        ->orWhere('description', 'LIKE', '%kantor%');
                });
            } elseif ($selectedOccasion === 'sarapan-harian') {
                $query->where(function ($q) {
                    $q->where('name', 'LIKE', '%roti%')
                        ->orWhere('name', 'LIKE', '%croissant%');
                });
            }
        }

        // 4. Filter by Date Availability
        if ($selectedDate) {
            try {
                $targetDate = Carbon::parse($selectedDate)->startOfDay();
                $diffDays = Carbon::now('Asia/Makassar')->startOfDay()->diffInDays($targetDate, false);

                if ($diffDays <= 0) {
                    // Only ready_today items allowed for same day
                    $query->where('availability_type', AvailabilityType::READY_TODAY->value);
                } else {
                    // Must have lead_time_days <= diffDays
                    $query->where('lead_time_days', '<=', $diffDays);
                }
            } catch (\Exception $e) {
                // Ignore invalid date
            }
        }

        // 5. Sorting
        switch ($sort) {
            case 'harga_asc':
                $query->withMin('variants', 'price')->orderBy('variants_min_price', 'asc');
                break;
            case 'harga_desc':
                $query->withMin('variants', 'price')->orderBy('variants_min_price', 'desc');
                break;
            case 'terpopuler':
                $query->orderByDesc('is_featured')->orderByDesc('rating');
                break;
            case 'terbaru':
            default:
                $query->latest('id');
                break;
        }

        // Pagination 6 items per page (clean 3x2 on desktop, 2x3 on mobile)
        $products = $query->paginate(6)->withQueryString();

        // Category tree for sidebar
        $allCategories = Collection::whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        return view('shop.catalog', compact(
            'products',
            'allCategories',
            'currentCollection',
            'breadcrumb',
            'selectedCategorySlug',
            'selectedPriceRange',
            'selectedOccasion',
            'selectedDate',
            'sort'
        ));
    }
}
