<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\SlotAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        protected SlotAvailabilityService $slotService
    ) {}

    public function show(string $slug): View
    {
        $product = Product::with(['variants', 'collection.parent'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Earliest date customer can choose based on product's lead time and 18:00 cutoff
        $now = Carbon::now('Asia/Makassar');
        $earliestDate = $this->slotService->getEarliestAvailableDate($product->lead_time_days, $now);
        $initialDateString = $earliestDate->toDateString();

        // Initial available slots for earliest date
        $initialSlots = $this->slotService->getAvailableSlots(
            $initialDateString,
            $product->lead_time_days,
            quantity: 1,
            now: $now
        );

        // Date options: 7 consecutive days starting from earliest date
        $dateOptions = [];
        for ($i = 0; $i < 7; $i++) {
            $d = $earliestDate->copy()->addDays($i);
            $dateOptions[] = [
                'value' => $d->toDateString(),
                'day_name' => $d->isoFormat('dddd'),
                'formatted' => $d->isoFormat('D MMM'),
                'is_today' => $d->isToday(),
                'is_tomorrow' => $d->isTomorrow(),
            ];
        }

        // Related products in same collection / parent
        $relatedProducts = Product::with('variants')
            ->where('id', '!=', $product->id)
            ->where('collection_id', $product->collection_id)
            ->take(4)
            ->get();

        if ($relatedProducts->count() < 4 && $product->collection && $product->collection->parent) {
            $parentCategoryIds = $product->collection->parent->getSelfAndDescendantIds();
            $moreRelated = Product::with('variants')
                ->where('id', '!=', $product->id)
                ->whereNotIn('id', $relatedProducts->pluck('id'))
                ->whereIn('collection_id', $parentCategoryIds)
                ->take(4 - $relatedProducts->count())
                ->get();
            $relatedProducts = $relatedProducts->concat($moreRelated);
        }

        // Breadcrumb
        $breadcrumb = [
            ['title' => 'Beranda', 'url' => route('home')],
        ];
        if ($product->collection && $product->collection->parent) {
            $breadcrumb[] = [
                'title' => $product->collection->parent->name,
                'url' => route('catalog.category', $product->collection->parent->slug),
            ];
        }
        if ($product->collection) {
            $breadcrumb[] = [
                'title' => $product->collection->name,
                'url' => route('catalog.category', $product->collection->slug),
            ];
        }
        $breadcrumb[] = [
            'title' => $product->name,
            'url' => route('product.show', $product->slug),
        ];

        return view('shop.product', compact(
            'product',
            'earliestDate',
            'initialDateString',
            'initialSlots',
            'dateOptions',
            'relatedProducts',
            'breadcrumb'
        ));
    }

    /**
     * JSON Endpoint: Fetch slots availability for selected date and lead time.
     */
    public function getSlots(Request $request): JsonResponse
    {
        $dateString = $request->query('tanggal');
        $leadTimeDays = (int) $request->query('lead_time', 1);
        $quantity = (int) $request->query('qty', 1);

        if (! $dateString) {
            return response()->json(['error' => 'Parameter tanggal wajib diisi.'], 400);
        }

        $slots = $this->slotService->getAvailableSlots(
            $dateString,
            $leadTimeDays,
            $quantity,
            Carbon::now('Asia/Makassar')
        );

        return response()->json([
            'date' => $dateString,
            'slots' => array_values($slots),
        ]);
    }
}
