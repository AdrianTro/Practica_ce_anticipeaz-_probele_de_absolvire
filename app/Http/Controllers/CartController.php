<?php

namespace App\Http\Controllers;

use App\Mail\CompanyOrderNotification;
use App\Mail\CustomerOrderConfirmation;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promocode;
use App\Services\SupabaseStorage;
use App\Support\StoredImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    private const DESIGN_IMAGE_FEE = 15;
    private const MAX_DESIGN_ITEMS = 4;
    private const MAX_DATA_URI_LENGTH = 2500000;

    public function index(): View
    {
        return view('cart.index');
    }

    public function checkPromocode(Request $request): JsonResponse
    {
        $code = Promocode::normalizeCode($request->input('code'));

        if ($code === '') {
            return response()->json(['valid' => false, 'message' => 'Introduce codul promocodului.'], 422);
        }

        $promocode = Promocode::query()->where('code', $code)->first();

        if (! $promocode || ! $promocode->is_valid) {
            return response()->json(['valid' => false, 'message' => 'Promocod invalid sau dezactivat.'], 422);
        }

        return response()->json([
            'valid' => true,
            'code' => $promocode->code,
            'discount_percent' => (float) $promocode->discount_percent,
            'message' => 'Promocod aplicat: -'.number_format((float) $promocode->discount_percent, 0).'%.',
        ]);
    }

    public function checkout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:40'],
            'email' => ['required', 'email', 'max:160'],
            'promocode' => ['nullable', 'string', 'max:60'],
            'cart_payload' => ['required', 'json'],
        ], [
            'name.required' => 'Introduceți Nume.',
            'phone.required' => 'Introduceți Telefon.',
            'email.required' => 'Introduceți Email.',
            'email.email' => 'Introduceți un Email valid.',
        ]);

        $cart = collect(json_decode($validated['cart_payload'], true) ?: [])
            ->filter(fn ($item) => is_array($item) && isset($item['id'], $item['qty']))
            ->map(fn ($item) => [
                'id' => (int) $item['id'],
                'qty' => max(1, min(100, (int) $item['qty'])),
                'options' => $this->sanitizeOptions(is_array($item['options'] ?? null) ? $item['options'] : []),
            ])
            ->values();

        if ($cart->isEmpty()) {
            throw ValidationException::withMessages([
                'cart_payload' => 'Coșul este gol. Adaugă cel puțin un produs.',
            ]);
        }

        $products = Product::query()
            ->with(['category', 'subcategory', 'images'])
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->whereIn('id', $cart->pluck('id')->unique())
            ->get()
            ->keyBy('id');

        $orderLines = [];
        $totalBeforeDiscount = 0;

        foreach ($cart as $item) {
            /** @var Product|null $product */
            $product = $products->get($item['id']);

            if (! $product) {
                throw ValidationException::withMessages([
                    'cart_payload' => 'Un produs din coș nu mai există sau nu este activ.',
                ]);
            }

            if ($item['qty'] > $product->stock) {
                throw ValidationException::withMessages([
                    'cart_payload' => "Cantitatea solicitată pentru {$product->name} nu este disponibilă. Ajustează cantitatea sau contactează-ne.",
                ]);
            }

            $options = $item['options'];
            $customDesignFee = $this->customDesignFee($options);
            $options['custom_design_fee'] = $customDesignFee;
            $options['base_price'] = (float) $product->price;
            $options['product_image'] = $product->mainImagePath();

            $price = round((float) $product->price + $customDesignFee, 2);
            $subtotal = round($price * $item['qty'], 2);
            $totalBeforeDiscount += $subtotal;

            $orderLines[] = [
                'product' => $product,
                'quantity' => $item['qty'],
                'price' => $price,
                'subtotal' => $subtotal,
                'options' => $options,
            ];
        }

        $promocode = $this->resolvePromocode($validated['promocode'] ?? null);
        $discountPercent = $promocode ? (float) $promocode->discount_percent : 0;
        $discountAmount = $discountPercent > 0 ? round($totalBeforeDiscount * $discountPercent / 100, 2) : 0;
        $total = max(0, round($totalBeforeDiscount - $discountAmount, 2));

        $order = DB::transaction(function () use ($validated, $orderLines, $totalBeforeDiscount, $discountAmount, $discountPercent, $promocode, $total): Order {
            $order = Order::query()->create([
                'order_uuid' => $this->generateOrderUuid(),
                'customer_name' => $validated['name'],
                'customer_phone' => $validated['phone'],
                'customer_email' => $validated['email'],
                'total_before_discount' => $totalBeforeDiscount,
                'discount_amount' => $discountAmount,
                'discount_percent' => $discountPercent,
                'promocode_code' => $promocode?->code,
                'total' => $total,
                'status' => 'noua',
            ]);

            foreach ($orderLines as $lineIndex => $line) {
                /** @var Product $product */
                $product = $line['product'];

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'category_name' => trim(($product->category?->name ?? 'Fara categorie').' / '.($product->subcategory?->name ?? ''), ' /'),
                    'price' => $line['price'],
                    'quantity' => $line['quantity'],
                    'subtotal' => $line['subtotal'],
                    'options' => $this->persistEmailImages($line['options'], $order->order_uuid, $lineIndex + 1),
                ]);

                $product->decrement('stock', $line['quantity']);
            }

            return $order->load('items');
        });

        $emailWarning = null;

        try {
            Mail::to($order->customer_email)->send(new CustomerOrderConfirmation($order));
            Mail::to(config('mail.company_address'))->send(new CompanyOrderNotification($order));
        } catch (\Throwable $exception) {
            Log::error('Email comanda nereusit', [
                'order_uuid' => $order->order_uuid,
                'error' => $exception->getMessage(),
            ]);
            $emailWarning = 'Comanda a fost salvată. Emailurile nu au fost trimise deoarece SMTP nu este configurat sau a returnat eroare.';
        }

        $redirect = redirect()
    ->route('orders.success', $order)
    ->with('success', 'Comanda a fost salvată cu succes.');

