<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Dish;
use App\Models\MenuCategory;
use App\Models\Promotion;
use App\Models\VenueSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MenuController extends Controller
{
    /**
     * The guest menu — everything on one page, exactly like the SaaS's
     * /m/{slug}. The whole interactive layer (locale, theme, layout, cart) runs
     * client-side in Alpine over the payload we hand it.
     */
    public function home(Request $request): View
    {
        $venue = VenueSetting::current();

        // Two-level menu: top-level categories, each with its own dishes and/or a
        // set of subcategories. Only visible rows, and everything empty is pruned
        // so no bare heading reaches the guest.
        $groups = MenuCategory::query()
            ->whereNull('parent_id')
            ->where('is_visible', true)
            ->with([
                'dishes' => fn ($q) => $q->where('is_visible', true),
                'children' => fn ($q) => $q->where('is_visible', true)->orderBy('sort')->orderBy('id'),
                'children.dishes' => fn ($q) => $q->where('is_visible', true),
            ])
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->map(function (MenuCategory $group) {
                // Drop subcategories that have no visible dishes.
                $group->setRelation(
                    'children',
                    $group->children->filter(fn (MenuCategory $c) => $c->dishes->isNotEmpty())->values(),
                );

                return $group;
            })
            // A top category survives if it has direct dishes or a non-empty child.
            ->filter(fn (MenuCategory $g) => $g->dishes->isNotEmpty() || $g->children->isNotEmpty())
            ->values();

        $promotions = Promotion::query()
            ->where('is_visible', true)
            ->orderBy('sort')
            ->orderBy('id')
            ->get();

        $collections = Collection::query()
            ->where('is_visible', true)
            ->with(['dishes' => fn ($q) => $q->where('is_visible', true)])
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            // A rail with no visible dishes is just an empty heading.
            ->filter(fn (Collection $c) => $c->dishes->isNotEmpty())
            ->values();

        return view('menu.home', [
            'venue' => $venue,
            'groups' => $groups,
            'promotions' => $promotions,
            'collections' => $collections,
        ]);
    }

    /**
     * Per-dish SEO page — the Premium selling point. Server-rendered with its
     * own <title>, meta description, OG tags and JSON-LD so search engines index
     * every dish. Links here are crawlable from the homepage.
     */
    public function dish(Request $request, string $slug): View
    {
        $venue = VenueSetting::current();

        $dish = Dish::query()
            ->where('slug', $slug)
            ->where('is_visible', true)
            ->with('category')
            ->firstOrFail();

        // A few sibling dishes to keep the crawler moving and offer "see also".
        $related = Dish::query()
            ->where('menu_category_id', $dish->menu_category_id)
            ->where('is_visible', true)
            ->whereKeyNot($dish->getKey())
            ->orderBy('sort')
            ->limit(6)
            ->get();

        return view('menu.dish', [
            'venue' => $venue,
            'dish' => $dish,
            'related' => $related,
        ]);
    }
}
