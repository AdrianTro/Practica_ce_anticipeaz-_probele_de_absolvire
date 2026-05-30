<?php

namespace App\Http\Controllers;

use App\Mail\ContactThreadCustomerReply;
use App\Models\Admin;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\ContactThread;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promocode;
use App\Models\Subcategory;
use App\Services\SupabaseStorage;
use App\Support\SubcategoryFeatures;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    private const MAX_LOGIN_ATTEMPTS = 5;

    private const LOCK_SECONDS = 10;

    private const IMAGE_UPLOAD_MAX_KB = 20480;

    public function loginForm(Request $request): View|RedirectResponse
    {
        if ((bool) session('is_admin', false)) {
            return redirect()->route('admin.dashboard');
        }

        $lockUntil = (int) $request->session()->get('admin_lock_until', 0);
        if ($lockUntil > 0 && $lockUntil <= now()->timestamp) {
            $request->session()->forget(['admin_lock_until', 'admin_login_attempts']);
            $lockUntil = 0;
        }

        return view('admin.login', compact('lockUntil'));
    }

    public function login(Request $request): RedirectResponse
    {
        $lockUntil = (int) $request->session()->get('admin_lock_until', 0);
        if ($lockUntil > now()->timestamp) {
            return back()->withErrors([
                'password' => 'Ai introdus parola gresit de prea multe ori. Asteapta pana dispare fereastra.',
            ])->with('lock_until', $lockUntil)->onlyInput('name');
        }

        $credentials = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'password' => ['required', 'string', 'max:120'],
        ]);

        $admin = Admin::query()->where('name', $credentials['name'])->first();

        if (! $admin || ! Hash::check($credentials['password'], $admin->password)) {
            $attempts = ((int) $request->session()->get('admin_login_attempts', 0)) + 1;

            if ($attempts >= self::MAX_LOGIN_ATTEMPTS) {
                $lockUntil = now()->addSeconds(self::LOCK_SECONDS)->timestamp;
                $request->session()->put('admin_lock_until', $lockUntil);
                $request->session()->put('admin_login_attempts', 0);

                return back()->withErrors([
                    'password' => 'Parola a fost introdusa gresit de 5 ori. Acces blocat 10 secunde.',
                ])->with('lock_until', $lockUntil)->onlyInput('name');
            }

            $request->session()->put('admin_login_attempts', $attempts);

            return back()->withErrors([
                'name' => 'Date de autentificare incorecte. Incercari ramase: '.(self::MAX_LOGIN_ATTEMPTS - $attempts).'.',
            ])->onlyInput('name');
        }

        $request->session()->regenerate();
        $request->session()->forget(['admin_lock_until', 'admin_login_attempts']);
        $request->session()->put('is_admin', true);
        $request->session()->put('admin_id', $admin->id);
        $request->session()->put('admin_name', $admin->name);

        $intendedUrl = $request->session()->pull('admin_intended_url');
        if (is_string($intendedUrl) && Str::startsWith($intendedUrl, url('/catadmin'))) {
            return redirect()->to($intendedUrl)->with('success', 'Bine ai venit, '.$admin->name.'.');
        }

        return redirect()->route('admin.dashboard')->with('success', 'Bine ai venit, '.$admin->name.'.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['is_admin', 'admin_id', 'admin_name']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Ai iesit din panoul admin.');
    }

    public function dashboard(Request $request): View
    {
        $orderSearch = trim((string) $request->query('order', ''));
        $productSearch = trim((string) $request->query('product', ''));

        $orders = Order::query()
            ->withCount('items')
            ->when($orderSearch !== '', fn ($query) => $query->where('order_uuid', 'like', "%{$orderSearch}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $products = Product::query()
            ->with(['category', 'subcategory', 'images'])
            ->when($productSearch !== '', fn ($query) => $query->where('name', 'like', "%{$productSearch}%"))
            ->latest()
            ->paginate(12, ['*'], 'products_page')
            ->withQueryString()
            ->fragment('admin-products');

        $categories = Category::query()
            ->with(['subcategories' => fn ($query) => $query->withCount('products')->orderBy('name')])
            ->withCount(['products', 'subcategories'])
            ->orderBy('id')
            ->get();

        $promocodes = Promocode::query()->latest()->get();
        $newClaimsCount = $this->newClaimsCount();

        $stats = [
            'orders' => Order::query()->count(),
            'products' => Product::query()->count(),
            'subcategories' => Subcategory::query()->count(),
            'categories' => Category::query()->count(),
            'revenue' => Order::query()->sum('total'),
        ];

        $subcategoryFeatureRules = SubcategoryFeatures::categoryRules();
        $subcategoryFeatureLabels = SubcategoryFeatures::builtIns();
        $subcategoryWizardCatalog = $categories->map(fn (Category $category) => [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'allowedFeatures' => SubcategoryFeatures::allowedForCategory($category->slug),
            'customFeatures' => SubcategoryFeatures::customFeaturesForCategory($category),
        ])->values();

        return view('admin.dashboard', compact(
            'orders',
            'products',
            'stats',
            'orderSearch',
            'productSearch',
            'categories',
            'promocodes',
            'subcategoryFeatureRules',
            'subcategoryFeatureLabels',
            'subcategoryWizardCatalog',
            'newClaimsCount'
        ));
    }

    public function showOrder(Order $order): View
    {
        $order->load(['items.product.images']);

        return view('admin.orders.show', compact('order'));
    }

    public function contactThreads(): View
    {
        $threads = ContactThread::query()
            ->with('latestMessage')
            ->withCount('messages')
            ->withCount(['messages as new_messages_count' => fn ($query) => $this->applyUnreadCustomerMessageFilter($query)])
            ->orderByDesc('last_message_at')
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('admin.claims.index', compact('threads'));
    }

    public function showContactThread(ContactThread $contactThread): View
    {
        $contactThread->forceFill(['admin_seen_at' => now()])->save();
        $contactThread->load(['messages' => fn ($query) => $query->oldest()]);

        return view('admin.claims.show', compact('contactThread'));
    }

    public function replyContactThread(Request $request, ContactThread $contactThread): RedirectResponse
    {
        if ($contactThread->status === 'closed') {
            return back()->with('warning', 'Pretenția este încheiată. Nu mai poți trimite răspunsuri.');
        }

        $data = $request->validate([
            'message' => ['required', 'string'],
        ], [
            'message.required' => 'Scrie raspunsul inainte de trimitere.',
        ]);

        $message = $contactThread->messages()->create([
            'sender' => ContactMessage::SENDER_COMPANY,
            'body' => $data['message'],
        ]);

        $contactThread->update([
            'status' => 'answered',
            'last_message_at' => $message->created_at,
        ]);

        $emailWarning = null;

        try {
            Mail::to($contactThread->email)->send(new ContactThreadCustomerReply($contactThread->refresh(), $message));
        } catch (\Throwable $exception) {
            Log::error('Email raspuns contact nereusit', [
                'thread_uuid' => $contactThread->thread_uuid,
                'message_id' => $message->id,
                'error' => $exception->getMessage(),
            ]);
            $emailWarning = 'Raspunsul a fost salvat in panou, dar emailul catre client nu a putut fi trimis.';
        }

        $redirect = back()->with(
            'success',
            $emailWarning ? 'Raspunsul a fost salvat in panou.' : 'Raspunsul a fost salvat si trimis catre client.'
        );

        if ($emailWarning) {
            $redirect->with('warning', $emailWarning);
        }

        return $redirect;
    }

    public function closeContactThread(ContactThread $contactThread): RedirectResponse
    {
        if ($contactThread->status === 'closed') {
            return back()->with('warning', 'Pretenția este deja încheiată.');
        }

        $contactThread->update(['status' => 'closed']);

        return back()->with('success', 'Pretenția a fost încheiată.');
    }

    public function createProduct(): View
    {
        $categories = Category::query()->with('subcategories')->orderBy('id')->get();
        $product = new Product(['stock' => 100, 'volume' => '250ML', 'is_active' => true]);

        return view('admin.products.create', compact('categories', 'product'));
    }

    public function storeProduct(Request $request): RedirectResponse
    {
        $data = $this->validateProduct($request);
        $data = $this->normalizeProductData($data, $request);
        $data['slug'] = Product::makeUniqueSlug($data['name']);

        $product = Product::query()->create($data);
        $this->storeImages($request, $product);

        return redirect()->to(route('admin.dashboard').'#admin-products')->with('success', 'Produsul a fost adaugat.');
    }

    public function editProduct(Product $product): View
    {
        $product->load(['images', 'subcategory']);
        $categories = Category::query()->with('subcategories')->orderBy('id')->get();

        return view('admin.products.edit', compact('categories', 'product'));
    }

    public function updateProduct(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validateProduct($request, $product);
        $data = $this->normalizeProductData($data, $request);
        $data['slug'] = Product::makeUniqueSlug($data['name'], $product->id);

        $product->update($data);

        if ($request->boolean('replace_images')) {
            foreach ($product->images as $image) {
                $this->deleteStoredImage($image->path);
                $image->delete();
            }
        }

        $this->storeImages($request, $product->refresh());

        return redirect()->to(route('admin.dashboard').'#admin-products')->with('success', 'Produsul a fost actualizat.');
    }

    public function destroyProduct(Product $product): RedirectResponse
    {
        $product->load('images');

        foreach ($product->images as $image) {
            $this->deleteStoredImage($image->path);
        }

        $product->delete();

        return back()->with('success', 'Produsul a fost sters.');
    }


    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $this->validateCategory($request);
        $images = $this->storeCategoryCarouselImages($request, $data);
        $mainImage = $images['ro'] ?: ($images['ru'] ?: ($images['en'] ?: null));

        Category::query()->create([
            'name' => trim($data['name']),
            'slug' => Category::makeUniqueSlug($data['name']),
            'icon' => trim((string) ($data['icon'] ?? '')) ?: '📦',
            'description' => trim($data['description']),
            'is_active' => true,
            'show_in_carousel' => $this->categoryHasCarouselContent($data, $images),
            'carousel_image' => $mainImage,
            'carousel_image_ro' => $images['ro'],
            'carousel_image_ru' => $images['ru'],
            'carousel_image_en' => $images['en'],
            'carousel_title' => $this->nullableTrim($data['carousel_title'] ?? null),
            'carousel_label' => $this->nullableTrim($data['carousel_label'] ?? null),
            'carousel_text' => $this->nullableTrim($data['carousel_text'] ?? null),
            'carousel_text_position' => $this->normalizeCarouselPosition($data['carousel_text_position'] ?? null),
        ]);

        return back()->with('success', 'Categoria a fost creata. Apare in admin, home, catalog si cautare.');
    }

    public function updateCategory(Request $request, Category $category): RedirectResponse
    {
        $data = $this->validateCategory($request);
        $oldImages = [
            'ro' => $category->carousel_image_ro ?: $category->carousel_image,
            'ru' => $category->carousel_image_ru,
            'en' => $category->carousel_image_en,
        ];
        $images = $oldImages;

        if ($request->boolean('clear_carousel_image')) {
            $this->deleteCategoryCarouselImages($category);
            $images = ['ro' => null, 'ru' => null, 'en' => null];
        } else {
            foreach (Category::CAROUSEL_LANGUAGES as $language) {
                $uploadField = 'carousel_image_upload_'.$language;
                $urlField = 'carousel_image_url_'.$language;

                if (! $request->hasFile($uploadField) && empty($data[$urlField])) {
                    continue;
                }

                $newImage = $this->storeCategoryCarouselImageForLanguage($request, $data, $language);
                if ($newImage && $oldImages[$language] && $oldImages[$language] !== $newImage) {
                    $this->deleteStoredImage($oldImages[$language]);
                }
                $images[$language] = $newImage;
            }
        }

        $mainImage = $images['ro'] ?: ($images['ru'] ?: ($images['en'] ?: null));

        $category->update([
            'name' => trim($data['name']),
            'slug' => Category::makeUniqueSlug($data['name'], $category->id),
            'icon' => trim((string) ($data['icon'] ?? '')) ?: '📦',
            'description' => trim($data['description']),
            'show_in_carousel' => $this->categoryHasCarouselContent($data, $images),
            'carousel_image' => $mainImage,
            'carousel_image_ro' => $images['ro'],
            'carousel_image_ru' => $images['ru'],
            'carousel_image_en' => $images['en'],
            'carousel_title' => $this->nullableTrim($data['carousel_title'] ?? null),
            'carousel_label' => $this->nullableTrim($data['carousel_label'] ?? null),
            'carousel_text' => $this->nullableTrim($data['carousel_text'] ?? null),
            'carousel_text_position' => $this->normalizeCarouselPosition($data['carousel_text_position'] ?? null),
        ]);

        return back()->with('success', 'Categoria a fost actualizata.');
    }

    public function toggleCategory(Category $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        return back()->with('success', 'Starea categoriei a fost schimbata.');
    }

    public function destroyCategory(Category $category): RedirectResponse
    {
        $category->load(['products.images', 'subcategories']);

        $this->deleteCategoryCarouselImages($category);

        foreach ($category->products as $product) {
            foreach ($product->images as $image) {
                $this->deleteStoredImage($image->path);
            }
        }

        foreach ($category->subcategories as $subcategory) {
            if ($subcategory->image) {
                $this->deleteStoredImage($subcategory->image);
            }
        }

        $category->delete();

        return back()->with('success', 'Categoria a fost stearsa.');
    }

    public function storeSubcategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'name' => ['required', 'string', 'max:120'],
            'icon' => ['nullable', 'string', 'max:80'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'image_upload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.self::IMAGE_UPLOAD_MAX_KB],
            'description' => ['nullable', 'string'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:120'],
            'custom_features' => ['nullable', 'array'],
            'custom_features.*' => ['nullable', 'string', 'max:80'],
        ]);

        $category = Category::query()
            ->with('subcategories')
            ->findOrFail($data['category_id']);
        $features = $this->normalizeSubcategoryFeatures($category, $data);
        $image = $this->storeSubcategoryImage($request, $data);

        Subcategory::query()->create([
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'slug' => Subcategory::makeUniqueSlug($data['name'], (int) $data['category_id']),
            'icon' => $data['icon'] ?? null,
            'image' => $image,
            'description' => $data['description'] ?? null,
            'features' => $features,
            'is_active' => true,
        ]);

        return back()->with('success', 'Subcategoria a fost creata.');
    }


    public function updateSubcategory(Request $request, Subcategory $subcategory): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'name' => ['required', 'string', 'max:120'],
            'icon' => ['nullable', 'string', 'max:80'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'image_upload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.self::IMAGE_UPLOAD_MAX_KB],
            'description' => ['nullable', 'string'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:120'],
            'custom_features' => ['nullable', 'array'],
            'custom_features.*' => ['nullable', 'string', 'max:80'],
        ]);

        $category = Category::query()
            ->with('subcategories')
            ->findOrFail($data['category_id']);
        $features = $this->normalizeSubcategoryFeatures($category, $data);
        $image = $request->hasFile('image_upload') || ! empty($data['image_url'])
            ? $this->storeSubcategoryImage($request, $data)
            : $subcategory->image;

        if ($image && $subcategory->image && $subcategory->image !== $image) {
            $this->deleteStoredImage($subcategory->image);
        }

        $subcategory->update([
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'slug' => Subcategory::makeUniqueSlug($data['name'], (int) $data['category_id'], $subcategory->id),
            'icon' => $data['icon'] ?? null,
            'image' => $image,
            'description' => $data['description'] ?? null,
            'features' => $features,
        ]);

        return back()->with('success', 'Subcategoria a fost actualizata.');
    }

    public function toggleSubcategory(Subcategory $subcategory): RedirectResponse
    {
        $subcategory->update(['is_active' => ! $subcategory->is_active]);

        return back()->with('success', 'Starea subcategoriei a fost schimbata.');
    }

    public function destroySubcategory(Subcategory $subcategory): RedirectResponse
    {
        if ($subcategory->image) {
            $this->deleteStoredImage($subcategory->image);
        }

        $subcategory->delete();

        return back()->with('success', 'Subcategoria a fost stearsa.');
    }

    public function storePromocode(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:60'],
            'discount_percent' => ['required', 'numeric', 'min:0.01', 'max:100'],
        ]);

        $code = Promocode::normalizeCode($data['code']);

        Promocode::query()->updateOrCreate(
            ['code' => $code],
            ['discount_percent' => $data['discount_percent'], 'is_active' => true]
        );

        return back()->with('success', 'Promocodul a fost salvat.');
    }

    public function togglePromocode(Promocode $promocode): RedirectResponse
    {
        $promocode->update(['is_active' => ! $promocode->is_active]);

        return back()->with('success', 'Starea promocodului a fost schimbata.');
    }

    private function newClaimsCount(): int
    {
        return ContactThread::query()
            ->whereHas('messages', fn ($query) => $this->applyUnreadCustomerMessageFilter($query))
            ->count();
    }

    private function applyUnreadCustomerMessageFilter($query): void
    {
        $query->where('sender', ContactMessage::SENDER_CUSTOMER)
            ->where(function ($query): void {
                $query->whereNull('contact_threads.admin_seen_at')
                    ->orWhereColumn('contact_messages.created_at', '>', 'contact_threads.admin_seen_at');
            });
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'subcategory_id' => ['nullable', Rule::exists('subcategories', 'id')],
            'name' => ['required', 'string', 'max:160'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999'],
            'stock' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'size' => ['nullable', 'string', 'max:120'],
            'color' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'type' => ['nullable', 'string', 'max:120'],
            'dimensions' => ['nullable', 'string', 'max:120'],
            'volume' => ['nullable', 'string', 'max:80'],
            'images' => ['nullable', 'array', 'max:4'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:'.self::IMAGE_UPLOAD_MAX_KB],
            'replace_images' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'custom_features' => ['nullable', 'array'],
            'custom_features.*' => ['nullable', 'string', 'max:160'],
        ]);
    }

    private function normalizeProductData(array $data, Request $request): array
    {
        $data['subcategory_id'] = $data['subcategory_id'] ?? null;

        if ($data['subcategory_id']) {
            $subcategory = Subcategory::query()->find($data['subcategory_id']);
            if (! $subcategory || (int) $subcategory->category_id !== (int) $data['category_id']) {
                $data['subcategory_id'] = null;
            }
        }

        $data['stock'] = $data['stock'] ?? 100;
        $data['is_active'] = $request->boolean('is_active');
        $data['attributes'] = $this->makeAttributes($data);
        unset($data['custom_features']);

        return $data;
    }

    private function makeAttributes(array $data): array
    {
        $attributes = [];

        if (! empty($data['type'])) {
            $attributes['type'] = $data['type'];
        }

        $typeLower = mb_strtolower((string) ($data['type'] ?? ''));
        if (str_contains($typeLower, 'termo')) {
            $attributes['model'] = 'model/cana/cana_termo.glb';
        } elseif (str_contains($typeLower, 'simpl')) {
            $attributes['model'] = 'model/cana/cana.glb';
        }

        foreach (['dimensions', 'volume', 'size', 'color'] as $key) {
            if (! empty($data[$key])) {
                $attributes[$key] = $data[$key];
            }
        }

        $customFeatures = collect($data['custom_features'] ?? [])
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->all();

        if ($customFeatures) {
            $attributes['custom_features'] = $customFeatures;
        }

        return $attributes;
    }


    private function validateCategory(Request $request): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'icon' => ['nullable', 'string', 'max:80'],
            'description' => ['required', 'string'],
            'carousel_title' => ['nullable', 'string', 'max:160'],
            'carousel_label' => ['nullable', 'string', 'max:80'],
            'carousel_text' => ['nullable', 'string'],
            'carousel_text_position' => ['nullable', Rule::in(Category::CAROUSEL_POSITIONS)],
            'clear_carousel_image' => ['nullable', 'boolean'],
        ];

        foreach (Category::CAROUSEL_LANGUAGES as $language) {
            $rules['carousel_image_url_'.$language] = ['nullable', 'url', 'max:2048'];
            $rules['carousel_image_upload_'.$language] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.self::IMAGE_UPLOAD_MAX_KB];
        }

        return $request->validate($rules);
    }

    private function nullableTrim(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function normalizeCarouselPosition(?string $position): string
    {
        return in_array($position, Category::CAROUSEL_POSITIONS, true) ? $position : 'bottom-left';
    }

    private function categoryHasCarouselContent(array $data, array $images): bool
    {
        return collect($images)->filter(fn ($image) => filled($image))->isNotEmpty()
            || filled($data['carousel_title'] ?? null)
            || filled($data['carousel_label'] ?? null)
            || filled($data['carousel_text'] ?? null);
    }

    private function storeCategoryCarouselImages(Request $request, array $data): array
    {
        $images = [];

        foreach (Category::CAROUSEL_LANGUAGES as $language) {
            $images[$language] = $this->storeCategoryCarouselImageForLanguage($request, $data, $language);
        }

        return $images;
    }

    private function storeCategoryCarouselImageForLanguage(Request $request, array $data, string $language): ?string
    {
        $uploadField = 'carousel_image_upload_'.$language;
        $urlField = 'carousel_image_url_'.$language;

        if ($request->hasFile($uploadField)) {
            return app(SupabaseStorage::class)->upload($request->file($uploadField), 'categories/carousel/'.$language);
        }

        return $this->nullableTrim($data[$urlField] ?? null);
    }

    private function deleteCategoryCarouselImages(Category $category): void
    {
        collect([
            $category->carousel_image,
            $category->carousel_image_ro,
            $category->carousel_image_ru,
            $category->carousel_image_en,
        ])->filter()->unique()->each(fn (string $image) => $this->deleteStoredImage($image));
    }

    private function normalizeSubcategoryFeatures(Category $category, array $data): array
    {
        $selected = collect($data['features'] ?? [])
            ->map(fn ($feature) => trim((string) $feature))
            ->filter()
            ->unique()
            ->values();

        $allowedBuiltIns = SubcategoryFeatures::allowedForCategory($category->slug);
        $existingCustom = SubcategoryFeatures::customFeaturesForCategory($category);

        $features = $selected
            ->intersect($allowedBuiltIns)
            ->mapWithKeys(fn (string $feature) => [$feature => true])
            ->all();

        foreach ($selected as $feature) {
            if (isset($existingCustom[$feature])) {
                $features[$feature] = $existingCustom[$feature];
            }
        }

        $takenKeys = array_merge($existingCustom, $features);
        foreach (($data['custom_features'] ?? []) as $label) {
            $label = trim((string) $label);
            if ($label === '') {
                continue;
            }

            $existingKey = collect($existingCustom)
                ->filter(fn (array $definition) => mb_strtolower((string) $definition['label']) === mb_strtolower($label))
                ->keys()
                ->first();

            if ($existingKey) {
                $features[$existingKey] = $existingCustom[$existingKey];

                continue;
            }

            $key = SubcategoryFeatures::makeCustomKey($label, $takenKeys);
            $features[$key] = SubcategoryFeatures::normalizeCustomDefinition($label);
            $takenKeys[$key] = $features[$key];
        }

        return $features;
    }

    private function storeSubcategoryImage(Request $request, array $data): ?string
    {
        if ($request->hasFile('image_upload')) {
            return app(SupabaseStorage::class)->upload($request->file('image_upload'), 'subcategories');
        }

        return $data['image_url'] ?? null;
    }

    private function storeImages(Request $request, Product $product): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $currentCount = $product->images()->count();
        $availableSlots = max(0, 4 - $currentCount);

        foreach (array_slice($request->file('images'), 0, $availableSlots) as $index => $file) {
            $path = app(SupabaseStorage::class)->upload($file, 'products');

            $product->images()->create([
                'path' => $path,
                'sort_order' => $currentCount + $index,
            ]);
        }
    }

    private function deleteStoredImage(string $path): void
    {
        if (Str::startsWith($path, 'storage/')) {
            Storage::disk('public')->delete(Str::after($path, 'storage/'));

            return;
        }

        app(SupabaseStorage::class)->deleteByUrl($path);
    }
}
