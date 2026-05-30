<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->with(['activeSubcategories' => fn ($query) => $query->withCount('products')->orderBy('name')])
            ->withCount(['products' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('id')
            ->get();

        $products = Product::query()
            ->with(['category', 'subcategory', 'images'])
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->latest()
            ->take(12)
            ->get();

        $activeCategorySlugs = $categories->pluck('slug')->all();

        $carousel = collect([
            [
                'title' => 'Rechizite necesare',
                'label' => 'ReclamDesign Modern',
                'image' => 'images/carousel/RO/RO_Rechi.png',
                'images' => [
                    'ro' => 'images/carousel/RO/RO_Rechi.png',
                    'ru' => 'images/carousel/RU/RU_NEOB.png',
                    'en' => 'images/carousel/EN/EN_NECES.png',
                ],
                'category' => 'cancelarie',
                'text' => 'Pixuri, caiete, foi si accesorii pentru birou.',
                'text_position' => 'bottom-left',
            ],
            [
                'title' => 'Creeaza-ti tricoul',
                'label' => 'ReclamDesign Modern',
                'image' => 'images/carousel/RO/RO_Tricou.png',
                'images' => [
                    'ro' => 'images/carousel/RO/RO_Tricou.png',
                    'ru' => 'images/carousel/RU/RU_Futb.png',
                    'en' => 'images/carousel/EN/EN_TShir.png',
                ],
                'category' => 'haine',
                'text' => 'Tricouri personalizate cu designul tau.',
                'text_position' => 'bottom-left',
            ],
            [
                'title' => 'Stilizeaza cana',
                'label' => 'ReclamDesign Modern',
                'image' => 'images/carousel/RO/RO_Cana.png',
                'images' => [
                    'ro' => 'images/carousel/RO/RO_Cana.png',
                    'ru' => 'images/carousel/RU/RU_Stili.png',
                    'en' => 'images/carousel/EN/EN_MUG.png',
                ],
                'category' => 'cani',
                'text' => 'Cani simple sau termo cu model 3D.',
                'text_position' => 'bottom-left',
            ],
            [
                'title' => 'Comanda banere',
                'label' => 'ReclamDesign Modern',
                'image' => 'images/carousel/RO/RO_Banere.png',
                'images' => [
                    'ro' => 'images/carousel/RO/RO_Banere.png',
                    'ru' => 'images/carousel/RU/RU_Zacaz.png',
                    'en' => 'images/carousel/EN/EN_Order.png',
                ],
                'category' => 'banere',
                'text' => 'Banere si roll-up-uri pentru orice eveniment.',
                'text_position' => 'bottom-left',
            ],
        ])->filter(fn (array $slide) => in_array($slide['category'], $activeCategorySlugs, true))
            ->merge(
            $categories
                ->where('show_in_carousel', true)
                ->map(fn (Category $category) => [
                    'title' => $category->carouselTitle(),
                    'label' => $category->carouselLabel(),
                    'image' => $category->carouselImagePath('ro'),
                    'images' => $category->localizedCarouselImages(),
                    'category' => $category->slug,
                    'text' => $category->carouselText(),
                    'text_position' => $category->carouselPosition(),
                ])
                ->values()
        )->values()->all();

        return view('home', compact('categories', 'products', 'carousel'));
    }
}
