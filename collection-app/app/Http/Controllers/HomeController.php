<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $totalItems = Item::count();
        $ownedItems = Item::where('qty', '>', 0)->count();
        $wishItems = max(0, $totalItems - $ownedItems);
        $totalValue = (float) Item::query()->selectRaw('COALESCE(SUM(price * qty), 0) as t')->value('t');
        $totalQty = (int) Item::query()->sum('qty');
        $avgPrice = $totalItems > 0 ? (float) Item::query()->avg('price') : 0.0;

        $withImages = Item::whereNotNull('image_path')->where('image_path', '!=', '')->count();

        $byType = Item::query()
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $topMakers = Item::query()
            ->selectRaw('maker, COUNT(*) as c')
            ->whereNotNull('maker')
            ->where('maker', '!=', '')
            ->groupBy('maker')
            ->orderByDesc('c')
            ->limit(5)
            ->get();

        $topBrands = Item::query()
            ->selectRaw('subject_brand as brand, COUNT(*) as c')
            ->whereNotNull('subject_brand')
            ->where('subject_brand', '!=', '')
            ->groupBy('subject_brand')
            ->orderByDesc('c')
            ->limit(5)
            ->get();

        $sparkline = [];
        for ($i = 6; $i >= 0; $i--) {
            $sparkline[] = (int) Item::whereDate('created_at', now()->subDays($i)->toDateString())->count();
        }

        $addedLast7 = Item::where('created_at', '>=', now()->subDays(7)->startOfDay())->count();
        $addedPrev7 = Item::where('created_at', '>=', now()->subDays(14)->startOfDay())
            ->where('created_at', '<', now()->subDays(7)->startOfDay())
            ->count();
        $weekTrendPercent = $addedPrev7 > 0
            ? (int) round((($addedLast7 - $addedPrev7) / $addedPrev7) * 100)
            : null;

        $latestItems = Item::latest()->take(10)->get();

        $typeMax = (int) ($byType->max('count') ?? 0);
        $makerMax = (int) ($topMakers->max('c') ?? 0);
        $brandMax = (int) ($topBrands->max('c') ?? 0);
        $sparkMax = max($sparkline) > 0 ? max($sparkline) : 1;

        return view('home', compact(
            'totalItems',
            'ownedItems',
            'wishItems',
            'totalValue',
            'totalQty',
            'avgPrice',
            'withImages',
            'byType',
            'topMakers',
            'topBrands',
            'sparkline',
            'addedLast7',
            'addedPrev7',
            'weekTrendPercent',
            'latestItems',
            'typeMax',
            'makerMax',
            'brandMax',
            'sparkMax',
        ));
    }
}
