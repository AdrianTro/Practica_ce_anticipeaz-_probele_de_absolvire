<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promocode;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Admin::query()->updateOrCreate(
            ['name' => 'Lord'],
            ['password' => Hash::make('chac')]
        );

        $categories = collect([
            [
                'name' => 'Cancelarie',
                'slug' => 'cancelarie',
                'icon' => '✏️',
                'description' => 'Pixuri, radiere, creioane, foi, caiete si accesorii utile pentru birou sau scoala.',
                'is_active' => true,
            ],
            [
                'name' => 'Haine',
                'slug' => 'haine',
                'icon' => '👕',
                'description' => 'Tricouri, hudi si alte haine personalizabile in culori moderne si marimi de la XS la XXL.',
                'is_active' => true,
            ],
            [
                'name' => 'Banere',
                'slug' => 'banere',
                'icon' => '🖼️',
                'description' => 'Banere si roll-up-uri pentru reclame, evenimente si prezentari.',
                'is_active' => true,
            ],
            [
                'name' => 'Cani',
                'slug' => 'cani',
                'icon' => '☕',
                'description' => 'Cani simple si termo, pregatite pentru personalizare si previzualizare 3D.',
                'is_active' => true,
            ],
        ])->mapWithKeys(function (array $data) {
            $category = Category::query()->updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );

            return [$data['slug'] => $category];
        });

        $subcategoryData = [
            'cancelarie' => [
                ['name' => 'Pixuri', 'slug' => 'pixuri', 'icon' => '🖊️', 'features' => ['type' => true, 'color' => true, 'custom_design' => true]],
                ['name' => 'Caiete', 'slug' => 'caiete', 'icon' => '📘', 'features' => ['size' => true, 'color' => true, 'custom_design' => true]],
            ],
            'haine' => [
                ['name' => 'Tricouri', 'slug' => 'tricouri', 'icon' => '👕', 'features' => ['size' => true, 'color' => true, 'front_back_customizer' => true, 'custom_design' => true]],
                ['name' => 'Hudi', 'slug' => 'hudi', 'icon' => '🧥', 'features' => ['size' => true, 'color' => true, 'front_back_customizer' => true, 'custom_design' => true]],
            ],
            'banere' => [
                ['name' => 'Banere exterior', 'slug' => 'banere-exterior', 'icon' => '🏙️', 'features' => ['dimensions' => true, 'custom_design' => true]],
                ['name' => 'Roll-up', 'slug' => 'roll-up', 'icon' => '🧾', 'features' => ['dimensions' => true, 'custom_design' => true]],
            ],
            'cani' => [
                ['name' => 'Cani simple', 'slug' => 'cani-simple', 'icon' => '☕', 'features' => ['type' => true, 'volume' => true, 'color' => true, 'mug_customizer' => true, 'custom_design' => true]],
                ['name' => 'Cani termo', 'slug' => 'cani-termo', 'icon' => '♨️', 'features' => ['type' => true, 'volume' => true, 'color' => true, 'mug_customizer' => true, 'custom_design' => true]],
            ],
        ];

        $subcategories = collect();
        foreach ($subcategoryData as $categorySlug => $items) {
            foreach ($items as $item) {
                $category = $categories[$categorySlug];
                $subcategories[$categorySlug.'.'.$item['slug']] = Subcategory::query()->updateOrCreate(
                    ['category_id' => $category->id, 'slug' => $item['slug']],
                    [
                        'name' => $item['name'],
                        'icon' => $item['icon'],
                        'description' => $item['description'] ?? null,
                        'features' => $item['features'],
                        'is_active' => true,
                    ]
                );
            }
        }

        Promocode::query()->updateOrCreate(
            ['code' => 'CUDESCHIDERE'],
            ['discount_percent' => 10, 'is_active' => true]
        );

        $products = [
            [
                'category' => 'cancelarie',
                'subcategory' => 'pixuri',
                'name' => 'Pix albastru ReclamDesign',
                'slug' => 'pix-albastru-reclamdesign',
                'price' => 8.50,
                'stock' => 100,
                'type' => 'pix',
                'color' => 'albastru',
                'description' => 'Pix ergonomic pentru birou, ideal pentru personalizare cu logo sau text scurt.',
                'image' => 'images/carousel/RO/RO_Rechi.png',
            ],
            [
                'category' => 'cancelarie',
                'subcategory' => 'caiete',
                'name' => 'Caiet A5 personalizat',
                'slug' => 'caiet-a5-personalizat',
                'price' => 32.00,
                'stock' => 100,
                'size' => 'A5',
                'color' => 'albastru / alb',
                'description' => 'Caiet pentru notite cu coperta personalizabila si design curat.',
                'image' => 'images/carousel/RO/RO_Rechi.png',
            ],
            [
                'category' => 'haine',
                'subcategory' => 'tricouri',
                'name' => 'Tricou alb personalizat',
                'slug' => 'tricou-alb-personalizat',
                'price' => 149.00,
                'stock' => 100,
                'size' => 'XS, S, M, L, XL, XXL',
                'color' => 'alb, negru, albastru',
                'description' => 'Tricou cu zona de personalizare. Poti incarca pana la 4 imagini pe fata sau pe spate si se salveaza pozitiile alese.',
                'image' => 'images/carousel/RO/RO_Tricou.png',
            ],
            [
                'category' => 'haine',
                'subcategory' => 'tricouri',
                'name' => 'Tricou negru astronaut',
                'slug' => 'tricou-negru-astronaut',
                'price' => 159.00,
                'stock' => 100,
                'size' => 'XS, S, M, L, XL, XXL',
                'color' => 'negru, alb',
                'description' => 'Tricou negru cu print creativ, potrivit pentru cadouri si evenimente tematice.',
                'image' => 'images/carousel/RO/RO_Tricou.png',
            ],
            [
                'category' => 'haine',
                'subcategory' => 'hudi',
                'name' => 'Hudi personalizat premium',
                'slug' => 'hudi-personalizat-premium',
                'price' => 299.00,
                'stock' => 50,
                'size' => 'S, M, L, XL, XXL',
                'color' => 'negru, gri, alb',
                'description' => 'Hudi personalizabil pe fata si spate, cu aceeasi logica de design ca la tricouri.',
                'image' => 'images/carousel/RO/RO_Tricou.png',
            ],
            [
                'category' => 'banere',
                'subcategory' => 'banere-exterior',
                'name' => 'Banner exterior premium',
                'slug' => 'banner-exterior-premium',
                'price' => 250.00,
                'stock' => 100,
                'dimensions' => '2 x 1 m',
                'description' => 'Banner rezistent pentru exterior, cu finisaj modern si culori intense.',
                'image' => 'images/carousel/RO/RO_Banere.png',
            ],
            [
                'category' => 'banere',
                'subcategory' => 'roll-up',
                'name' => 'Roll-up prezentare',
                'slug' => 'roll-up-prezentare',
                'price' => 320.00,
                'stock' => 100,
                'dimensions' => '85 x 200 cm',
                'description' => 'Roll-up pentru expozitii, conferinte si prezentari de companie.',
                'image' => 'images/carousel/RO/RO_Banere.png',
            ],
            [
                'category' => 'cani',
                'subcategory' => 'cani-simple',
                'name' => 'Cana simpla 250ML',
                'slug' => 'cana-simpla-250ml',
                'price' => 89.00,
                'stock' => 100,
                'type' => 'simpla',
                'volume' => '250ML',
                'description' => 'Cana simpla pentru personalizare. Poti incarca imaginea ta pentru textura pe modelul 3D.',
                'image' => 'images/carousel/RO/RO_Cana.png',
                'attributes' => ['model' => 'model/cana/cana.glb'],
            ],
            [
                'category' => 'cani',
                'subcategory' => 'cani-termo',
                'name' => 'Cana termo magica 250ML',
                'slug' => 'cana-termo-magica-250ml',
                'price' => 129.00,
                'stock' => 100,
                'type' => 'termo',
                'volume' => '250ML',
                'description' => 'Cana termo pe care imaginea apare la incalzire. Include previzualizare pe modelul 3D termo.',
                'image' => 'images/carousel/RO/RO_Cana.png',
                'attributes' => ['model' => 'model/cana/cana_termo.glb'],
            ],
        ];

        foreach ($products as $item) {
            $category = $categories[$item['category']];
            $subcategory = $subcategories[$item['category'].'.'.$item['subcategory']] ?? null;
            $image = $item['image'];
            unset($item['category'], $item['subcategory'], $item['image']);

            $product = Product::query()->updateOrCreate(
                ['slug' => $item['slug']],
                array_merge($item, [
                    'category_id' => $category->id,
                    'subcategory_id' => $subcategory?->id,
                    'is_active' => true,
                    'attributes' => $item['attributes'] ?? [],
                ])
            );

            if (! $product->images()->exists()) {
                $product->images()->create([
                    'path' => $image,
                    'sort_order' => 0,
                ]);
            }
        }
    }
}
