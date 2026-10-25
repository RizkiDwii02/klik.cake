<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\View\View;

class StyleguideController extends Controller
{
    public function index(): View
    {
        abort_unless(app()->isLocal(), 404);

        $strawberryCake = Product::with('variants')->where('slug', 'strawberry-cream-cake')->first();
        $sampleProducts = Product::with('variants')->where('slug', '!=', 'strawberry-cream-cake')->take(3)->get();

        return view('shop.styleguide', [
            'strawberryCake' => $strawberryCake,
            'sampleProducts' => $sampleProducts,
        ]);
    }
}
