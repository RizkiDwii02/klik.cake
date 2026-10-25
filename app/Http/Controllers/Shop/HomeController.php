<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        // 4 Root Categories (Roti, Cake, Pastry, Snackbox)
        $categories = Collection::whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        // Best Seller Products (Strawberry Cream Cake prioritized, plus other best sellers)
        $bestSellers = Product::with(['variants', 'collection'])
            ->where('is_featured', true)
            ->orderByDesc('rating')
            ->take(8)
            ->get();

        // Fallback if featured is less than 4
        if ($bestSellers->count() < 4) {
            $extra = Product::with(['variants', 'collection'])
                ->whereNotIn('id', $bestSellers->pluck('id'))
                ->take(4 - $bestSellers->count())
                ->get();
            $bestSellers = $bestSellers->concat($extra);
        }

        // Hero featured item (Strawberry Cream Cake)
        $heroProduct = Product::with('variants')->where('slug', 'strawberry-cream-cake')->first()
            ?? $bestSellers->first();

        // Fresh bake products
        $freshBakeProducts = Product::with(['variants', 'collection'])
            ->where('is_fresh_bake', true)
            ->take(6)
            ->get();

        return view('shop.home', compact('categories', 'bestSellers', 'heroProduct', 'freshBakeProducts'));
    }
}