if ($emailWarning) {
    $redirect->with('warning', $emailWarning);
}

return $redirect;
    }

    public function success(Order $order): View
    {
        $order->load('items');

        return view('cart.success', compact('order'));
    }

    private function resolvePromocode(?string $code): ?Promocode
    {
        $normalized = Promocode::normalizeCode($code);

        if ($normalized === '') {
            return null;
        }

        $promocode = Promocode::query()->where('code', $normalized)->first();

        if (! $promocode || ! $promocode->is_valid) {
            throw ValidationException::withMessages([
                'promocode' => 'Promocod invalid sau dezactivat.',
            ]);
        }

        return $promocode;
    }

    private function customDesignFee(array $options): float
    {
        $items = collect($options['design_items'] ?? [])
            ->filter(fn ($item) => is_array($item))
            ->take(self::MAX_DESIGN_ITEMS)
            ->count();

        if ($items === 0 && ! empty($options['design_previews'])) {
            $items = 1;
        }

        return (float) ($items * self::DESIGN_IMAGE_FEE);
    }

    private function sanitizeOptions(array $options): array
    {
        $clean = [];

        foreach (['selected_size', 'selected_color', 'modification_label'] as $field) {
            if (isset($options[$field]) && is_scalar($options[$field])) {
                $clean[$field] = Str::limit(strip_tags((string) $options[$field]), 120, '');
            }
        }

        $designItems = [];
        foreach (array_slice($options['design_items'] ?? [], 0, self::MAX_DESIGN_ITEMS) as $item) {
            if (! is_array($item)) {
                continue;
            }

            $image = $this->safeDataUri($item['image'] ?? null);
            $designItems[] = [
                'side' => in_array(($item['side'] ?? 'front'), ['front', 'back', 'mug'], true) ? $item['side'] : 'front',
                'x' => round((float) ($item['x'] ?? 50), 2),
                'y' => round((float) ($item['y'] ?? 45), 2),
                'width' => round((float) ($item['width'] ?? 140), 2),
                'rotation' => round((float) ($item['rotation'] ?? 0), 2),
                'aspectRatio' => round((float) ($item['aspectRatio'] ?? 1), 4),
                'image' => $image,
            ];
        }
        if ($designItems !== []) {
            $clean['design_items'] = $designItems;
        }

        $previews = [];
        foreach (['front', 'back', 'mug'] as $side) {
            $preview = $this->safeDataUri(data_get($options, "design_previews.{$side}"));
            if ($preview) {
                $previews[$side] = $preview;
            }
        }
        if ($previews !== []) {
            $clean['design_previews'] = $previews;
        }

        return $clean;
    }

    private function safeDataUri(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        if (! Str::startsWith($value, 'data:image/')) {
            return null;
        }

        if (strlen($value) > self::MAX_DATA_URI_LENGTH) {
            return null;
        }

        return $value;
    }


    private function persistEmailImages(array $options, string $orderUuid, int $itemNumber): array
    {
        if (! empty($options['product_image'])) {
            $options['product_image_url'] = $this->publicImageUrl((string) $options['product_image']);
        }

        if (! empty($options['design_items']) && is_array($options['design_items'])) {
            foreach ($options['design_items'] as $index => $design) {
                if (! is_array($design) || empty($design['image'])) {
                    continue;
                }

                $savedPath = $this->saveDataUriForEmail(
                    $design['image'],
                    $orderUuid,
                    'produs-'.$itemNumber.'-design-'.($index + 1).'-'.($design['side'] ?? 'front')
                );

                if ($savedPath) {
                    $options['design_items'][$index]['image_path'] = $savedPath;
                    $options['design_items'][$index]['image_url'] = $savedPath;
                }

                unset($options['design_items'][$index]['image']);
            }
        }

        if (! empty($options['design_previews']) && is_array($options['design_previews'])) {
            $options['design_preview_paths'] = [];
            $options['design_preview_urls'] = [];

            foreach ($options['design_previews'] as $side => $preview) {
                $savedPath = $this->saveDataUriForEmail(
                    $preview,
                    $orderUuid,
                    'produs-'.$itemNumber.'-aplicat-'.$side
                );

                if ($savedPath) {
                    $options['design_preview_paths'][$side] = $savedPath;
                    $options['design_preview_urls'][$side] = $savedPath;
                }
            }

            unset($options['design_previews']);

            if ($options['design_preview_paths'] === []) {
                unset($options['design_preview_paths'], $options['design_preview_urls']);
            }
        }

        return $options;
    }

    private function saveDataUriForEmail(mixed $dataUri, string $orderUuid, string $name): ?string
    {
        $parsed = $this->parseDataUri($dataUri);
        if (! $parsed) {
            return null;
        }

        [$bytes, $extension, $mimeType] = $parsed;
        $fileName = Str::slug($name ?: 'imagine').'.'.$extension;
        $path = 'order-mail-images/'.$orderUuid.'/'.$fileName;

        return app(SupabaseStorage::class)->uploadBinary($bytes, $path, $mimeType);
    }

    private function parseDataUri(mixed $dataUri): ?array
    {
        if (! is_string($dataUri) || ! preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,(.+)$/', $dataUri, $matches)) {
            return null;
        }

        $bytes = base64_decode($matches[2], true);
        if ($bytes === false) {
            return null;
        }

        $type = $matches[1] === 'jpg' ? 'jpeg' : $matches[1];
        $extension = $type === 'jpeg' ? 'jpg' : $type;

        return [$bytes, $extension, 'image/'.$type];
    }

    private function publicImageUrl(string $path): string
    {
        return StoredImage::url($path);
    }

    private function generateOrderUuid(): string
    {
        do {
            $uuid = (string) random_int(1000, 99999);
        } while (Order::query()->where('order_uuid', $uuid)->exists());

        return $uuid;
    }
}
